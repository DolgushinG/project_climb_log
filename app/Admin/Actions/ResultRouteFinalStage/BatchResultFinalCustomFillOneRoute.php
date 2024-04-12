<?php

namespace App\Admin\Actions\ResultRouteFinalStage;

use App\Models\Event;
use App\Models\Participant;
use App\Models\ParticipantCategory;
use App\Models\ResultFinalStage;
use App\Models\ResultQualificationLikeFinal;
use App\Models\ResultRouteFinalStage;
use App\Models\ResultRouteQualificationLikeFinal;
use App\Models\ResultRouteSemiFinalStage;
use App\Models\ResultSemiFinalStage;
use App\Models\User;
use Encore\Admin\Actions\Action;
use Encore\Admin\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BatchResultFinalCustomFillOneRoute extends Action
{
    protected $selector = '.result-add-final-one-route';

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
        if($event->is_qualification_counting_like_final){
            $participant = ResultQualificationLikeFinal::where('event_id', $results['event_id'])->where('user_id', $results['user_id'])->first();
        } else {
            $participant = Participant::where('event_id', $results['event_id'])->where('user_id', $results['user_id'])->first();
        }
        $category_id = $participant->category_id;
        $gender = $participant->gender;
        $data[] = array('owner_id' => \Encore\Admin\Facades\Admin::user()->id,
            'user_id' => intval($results['user_id']),
            'event_id' => intval($results['event_id']),
            'final_route_id' => intval($results['final_route_id_'.$i]),
            'category_id' => $category_id,
            'amount_top' => $amount_top,
            'gender' => $gender,
            'amount_try_top' => intval($results['amount_try_top_'.$i]),
            'amount_zone' => $amount_zone,
            'amount_try_zone' => intval($results['amount_try_zone_'.$i]),
        );
        $final_route_id = intval($results['final_route_id']);
        $user = User::find(intval($results['user_id']))->middlename;

        $result = ResultRouteFinalStage::where('event_id', $results['event_id']
        )->where('user_id', $results['user_id'])->where('final_route_id', $final_route_id)->first();
        if($result){
            return $this->response()->error('Результат уже есть по '.$user.' и трассе '.$final_route_id);
        } else {
            DB::table('result_route_final_stage')->insert($data);
            Event::refresh_final_points_all_participant_in_semifinal($event->id);
            return $this->response()->success('Результат успешно внесен')->refresh();
        }
    }

    public function form()
    {
        $this->modalSmall();
        $event = Event::where('owner_id', '=', \Encore\Admin\Facades\Admin::user()->id)
            ->where('active', '=', 1)->first();
        $merged_users = ResultFinalStage::get_final_participant($event, $this->category);
        $result = $merged_users->pluck( 'middlename','id');
        $result_final = ResultRouteFinalStage::where('event_id', '=', $event->id)->select('user_id')->distinct()->pluck('user_id')->toArray();
        foreach ($result as $index => $res){
            $user = User::where('middlename', $res)->first()->id;
            if($event->is_qualification_counting_like_final) {
                $category_id = ResultRouteQualificationLikeFinal::where('event_id', '=', $event->id)->where('user_id', '=', $user)->first()->category_id;
            }
            if($event->is_additional_final){
                $category_id = Participant::where('event_id', $event->id)->where('user_id', $user)->first()->category_id;
            }
            $category = ParticipantCategory::find($category_id)->category;
            $result[$index] = $res.' ['.$category.']';
            if(in_array($index, $result_final)){
                $result[$index] = $res.' ['.$category.']'.' [Уже добавлен]';
            }
        }
        $routes = [];
        for($i = 1; $i <= $event->amount_routes_in_semifinal; $i++){
            $routes[$i] = $i;
        }
        $this->select('user_id', 'Участник')->options($result)->required();
        $this->hidden('event_id', '')->value($event->id);
        $this->select('final_route_id', 'Трасса')->value($routes);
        $this->integer('amount_try_top', 'Попытки на топ');
        $this->integer('amount_try_zone', 'Попытки на зону');
        Admin::script("// Получаем все элементы с атрибутом modal
        const elementsWithModalAttribute3 = document.querySelectorAll('[modal=\"app-admin-actions-resultroutefinalstage-batchresultfinalcustom\"]');
        const elementsWithIdAttribute3 = document.querySelectorAll('[id=\"app-admin-actions-resultroutefinalstage-batchresultfinalcustom\"]');

        // Создаем объект для отслеживания счетчика для каждого modal
        const modalCounters3 = {};
        const idCounters3 = {};

        // Перебираем найденные элементы
        elementsWithModalAttribute3.forEach(element => {
            const modalValue3 = element.getAttribute('modal');

            // Проверяем, существует ли уже счетчик для данного modal
            if (modalValue3 in modalCounters3) {
                // Если счетчик уже существует, инкрементируем его значение
                modalCounters3[modalValue3]++;
            } else {
                // Если счетчика еще нет, создаем его и устанавливаем значение 1
                modalCounters3[modalValue3] = 1;
            }

            // Получаем номер элемента для данного modal
            const elementNumber3 = modalCounters3[modalValue3];

            // Устанавливаем новое значение modal
            element.setAttribute('modal', `\${modalValue3}-\${elementNumber3}`);
        });
        elementsWithIdAttribute3.forEach(element => {
            const idValue3 = element.getAttribute('id');

            // Проверяем, существует ли уже счетчик для данного modal
            if (idValue3 in idCounters3) {
                // Если счетчик уже существует, инкрементируем его значение
                idCounters3[idValue3]++;
            } else {
                // Если счетчика еще нет, создаем его и устанавливаем значение 1
                idCounters3[idValue3] = 1;
            }

            // Получаем номер элемента для данного modal
            const elementNumber3 = idCounters3[idValue3];

            // Устанавливаем новое значение modal
            element.setAttribute('id', `\${idValue3}-\${elementNumber3}`);
        });

        ");
    }

    public function html()
    {
       return "<a class='result-add-final-one-route btn btn-sm btn-primary'><i class='fa fa-plus-circle'></i> {$this->category->category} по одной трассе</a>
                 <style>
                 .result-add-final-one-route {margin-top:8px;}
                 @media screen and (max-width: 767px) {
                        .result-add-final-one-route {margin-top:8px;}
                    }
                </style>
            ";
    }

}
