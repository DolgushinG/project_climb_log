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
use function Symfony\Component\String\s;

class BatchResultQualificationFranceCustomFillOneRoute extends CustomAction
{
    protected $selector = '.result-add-qualification-france-one-route';

    public $category;
    private string $script;

    public function __construct(ParticipantCategory $category, string $script = 'значение_по_умолчанию')
    {
        $this->initInteractor();
        $this->category = $category;
        $this->script = $script;
    }
    public function handle(Request $request)
    {
        $results = $request->toArray();
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
        if($event->is_open_main_rating){
            $merged_users = ResultFranceSystemQualification::get_qualification_france_global_participants($event, $this->category);
        } else {
            $merged_users = ResultFranceSystemQualification::get_qualification_france_participants($event, $this->category);
        }
        $result = $merged_users->pluck( 'middlename','id');
        $result_final = ResultRouteFranceSystemQualification::where('event_id', '=', $event->id)->select('user_id')->distinct()->pluck('user_id')->toArray();
        $amount_routes = Grades::where('event_id', $event->id)->first();
        if($amount_routes){
            $amount_routes = $amount_routes->count_routes;
        } else {
            $amount_routes = 0;
        }
        foreach ($result as $user_id => $middlename){
            $category_id = ResultFranceSystemQualification::where('event_id', '=', $event->id)->where('user_id', '=', $user_id)->first()->category_id;
            $category = ParticipantCategory::find($category_id)->category;
            $result[$user_id] = $middlename.' ['.$category.']';
            if(in_array($user_id, $result_final)){
                $result_user = ResultRouteFranceSystemQualification::where('event_id', $event->id)->where('user_id', $user_id);
                $routes = $result_user->pluck('route_id')->toArray();
                $string_version = '';
                foreach ($routes as $value) {
                    $string_version .= $value . ', ';
                }
                if($result_user->get()->count() == $amount_routes){
                    $result[$user_id] = $middlename.' ['.$category.']'.' [Добавлены все трассы]';
                } else {
                    $result[$user_id] = $middlename.' ['.$category.']'.' [Трассы: '.$string_version.']';
                }
            }
        }

        $routes = [];
        for($i = 1; $i <= $amount_routes; $i++){
            $routes[$i] = $i;
        }

        $this->select('user_id', 'Участник')->attribute('autocomplete', 'off')->attribute('data-user-id-'.$this->category->id, 'user_id')->options($result)->required();
        $this->hidden('event_id', '')->attribute('autocomplete', 'off')->attribute('id', 'event_id')->value($event->id);
        $this->select('route_id', 'Трасса')->attribute('autocomplete', 'off')->attribute('data-route-id-'.$this->category->id, 'route_id')->options($routes)->required();
        $this->integer('amount_try_top', 'Попытки на топ')->attribute('autocomplete', 'off');
        $this->integer('amount_try_zone', 'Попытки на зону')->attribute('autocomplete', 'off');
        Admin::script("// Получаем все элементы с атрибутом modal
        const elementsWithModalAttributeFranceQualification = document.querySelectorAll('[modal=\"app-admin-actions-resultroutefrancesystemqualificationstage-batchresultqualificationfrancecustomfilloneroute\"]');
        const elementsWithIdAttributeFranceQualification = document.querySelectorAll('[id=\"app-admin-actions-resultroutefrancesystemqualificationstage-batchresultqualificationfrancecustomfilloneroute\"]');

        // Создаем объект для отслеживания счетчика для каждого modal
        const modalCountersFranceQualification = {};
        const idCountersFranceQualification = {};

        // Перебираем найденные элементы
        elementsWithModalAttributeFranceQualification.forEach(element => {
            const modalValueFranceQualification = element.getAttribute('modal');

            // Проверяем, существует ли уже счетчик для данного modal
            if (modalValueFranceQualification in modalCountersFranceQualification) {
                // Если счетчик уже существует, инкрементируем его значение
                modalCountersFranceQualification[modalValueFranceQualification]++;
            } else {
                // Если счетчика еще нет, создаем его и устанавливаем значение 1
                modalCountersFranceQualification[modalValueFranceQualification] = 1;
            }

            // Получаем номер элемента для данного modal
            const elementNumberFranceQualification = modalCountersFranceQualification[modalValueFranceQualification];

            // Устанавливаем новое значение modal
            element.setAttribute('modal', `\${modalValueFranceQualification}-\${elementNumberFranceQualification}`);
        });
        elementsWithIdAttributeFranceQualification.forEach(element => {
            const idValueFranceQualification = element.getAttribute('id');

            // Проверяем, существует ли уже счетчик для данного modal
            if (idValueFranceQualification in idCountersFranceQualification) {
                // Если счетчик уже существует, инкрементируем его значение
                idCountersFranceQualification[idValueFranceQualification]++;
            } else {
                // Если счетчика еще нет, создаем его и устанавливаем значение 1
                idCountersFranceQualification[idValueFranceQualification] = 1;
            }

            // Получаем номер элемента для данного modal
            const elementNumberFranceQualification = idCountersFranceQualification[idValueFranceQualification];

            // Устанавливаем новое значение modal
            element.setAttribute('id', `\${idValueFranceQualification}-\${elementNumberFranceQualification}`);
        });

        ");
        \Encore\Admin\Facades\Admin::script($this->script);
    }

    public function html()
    {
       return "<a class='result-add-qualification-france-one-route btn btn-sm btn-primary'><i class='fa fa-plus-circle'></i> {$this->category->category} по одной трассе</a>
                 <style>
                 .result-add-qualification-france-one-route {margin-top:8px;}
                 @media screen and (max-width: 767px) {
                        .result-add-qualification-france-one-route {margin-top:8px;}
                    }
                </style>
            ";
    }

}
