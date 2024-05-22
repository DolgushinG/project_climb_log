<?php

namespace App\Admin\Actions\ResultRouteFinalStage;

use App\Models\Event;
use App\Models\ResultQualificationClassic;
use App\Models\ParticipantCategory;
use App\Models\ResultFinalStage;
use App\Models\ResultFranceSystemQualification;
use App\Models\ResultRouteFinalStage;
use App\Models\ResultRouteFranceSystemQualification;
use App\Models\ResultRouteSemiFinalStage;
use App\Models\ResultSemiFinalStage;
use App\Models\User;
use Encore\Admin\Actions\Action;
use Encore\Admin\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BatchResultFinalCustom extends Action
{
    protected $selector = '.result-add-final';

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
        for($i = 1; $i <= $event->amount_routes_in_final; $i++){
            if($results['amount_try_top_'.$i] > 0 || $results['amount_try_top_'.$i] != null){
                $amount_top  = 1;
            } else {
                $amount_top  = 0;
            }
            if($results['amount_try_zone_'.$i] > 0 || $results['amount_try_zone_'.$i] != null){
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
                'final_route_id' => intval($results['final_route_id_'.$i]),
                'category_id' => $category_id,
                'amount_top' => $amount_top,
                'gender' => $gender,
                'amount_try_top' => intval($results['amount_try_top_'.$i]),
                'amount_zone' => $amount_zone,
                'amount_try_zone' => intval($results['amount_try_zone_'.$i]),
                );
        }
        DB::table('result_route_final_stage')->insert($data);
        Event::refresh_final_points_all_participant_in_final($event->id);
        return $this->response()->success('Результат успешно внесен')->refresh();
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
            if($event->is_france_system_qualification) {
                $category_id = ResultRouteFranceSystemQualification::where('event_id', '=', $event->id)->where('user_id', '=', $user)->first()->category_id;
            } else {
                $category_id = ResultQualificationClassic::where('event_id', $event->id)->where('user_id', $user)->first()->category_id;
            }
            $category = ParticipantCategory::find($category_id)->category;
            $result[$index] = $res.' ['.$category.']';
            if(in_array($index, $result_final)){
                $result[$index] = $res.' ['.$category.']'.' [Уже добавлен]';
            }
        }
        $this->select('user_id', 'Участник')->options($result)->required();
        $this->hidden('event_id', '')->value($event->id);
        for($i = 1; $i <= $event->amount_routes_in_final; $i++){
            $this->hidden('final_route_id_'.$i, 'Трасса'.$i)->value($i);
            $this->integer('show_final_route_id_'.$i, 'Трасса '.$i);
            $this->integer('amount_try_top_'.$i, 'Попытки на топ');
            $this->integer('amount_try_zone_'.$i, 'Попытки на зону');
        }
        Admin::script("// Получаем все элементы с атрибутом modal
        const elementsWithModalAttribute = document.querySelectorAll('[modal=\"app-admin-actions-resultroutefinalstage-batchresultfinalcustom\"]');
        const elementsWithIdAttribute = document.querySelectorAll('[id=\"app-admin-actions-resultroutefinalstage-batchresultfinalcustom\"]');

        // Создаем объект для отслеживания счетчика для каждого modal
        const modalCounters = {};
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
    }

    public function html()
    {
       return "<a class='result-add-final btn btn-sm btn-primary'><i class='fa fa-plus-circle'></i> {$this->category->category}</a>
                 <style>
                 .result-add-final {margin-top:8px;}
                 @media screen and (max-width: 767px) {
                        .result-add-final {margin-top:8px;}
                    }
                </style>
            ";
    }

}
