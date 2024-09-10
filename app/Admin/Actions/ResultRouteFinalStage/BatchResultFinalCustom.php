<?php

namespace App\Admin\Actions\ResultRouteFinalStage;

use App\Admin\Extensions\CustomAction;
use App\Helpers\Helpers;
use App\Models\Event;
use App\Models\ResultFinalStage;
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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BatchResultFinalCustom extends CustomAction
{
    protected $selector = '.result-add';

    public $category;
    private mixed $script;

    public function __construct(ParticipantCategory $category, $script = '')
    {
        $this->initInteractor();
        $this->category = $category;
        $this->script = $script;
    }
    public function handle(Request $request)
    {
        $results = $request->toArray();
        $event = Event::find($results['event_id']);
        $data = array();
        $result_for_edit = array();
        for($i = 1; $i <= $event->amount_routes_in_final; $i++){
            if(intval($results['amount_try_top_'.$i]) > 0){
                $amount_top  = 1;
            } else {
                $amount_top  = 0;
            }
            if(intval($results['amount_try_zone_'.$i]) > 0){
                $amount_zone  = 1;
            } else {
                $amount_zone  = 0;
            }

            # Если есть ТОП то зона не может быть 0
            if(Helpers::validate_amount_top_and_zone($amount_top, $amount_zone)){
                return $this->response()->error('У трассы '.$i.' отмечен ТОП, и получается зона не может быть 0');
            }

            # Кол-во попыток на зону не может быть меньше чем кол-во на ТОП
            if(Helpers::validate_amount_try_top_and_zone($results['amount_try_top_'.$i], $results['amount_try_zone_'.$i])){
                return $this->response()->error('Кол-во попыток на зону не может быть меньше, чем кол-во попыток на ТОП, трасса '.$i );
            }

            if($event->is_france_system_qualification){
                $participant = ResultFranceSystemQualification::where('event_id', $results['event_id'])->where('user_id', $results['user_id'])->first();
            } else {
                $participant = ResultQualificationClassic::where('event_id', $results['event_id'])->where('user_id', $results['user_id'])->first();
            }
            if(!$participant){
                Log::error('Category id not found -event_id - '.$results['event_id'].'user_id'.$results['user_id']);
            }
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
                'final_route_id' => intval($results['final_route_id_'.$i]),
                'category_id' => $category_id,
                'amount_top' => $amount_top,
                'gender' => $gender,
                'amount_try_top' => intval($results['amount_try_top_'.$i]),
                'amount_zone' => $amount_zone,
                'amount_try_zone' => intval($results['amount_try_zone_'.$i]),
            );
            $result_for_edit[] = array(
                'Номер маршрута' => intval($results['final_route_id_'.$i]),
                'Попытки на топ' => intval($results['amount_try_top_'.$i]),
                'Попытки на зону' => intval($results['amount_try_zone_'.$i])
            );
        }
        $result = ResultRouteFinalStage::where('event_id', $results['event_id']
        )->where('user_id', $results['user_id'])->first();
        $user = User::find(intval($results['user_id']))->middlename;
        if($result) {
            return $this->response()->error('Результат уже есть по ' . $user);
        }
        DB::table('result_route_final_stage')->insert($data);
        Event::send_result_final(intval($results['event_id']), $owner_id, intval($results['user_id']), $category_id, $result_for_edit, $gender);
        Event::refresh_final_points_all_participant_in_final($event->id);
        return $this->response()->success('Результат успешно внесен')->refresh();
    }

    public function custom_form()
    {
        $this->modalSmall();
        $event = Event::where('owner_id', '=', \Encore\Admin\Facades\Admin::user()->id)
            ->where('active', '=', 1)->first();
        if($event->is_open_main_rating && $event->is_auto_categories){
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
                    $category_id = ResultQualificationClassic::where('event_id', $event->id)->where('user_id', $user_id)->first()->category_id;
                }
            }
            $category = ParticipantCategory::find($category_id)->category;
            $result[$user_id] = $middlename.' ['.$category.']';
            if(in_array($user_id, $result_final)){
                $result[$user_id] = $middlename.' ['.$category.']'.' [Уже добавлен]';
            }
        }
        Admin::script("// Получаем все элементы с атрибутом modal
        const elementsWithModalAttribute4 = document.querySelectorAll('[modal=\"app-admin-actions-resultroutefinalstage-batchresultfinalcustom\"]');
        const elementsWithIdAttribute4 = document.querySelectorAll('[id=\"app-admin-actions-resultroutefinalstage-batchresultfinalcustom\"]');

        // Создаем объект для отслеживания счетчика для каждого modal
        const modalCounters4 = {};
        const idCounters4 = {};

        // Перебираем найденные элементы
        elementsWithModalAttribute4.forEach(element => {
            const modalValue4 = element.getAttribute('modal');

            // Проверяем, существует ли уже счетчик для данного modal
            if (modalValue4 in modalCounters4) {
                // Если счетчик уже существует, инкрементируем его значение
                modalCounters4[modalValue4]++;
            } else {
                // Если счетчика еще нет, создаем его и устанавливаем значение 1
                modalCounters4[modalValue4] = 1;
            }

            // Получаем номер элемента для данного modal
            const elementNumber4 = modalCounters4[modalValue4];

            // Устанавливаем новое значение modal
            element.setAttribute('modal', `\${modalValue4}-\${elementNumber4}`);
        });
        elementsWithIdAttribute4.forEach(element => {
            const idValue4 = element.getAttribute('id');

            // Проверяем, существует ли уже счетчик для данного modal
            if (idValue4 in idCounters4) {
                // Если счетчик уже существует, инкрементируем его значение
                idCounters4[idValue4]++;
            } else {
                // Если счетчика еще нет, создаем его и устанавливаем значение 1
                idCounters4[idValue4] = 1;
            }

            // Получаем номер элемента для данного modal
            const elementNumber4 = idCounters4[idValue4];

            // Устанавливаем новое значение modal
            element.setAttribute('id', `\${idValue4}-\${elementNumber4}`);
        });

        ");
        $this->select('user_id', 'Участник')->options($result)->required();
        $this->hidden('event_id', '')->value($event->id);
        for($i = 1; $i <= $event->amount_routes_in_final; $i++){
            $this->integer('final_route_id_'.$i, 'Трасса')->value($i)->readOnly();
            $this->integer('amount_try_top_'.$i, 'Попытки на топ');
            $this->integer('amount_try_zone_'.$i, 'Попытки на зону');
        }
        \Encore\Admin\Facades\Admin::script($this->script);
    }

    public function html()
    {
       return "<a class='result-add btn btn-sm btn-primary'><i class='fa fa-plus-circle'></i> {$this->category->category}</a>
                 <style>
                 .result-add {margin-top:8px;}
                 @media screen and (max-width: 767px) {
                        .result-add {margin-top:8px;}
                    }
                </style>
            ";
    }

}
