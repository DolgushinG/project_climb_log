<?php

namespace App\Admin\Actions\ResultRouteFranceSystemQualificationStage;

use App\Helpers\Helpers;
use App\Models\Event;
use App\Models\Grades;
use App\Models\ParticipantCategory;
use App\Models\ResultFranceSystemQualification;
use App\Models\ResultRouteFranceSystemQualification;
use App\Models\User;
use Encore\Admin\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Admin\Extensions\CustomAction;
use Illuminate\Support\Facades\Log;
use function Symfony\Component\String\s;

class BatchResultQualificationFranceCustomFillOneRouteAndOneCategory extends CustomAction
{
    protected $selector = '.result-add-qualification-france-one-route-one-category';

    private string $script;

    public function __construct(string $script = 'значение_по_умолчанию')
    {
        $this->initInteractor();
        $this->script = $script;
    }
    public function handle(Request $request)
    {
        $results = $request->toArray();
        $amount_try_top = intval($results['amount_try_top']);
        $amount_try_zone = intval($results['amount_try_zone']);
        $all_attempts = intval($results['all_attempts']);

        $event = Event::find($results['event_id']);
        if(intval($results['amount_try_top']) > 0){
            $amount_top  = 1;
        } else {
            $amount_top  = 0;
        }
        if(intval($results['amount_try_zone']) > 0){
            $amount_zone  = 1;
        } else {
            $amount_zone  = 0;
        }
        $max_attempts = Helpers::find_max_attempts($amount_try_top, $amount_try_zone);
        if(Helpers::validate_amount_sum_top_and_zone_and_attempts($all_attempts, $amount_try_top, $amount_try_zone)){
            return $this->response()->error(
                'У трассы '.$results['route_id'].' Максимальное кол-во попыток '.$max_attempts.' а в поле все попытки - '. $all_attempts);
        }

        # Если есть ТОП то зона не может быть 0
        if(Helpers::validate_amount_top_and_zone($amount_top, $amount_zone)){
            return $this->response()->error('У трассы '.$results['route_id'].' отмечен ТОП, и получается зона не может быть 0');
        }

        # Кол-во попыток на зону не может быть меньше чем кол-во на ТОП
        if(Helpers::validate_amount_try_top_and_zone($results['amount_try_top'], $results['amount_try_zone'])){
            return $this->response()->error('Кол-во попыток на зону не может быть меньше, чем кол-во попыток на ТОП, трасса '.$results['route_id'] );
        }

        $participant = ResultFranceSystemQualification::where('event_id', $results['event_id'])->where('user_id', $results['user_id'])->first();
        if($event->is_open_main_rating){
            $category_id = $participant->global_category_id;
        } else {
            $category_id = $participant->category_id;
        }
        $gender = $participant->gender;
        $owner_id = \Encore\Admin\Facades\Admin::user()->id;

        self::update_france_route_results($owner_id, $category_id, $results, $amount_top, $gender, $amount_zone);

        Event::refresh_france_system_qualification_counting($event);
        return $this->response()->success('Результат успешно внесен')->refresh();
    }

    public static function update_france_route_results($owner_id, $category_id, $results, $amount_top, $gender, $amount_zone)
    {
        $result_for_edit = [[
            'Номер маршрута' => intval($results['route_id']),
            'Попытки на топ' => intval($results['amount_try_top']),
            'Попытки на зону' => intval($results['amount_try_zone'])
        ]];
        $route_id = intval($results['route_id']);
        $amount_try_top = intval($results['amount_try_top']);
        $amount_try_zone = intval($results['amount_try_zone']);
        $user_id = $results['user_id'];
        $event_id = $results['event_id'];
        $all_attempts = intval($results['all_attempts']);
        $participant = ResultFranceSystemQualification::where('event_id', $event_id)
            ->where('user_id', $user_id)
            ->first();
        $result_route = ResultRouteFranceSystemQualification::where('event_id', $event_id)
            ->where('user_id', $user_id)
            ->where('route_id', $route_id)
            ->first();
        $result_all_route = ResultRouteFranceSystemQualification::where('event_id', $event_id)
            ->where('user_id', $user_id)
            ->first();
        if(!$result_all_route){
            $participant->active = 1;
            $participant->save();
        }
        $existing_result_for_edit = $participant->result_for_edit_france_system_qualification ?? [];
        # Если уже есть результат надо обновить его как в grid - $participant - json for edit так и в $result по трассам
        if($result_route){
            $result_route->all_attempts = $all_attempts;
            $result_route->amount_top = $amount_top;
            $result_route->amount_try_top = $amount_try_top;
            $result_route->amount_zone = $amount_zone;
            $result_route->amount_try_zone = $amount_try_zone;
            $result_route->save();
            foreach ($existing_result_for_edit as $index => $res){
                if($res['Номер маршрута'] == $route_id){
                    $existing_result_for_edit[$index]['Попытки на топ'] = $amount_try_top;
                    $existing_result_for_edit[$index]['Попытки на зону'] = $amount_try_zone;
                }
            }
            self::update_results_fsq($participant, $existing_result_for_edit);
        } else {
            # Создание результата трассы который еще не было
            self::create_results_fsq($participant, $existing_result_for_edit, $result_for_edit);
            $data = [['owner_id' => $owner_id,
                'user_id' => $user_id,
                'event_id' => $event_id,
                'route_id' => $route_id,
                'category_id' => $category_id,
                'amount_top' => $amount_top,
                'gender' => $gender,
                'all_attempts' => $all_attempts,
                'amount_try_top' => $amount_try_top,
                'amount_zone' => $amount_zone,
                'amount_try_zone' => $amount_try_zone,
            ]];
            self::update_results_rrfsq($data);
        }
    }
    public static function update_results_rrfsq($data)
    {
        DB::table('result_route_france_system_qualification')->insert($data);
    }

    public static function create_results_fsq($participant, $results_old_for_edit, $result_for_edit)
    {
        $merged_result_for_edit = array_merge($results_old_for_edit, $result_for_edit);
        // Сортируем массив по "Номеру маршрута"
        usort($merged_result_for_edit, function ($a, $b) {
            return $a['Номер маршрута'] <=> $b['Номер маршрута'];
        });
        $participant->result_for_edit_france_system_qualification = $merged_result_for_edit;
        $participant->save();
    }
    public static function update_results_fsq($participant, $result_for_edit)
    {
        $participant->result_for_edit_france_system_qualification = $result_for_edit;
        $participant->save();
    }
    public function custom_form()
    {
        $this->modalSmall();
        $event = Event::where('owner_id', '=', \Encore\Admin\Facades\Admin::user()->id)
            ->where('active', '=', 1)->first();
        $amount_routes = Grades::where('event_id', $event->id)->first();
        if($amount_routes){
            $amount_routes = $amount_routes->count_routes;
        } else {
            $amount_routes = 0;
        }
        $routes = [];
        for($i = 1; $i <= $amount_routes; $i++){
            $routes[$i] = $i;
        }
        $this->select('user_id', 'Участник')->attribute('autocomplete', 'off')->attribute('data-category-user-id', 'user_id')->required();
        $this->hidden('event_id', '')->attribute('autocomplete', 'off')->attribute('data-category-event-id', 'event_id')->value($event->id);
        $this->select('route_id', 'Трасса')->attribute('autocomplete', 'off')->attribute('data-category-route-id', 'route_id')->options($routes)->required();
        $this->integer('all_attempts', 'Все попытки')
            ->attribute('autocomplete', 'off')
            ->attribute('data-all-attempts-id', 'all-attempts');
        $this->integer('amount_try_top', 'Попытки на топ')->attribute('autocomplete', 'off');
        $this->integer('amount_try_zone', 'Попытки на зону')->attribute('autocomplete', 'off');
        Admin::style('
                    .input-group {
                    display: flex;
                    align-items: center;
                }

                .form-control {
                    margin-right: -1px; /* Небольшой выступ для кнопки */
                }

                .input-group-append {
                    margin-top: 10px;
                    margin-left: 5px; /* Убираем отступ слева */
                }

                .btn-outline-secondary {
                    background-color: #28a745; /* Зеленый фон */
                    border-color: #28a745; /* Цвет границы совпадает с фоном */
                    color: #fff; /* Белый цвет текста */
                     margin-left: 5px;
                }

                .btn-outline-secondary:hover {
                    background-color: #218838; /* Темнее зеленый при наведении */
                    border-color: #1e7e34; /* Темнее граница при наведении */
                     margin-left: 5px;
                }

        ');
        \Encore\Admin\Facades\Admin::script($this->script);
    }
    public function html()
    {
       return "<a class='result-add-qualification-france-one-route-one-category btn btn-sm btn-warning'><i class='fa fa-plus-circle'></i> Все участники по одной трассе </a>
                 <style>
                 .result-add-qualification-france-one-route-one-category {margin-top:8px;}
                 @media screen and (max-width: 767px) {
                        .result-add-qualification-france-one-route-one-category {margin-top:8px;}
                    }
                </style>
            ";
    }

}
