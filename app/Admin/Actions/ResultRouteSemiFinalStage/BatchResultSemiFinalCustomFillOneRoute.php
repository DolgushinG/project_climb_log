<?php

namespace App\Admin\Actions\ResultRouteSemiFinalStage;

use App\Models\Event;
use App\Models\ResultQualificationClassic;
use App\Models\ParticipantCategory;
use App\Models\ResultFranceSystemQualification;
use App\Models\ResultRouteFinalStage;
use App\Models\ResultRouteFranceSystemQualification;
use App\Models\ResultRouteSemiFinalStage;
use App\Models\ResultSemiFinalStage;
use App\Models\User;
use Encore\Admin\Actions\Action;
use Encore\Admin\Admin;
use Encore\Admin\Form;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BatchResultSemiFinalCustomFillOneRoute extends Action
{
    protected $selector = '.result-add-one-route';

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
        if($results['amount_try_top'] > 0 || $results['amount_try_top'] != null){
            $amount_top  = 1;
        } else {
            $amount_top  = 0;
        }
        if($results['amount_try_zone'] > 0 || $results['amount_try_zone'] != null){
            $amount_zone  = 1;
        } else {
            $amount_zone  = 0;
        }
        if($event->is_france_system_qualification){
            $participant = ResultFranceSystemQualification::where('event_id', $results['event_id'])->where('user_id', $results['user_id'])->first();
        } else {
            $participant = ResultQualificationClassic::where('event_id', $results['event_id'])->where('user_id', $results['user_id'])->first();
        }
        $category_id = $participant->category_id;
        $gender = $participant->gender;
        $data[] = array('owner_id' => \Encore\Admin\Facades\Admin::user()->id,
            'user_id' => intval($results['user_id']),
            'event_id' => intval($results['event_id']),
            'final_route_id' => intval($results['final_route_id']),
            'category_id' => $category_id,
            'amount_top' => $amount_top,
            'gender' => $gender,
            'amount_try_top' => intval($results['amount_try_top']),
            'amount_zone' => $amount_zone,
            'amount_try_zone' => intval($results['amount_try_zone']),
        );
        $final_route_id = intval($results['final_route_id']);
        $user = User::find(intval($results['user_id']))->middlename;

        $result = ResultRouteSemiFinalStage::where('event_id', $results['event_id']
        )->where('user_id', $results['user_id'])->where('final_route_id', $final_route_id)->first();
        if($result){
            return $this->response()->error('Результат уже есть по '.$user.' и трассе '.$final_route_id);
        } else {
            DB::table('result_route_semifinal_stage')->insert($data);
            Event::refresh_final_points_all_participant_in_semifinal($event->id);
            return $this->response()->success('Результат успешно внесен')->refresh();
        }
    }

    public function form()
    {
        $this->modalSmall();
        $event = Event::where('owner_id', '=', \Encore\Admin\Facades\Admin::user()->id)
            ->where('active', '=', 1)->first();
        $amount_the_best_participant = $event->amount_the_best_participant ?? 10;
        $merged_users = ResultSemiFinalStage::get_participant_semifinal($event, $amount_the_best_participant, $this->category);
        $result = $merged_users->pluck( 'middlename','id');
        $result_semifinal = ResultRouteSemiFinalStage::where('event_id', '=', $event->id)->select('user_id')->distinct()->pluck('user_id')->toArray();
        foreach ($result as $index => $res){
            $user = User::where('middlename', $res)->first()->id;
            if($event->is_france_system_qualification) {
                $category_id = ResultRouteFranceSystemQualification::where('event_id', '=', $event->id)->where('user_id', '=', $user)->first()->category_id;
            } else {
                $category_id = ResultQualificationClassic::where('event_id', $event->id)->where('user_id', $user)->first()->category_id;
            }
            $category = ParticipantCategory::find($category_id)->category;
            $result[$index] = $res.' ['.$category.']';
            if(in_array($index, $result_semifinal)){
                $result_user = ResultRouteSemiFinalStage::where('event_id', $event->id)->where('user_id', $user);
                $routes = $result_user->pluck('final_route_id')->toArray();
                $string_version = '';
                foreach ($routes as $value) {
                    $string_version .= $value . ', ';
                }
                if($result_user->get()->count() == $event->amount_routes_in_final){
                    $result[$index] = $res.' ['.$category.']'.' [Добавлены все трассы]';
                } else {
                    $result[$index] = $res.' ['.$category.']'.' [Трассы: '.$string_version.']';
                }
            }
        }
        Admin::script("// Получаем все элементы с атрибутом modal
        const elementsWithModalAttribute2 = document.querySelectorAll('[modal=\"app-admin-actions-resultroutesemifinalstage-batchresultsemifinalcustomfilloneroute\"]');
        const elementsWithIdAttribute2 = document.querySelectorAll('[id=\"app-admin-actions-resultroutesemifinalstage-batchresultsemifinalcustomfilloneroute\"]');

        // Создаем объект для отслеживания счетчика для каждого modal
        const modalCounters2 = {};
        const idCounters2 = {};

        // Перебираем найденные элементы
        elementsWithModalAttribute2.forEach(element => {
            const modalValue2 = element.getAttribute('modal');

            // Проверяем, существует ли уже счетчик для данного modal
            if (modalValue2 in modalCounters2) {
                // Если счетчик уже существует, инкрементируем его значение
                modalCounters2[modalValue2]++;
            } else {
                // Если счетчика еще нет, создаем его и устанавливаем значение 1
                modalCounters2[modalValue2] = 1;
            }

            // Получаем номер элемента для данного modal
            const elementNumber2 = modalCounters2[modalValue2];

            // Устанавливаем новое значение modal
            element.setAttribute('modal', `\${modalValue2}-\${elementNumber2}`);
        });
        elementsWithIdAttribute2.forEach(element => {
            const idValue2 = element.getAttribute('id');

            // Проверяем, существует ли уже счетчик для данного modal
            if (idValue2 in idCounters2) {
                // Если счетчик уже существует, инкрементируем его значение
                idCounters2[idValue2]++;
            } else {
                // Если счетчика еще нет, создаем его и устанавливаем значение 1
                idCounters2[idValue2] = 1;
            }

            // Получаем номер элемента для данного modal
            const elementNumber2 = idCounters2[idValue2];

            // Устанавливаем новое значение modal
            element.setAttribute('id', `\${idValue2}-\${elementNumber2}`);
        });

        ");

        $routes = [];
        for($i = 1; $i <= $event->amount_routes_in_semifinal; $i++){
            $routes[$i] = $i;
        }
        $this->select('user_id', 'Участник')->options($result)->required();
        $this->hidden('event_id', '')->value($event->id);
        $this->select('final_route_id', 'Трасса')->attribute('id', 'final_route_id')->options($routes);
        $this->integer('amount_try_top', 'Попытки на топ');
        $this->integer('amount_try_zone', 'Попытки на зону');
    }

    public function html()
    {
        $event = Event::where('owner_id', '=', \Encore\Admin\Facades\Admin::user()->id)
            ->where('active', '=', 1)->first();
        if($event->is_semifinal){
            return "<a class='result-add-one-route btn btn-sm btn-primary'><i class='fa fa-plus-circle'></i> {$this->category->category} по одной трассе</a>
                 <style>
                  .result-add-one-route {margin-top:8px;}
                 @media screen and (max-width: 767px) {
                        .result-add-one-route {margin-top:8px;}
                    }
                </style>
            ";
        } else {
            return "<a disabled class='result-add-one-route btn btn-sm btn-warning' style='display: none'><i class='fa fa-info-circle'></i></a>";
        }
    }

}
