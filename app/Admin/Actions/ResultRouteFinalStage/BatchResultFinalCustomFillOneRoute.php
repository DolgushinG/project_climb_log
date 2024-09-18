<?php

namespace App\Admin\Actions\ResultRouteFinalStage;

use App\Admin\Extensions\CustomAction;
use App\Helpers\Helpers;
use App\Models\Event;
use App\Models\ResultQualificationClassic;
use App\Models\ParticipantCategory;
use App\Models\ResultFinalStage;
use App\Models\ResultFranceSystemQualification;
use App\Models\ResultRouteFinalStage;
use App\Models\ResultRouteFranceSystemQualification;
use App\Models\User;
use Encore\Admin\Actions\Action;
use Encore\Admin\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BatchResultFinalCustomFillOneRoute extends CustomAction
{
    protected $selector = '.result-add-final-one-route';

    public $category;
    private string $script;

    public function __construct(ParticipantCategory $category, string $script = '')
    {
        $this->initInteractor();
        $this->category = $category;
        $this->script = $script;
    }
    public function handle(Request $request)
    {
        $results = $request->toArray();
        $amount_try_top = intval($results['amount_try_top']);
        $amount_try_zone = intval($results['amount_try_zone']);
        $all_attempts = intval($results['all_attempts']);
        $event_id = intval($results['event_id']);
        $event = Event::find($event_id);
        $final_route_id = intval($results['final_route_id']);
        if($amount_try_top > 0){
            $amount_top  = 1;
        } else {
            $amount_top  = 0;
        }
        if($amount_try_zone > 0){
            $amount_zone  = 1;
        } else {
            $amount_zone  = 0;
        }
        if($final_route_id == 0){
            return $this->response()->error('Вы не выбрали номер маршрута');
        }
        $max_attempts = Helpers::find_max_attempts($amount_try_top, $amount_try_zone);
        if(Helpers::validate_amount_sum_top_and_zone_and_attempts($all_attempts, $amount_try_top, $amount_try_zone)){
            return $this->response()->error(
                'У трассы '.$final_route_id.' Максимальное кол-во попыток '.$max_attempts.' а в поле все попытки - '. $all_attempts);
        }
        # Если есть ТОП то зона не может быть 0
        if(Helpers::validate_amount_top_and_zone($amount_top, $amount_zone)){
            return $this->response()->error('У трассы '.$final_route_id.' отмечен ТОП, и получается зона не может быть 0');
        }

        # Кол-во попыток на зону не может быть меньше чем кол-во на ТОП
        if(Helpers::validate_amount_try_top_and_zone($amount_try_top, $amount_try_zone)){
            return $this->response()->error('Кол-во попыток на зону не может быть меньше, чем кол-во попыток на ТОП, трасса '.$final_route_id );
        }

        if($event->is_france_system_qualification){
            $participant = ResultFranceSystemQualification::where('event_id', $results['event_id'])->where('user_id', $results['user_id'])->first();
        } else {
            $participant = ResultQualificationClassic::where('event_id', $results['event_id'])->where('user_id', $results['user_id'])->first();
        }
        if($event->is_open_main_rating && $event->is_auto_categories){
            $category_id = $participant->global_category_id;
        } else {
            $category_id = $participant->category_id;
        }
        $gender = $participant->gender;
        $owner_id = \Encore\Admin\Facades\Admin::user()->id;

        self::update_final_route_results($owner_id, $category_id,$results, $amount_top,$gender, $amount_zone);
        Event::refresh_final_points_all_participant_in_final($event_id);
        return $this->response()->success('Результат успешно внесен')->refresh();
    }

    public function custom_form()
    {
        $this->modalSmall();
        $event = Event::where('owner_id', '=', \Encore\Admin\Facades\Admin::user()->id)
            ->where('active', '=', 1)->first();
        if($event->is_open_main_rating){
            $merged_users = ResultFinalStage::get_final_global_participant($event, $this->category);
        } else {
            $merged_users = ResultFinalStage::get_final_participant($event, $this->category);
        }
        $result = $merged_users->pluck( 'middlename','id');
        $result_final = ResultRouteFinalStage::where('event_id', '=', $event->id)->select('user_id')->distinct()->pluck('user_id')->toArray();
        foreach ($result as $user_id => $middlename){
            if($event->is_france_system_qualification) {
                $category_id = ResultFranceSystemQualification::where('event_id', '=', $event->id)->where('user_id', '=', $user_id)->first()->category_id;
            } else {
                if($event->is_open_main_rating && $event->is_auto_categories){
                    $category_id = ResultQualificationClassic::where('event_id', $event->id)->where('user_id', $user_id)->first()->global_category_id;
                } else {
                    $category_id = ResultQualificationClassic::where('event_id', $event->id)->where('user_id', $user_id)->where('active', 1)->first()->category_id;
                }
            }
            $category = ParticipantCategory::find($category_id)->category;
            $result[$user_id] = $middlename.' ['.$category.']';
            if(in_array($user_id, $result_final)){
                $result_user = ResultRouteFinalStage::where('event_id', $event->id)->where('user_id', $user_id);
                $routes = $result_user->pluck('final_route_id')->toArray();
                $string_version = '';
                foreach ($routes as $value) {
                    $string_version .= $value . ', ';
                }
                if($result_user->get()->count() == $event->amount_routes_in_final){
                    $result[$user_id] = $middlename.' ['.$category.']'.' [Добавлены все трассы]';
                } else {
                    $result[$user_id] = $middlename.' ['.$category.']'.' [Трассы: '.$string_version.']';
                }
            }
        }

        $routes = [];
        for($i = 1; $i <= $event->amount_routes_in_final; $i++){
            $routes[$i] = $i;
        }
        $this->select('user_id', 'Участник')->attribute('autocomplete', 'off')->attribute('data-user-id'.$this->category->id, 'user_id')->options($result)->required();
        $this->hidden('event_id', '')->attribute('autocomplete', 'off')->attribute('data-event-id'.$this->category->id, 'event_id')->value($event->id);
        $this->select('final_route_id', 'Трасса')->attribute('autocomplete', 'off')->attribute('data-final-route-id'.$this->category->id, 'final_route_id')->options($routes)->required();
        $this->integer('all_attempts', 'Все попытки')
            ->attribute('autocomplete', 'off')
            ->attribute('id', 'all_attempts-'.$this->category->id)
            ->attribute('data-all-attempts-id'.$this->category->id, 'all-attempts');
        $this->integer('amount_try_top', 'Попытки на топ')->attribute('data-amount_try_top'.$this->category->id, 'top');
        $this->integer('amount_try_zone', 'Попытки на зону')->attribute('data-amount_try_zone'.$this->category->id, 'zone');
        Admin::script("// Получаем все элементы с атрибутом modal
        const elementsWithModalAttribute = document.querySelectorAll('[modal=\"app-admin-actions-resultroutefinalstage-batchresultfinalcustomfilloneroute\"]');
        const elementsWithIdAttribute = document.querySelectorAll('[id=\"app-admin-actions-resultroutefinalstage-batchresultfinalcustomfilloneroute\"]');

        // Создаем объект для отслеживания счетчика для каждого modal
        const modalCounters= {};
        const idCounters = {};

        // Перебираем найденные элементы
        elementsWithModalAttribute.forEach(element => {
            const modalValue = element.getAttribute('modal');

            // Проверяем, существует ли уже счетчик для данного modal
            if (modalValue in modalCounters) {
                // Если счетчик уже существует, инкрементируем его значение
                modalCounters[modalValue]++;
            } else {
                // Если счетчика еще нет, создаем его и устанавливаем значение 1
                modalCounters[modalValue] = 1;
            }

            // Получаем номер элемента для данного modal
            const elementNumber = modalCounters[modalValue];

            // Устанавливаем новое значение modal
            element.setAttribute('modal', `\${modalValue}-\${elementNumber}`);
        });
        elementsWithIdAttribute.forEach(element => {
            const idValue = element.getAttribute('id');

            // Проверяем, существует ли уже счетчик для данного modal
            if (idValue in idCounters) {
                // Если счетчик уже существует, инкрементируем его значение
                idCounters[idValue]++;
            } else {
                // Если счетчика еще нет, создаем его и устанавливаем значение 1
                idCounters[idValue] = 1;
            }

            // Получаем номер элемента для данного modal
            const elementNumber = idCounters[idValue];

            // Устанавливаем новое значение modal
            element.setAttribute('id', `\${idValue}-\${elementNumber}`);
        });

        ");
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
        $event = Event::where('owner_id', '=', \Encore\Admin\Facades\Admin::user()->id)->where('active', 1)->first();
        if($event->amount_the_best_participant_to_go_final > 0){
            return "<a class='result-add-final-one-route btn btn-sm btn-primary'><i class='fa fa-plus-circle'></i> {$this->category->category} по одной трассе</a>
                 <style>
                 .result-add-final-one-route {margin-top:8px;}
                 @media screen and (max-width: 767px) {
                        .result-add-final-one-route {margin-top:8px;}
                    }
                </style>
            ";
        } else {
            return "<a href='#' class='result-add-final-one-route btn btn-sm btn-primary' disabled>Кол-во участников в финал 0</a>
                 <style>
                 .result-add-final-one-route {margin-top:8px;}
                 @media screen and (max-width: 767px) {
                        .result-add-final-one-route {margin-top:8px;}
                    }
                </style>
            ";
        }

    }
    public static function update_final_route_results($owner_id, $category_id, $results, $amount_top, $gender, $amount_zone)
    {
        $amount_try_top = intval($results['amount_try_top']);
        $amount_try_zone = intval($results['amount_try_zone']);
        $route_id = intval($results['final_route_id']);

        $result_for_edit = [[
            'Номер маршрута' => $route_id,
            'Попытки на топ' => $amount_try_top,
            'Попытки на зону' => $amount_try_zone
        ]];

        $user_id = $results['user_id'];
        $event_id = $results['event_id'];
        $all_attempts = intval($results['all_attempts']);

        $participant = ResultFinalStage::where('event_id', $event_id)
            ->where('user_id', $user_id)
            ->first();
        if(!$participant){
            $participant = new ResultFinalStage;
        }

        $result_route = ResultRouteFinalStage::where('event_id', $event_id)
            ->where('user_id', $user_id)
            ->where('final_route_id', $route_id)
            ->first();

        $existing_result_for_edit = $participant->result_for_edit_final ?? [];
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
            self::update_results_final($participant, $existing_result_for_edit);
        } else {
            # Создание результата трассы который еще не было
            self::create_results_final($owner_id, $event_id, $user_id, $gender, $category_id, $participant, $existing_result_for_edit, $result_for_edit);
            $data = [['owner_id' => $owner_id,
                'user_id' => $user_id,
                'event_id' => $event_id,
                'final_route_id' => $route_id,
                'category_id' => $category_id,
                'amount_top' => $amount_top,
                'gender' => $gender,
                'all_attempts' => $all_attempts,
                'amount_try_top' => $amount_try_top,
                'amount_zone' => $amount_zone,
                'amount_try_zone' => $amount_try_zone,
            ]];
            self::update_results_route_final($data, 'result_route_final_stage');
        }
    }
    public static function update_results_final($participant, $result_for_edit)
    {
        $participant->result_for_edit_final = $result_for_edit;
        $participant->save();
    }
    public static function update_results_route_final($data, $table)
    {
        DB::table($table)->insert($data);
    }
    public static function create_results_final($owner_id, $event_id, $user_id, $gender, $category_id, $participant, $results_old_for_edit, $result_for_edit)
    {
        $merged_result_for_edit = array_merge($results_old_for_edit, $result_for_edit);
        // Сортируем массив по "Номеру маршрута"
        usort($merged_result_for_edit, function ($a, $b) {
            return $a['Номер маршрута'] <=> $b['Номер маршрута'];
        });
        $participant->owner_id = $owner_id;
        $participant->event_id = $event_id;
        $participant->user_id = $user_id;
        $participant->gender = $gender;
        $participant->category_id = $category_id;
        $participant->result_for_edit_final = $merged_result_for_edit;
        $participant->save();
    }

}
