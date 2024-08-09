<?php

namespace App\Admin\Actions\ResultRouteFranceSystemQualificationStage;

use App\Helpers\Helpers;
use App\Models\Event;
use App\Models\Grades;
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

class BatchResultQualificationFranceCustomFillOneRoute extends Action
{
    protected $selector = '.result-add-qualification-france-one-route';

    public $category;

    public function __construct(ParticipantCategory $category)
    {
        $this->initInteractor();
        $this->category = $category;
    }
    public function handle(Request $request)
    {
        $results = $request->toArray();
        $event = Event::find($results['event_id']);
        $data = array();
        $result_for_edit = array();
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
        $data[] = array('owner_id' => $owner_id,
            'user_id' => intval($results['user_id']),
            'event_id' => intval($results['event_id']),
            'route_id' => intval($results['route_id']),
            'category_id' => $category_id,
            'amount_top' => $amount_top,
            'gender' => $gender,
            'amount_try_top' => intval($results['amount_try_top']),
            'amount_zone' => $amount_zone,
            'amount_try_zone' => intval($results['amount_try_zone']),
        );
        $result_for_edit[] = array(
            'Номер маршрута' => intval($results['route_id']),
            'Попытки на топ' => intval($results['amount_try_top']),
            'Попытки на зону' => intval($results['amount_try_zone'])
        );
        $route_id = intval($results['route_id']);
        $user = User::find(intval($results['user_id']))->middlename;

        $result = ResultRouteFranceSystemQualification::where('event_id', $results['event_id']
        )->where('user_id', $results['user_id'])->where('route_id', $route_id)->first();
        if($result){
            return $this->response()->error('Результат уже есть по '.$user.' и трассе '.$route_id);
        } else {
            $participant = ResultFranceSystemQualification::where('event_id', $results['event_id'])->where('user_id', $results['user_id'])->first();
            $participant->active = 1;
            $participant->result_for_edit_france_system_qualification = $result_for_edit;
            $participant->save();

            DB::table('result_route_france_system_qualification')->insert($data);
            Event::refresh_france_system_qualification_counting($event);
            return $this->response()->success('Результат успешно внесен')->refresh();
        }
    }

    public function form()
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
        $amount_routes = Grades::where('event_id', $event->id)->first()->count_routes;
        foreach ($result as $index => $res){
            $user = User::where('middlename', $res)->first()->id;
            $category_id = ResultFranceSystemQualification::where('event_id', '=', $event->id)->where('user_id', '=', $user)->first()->category_id;
            $category = ParticipantCategory::find($category_id)->category;
            $result[$index] = $res.' ['.$category.']';
            if(in_array($index, $result_final)){
                $result_user = ResultRouteFranceSystemQualification::where('event_id', $event->id)->where('user_id', $user);
                $routes = $result_user->pluck('route_id')->toArray();
                $string_version = '';
                foreach ($routes as $value) {
                    $string_version .= $value . ', ';
                }
                if($result_user->get()->count() == $amount_routes){
                    $result[$index] = $res.' ['.$category.']'.' [Добавлены все трассы]';
                } else {
                    $result[$index] = $res.' ['.$category.']'.' [Трассы: '.$string_version.']';
                }
            }
        }

        $routes = [];
        for($i = 1; $i <= $amount_routes; $i++){
            $routes[$i] = $i;
        }

        $this->select('user_id', 'Участник')->options($result)->required();
        $this->hidden('event_id', '')->value($event->id);
        $this->select('route_id', 'Трасса')->options($routes);
        $this->integer('amount_try_top', 'Попытки на топ');
        $this->integer('amount_try_zone', 'Попытки на зону');
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
