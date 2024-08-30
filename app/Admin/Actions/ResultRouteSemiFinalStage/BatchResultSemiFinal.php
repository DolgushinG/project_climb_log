<?php

namespace App\Admin\Actions\ResultRouteSemiFinalStage;

use App\Admin\Extensions\CustomAction;
use App\Helpers\Helpers;
use App\Models\Event;
use App\Models\ResultQualificationClassic;
use App\Models\ResultFranceSystemQualification;
use App\Models\ResultRouteSemiFinalStage;
use App\Models\ResultSemiFinalStage;
use App\Models\User;
use Encore\Admin\Actions\Action;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BatchResultSemiFinal extends CustomAction
{
    public $name = 'Внести результат полуфинала';

    protected $selector = '.result-add';

    public function handle(Request $request)
    {
        $results = $request->toArray();
        $event = Event::find($results['event_id']);
        $data = array();
        $result_for_edit = [];
        for($i = 1; $i <= $event->amount_routes_in_semifinal; $i++){
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
                Log::error('Category id not found -event_id - '.$results['event_id'].'user_id'.$results['user_id'], ['file' => __FILE__, 'line' => __LINE__]);
            }
            $gender = $participant->gender;
            if($event->is_open_main_rating && $event->is_auto_categories){
                $category_id = $participant->global_category_id;
            } else {
                $category_id = $participant->category_id;
            }
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

            $result_for_edit[] = array(
                'Номер маршрута' => intval($results['final_route_id_'.$i]),
                'Попытки на топ' => intval($results['amount_try_top_'.$i]),
                'Попытки на зону' => intval($results['amount_try_zone_'.$i])
            );
        }
        $result = ResultRouteSemiFinalStage::where('event_id', $results['event_id']
        )->where('user_id', $results['user_id'])->first();
        $user = User::find(intval($results['user_id']))->middlename;
        if($result){
            return $this->response()->error('Результат уже есть по '.$user);
        } else {
            $participant_semifinal = ResultSemiFinalStage::where('event_id', $results['event_id'])->where('user_id', $results['user_id'])->first();
            if(!$participant_semifinal){
                $participant_semifinal = new ResultSemiFinalStage;
            }
            $participant_semifinal->owner_id = \Encore\Admin\Facades\Admin::user()->id;
            $participant_semifinal->event_id = $results['event_id'];
            $participant_semifinal->user_id = $results['user_id'];
            $participant_semifinal->category_id = $category_id;
            $participant_semifinal->gender = $gender;
            $participant_semifinal->result_for_edit_semifinal = $result_for_edit;
            $participant_semifinal->save();

            DB::table('result_route_semifinal_stage')->insert($data);
            Event::refresh_final_points_all_participant_in_semifinal($event->id);
            return $this->response()->success('Результат успешно внесен')->refresh();
        }

    }

    public function custom_form()
    {
        $this->modalSmall();
        $event = Event::where('owner_id', '=', \Encore\Admin\Facades\Admin::user()->id)
            ->where('active', '=', 1)->first();
        $amount_the_best_participant = $event->amount_the_best_participant ?? 10;
        if($event->is_open_main_rating){
            $merged_users = ResultSemiFinalStage::get_global_participant_semifinal($event, $amount_the_best_participant);
        } else {
            $merged_users = ResultSemiFinalStage::get_participant_semifinal($event, $amount_the_best_participant);
        }
        $result = $merged_users->pluck( 'middlename','id');
        $result_semifinal = ResultRouteSemiFinalStage::where('event_id', '=', $event->id)->select('user_id')->distinct()->pluck('user_id')->toArray();
        foreach ($result as $index => $res){
            if(in_array($index, $result_semifinal)){
                $result[$index] = $res.' [Уже добавлен]';
            }
        }
        $this->select('user_id', 'Участник')->options($result)->required();
        $this->hidden('event_id', '')->value($event->id);
        for($i = 1; $i <= $event->amount_routes_in_semifinal; $i++){
            $this->integer('final_route_id_'.$i, 'Трасса')->value($i)->readOnly();
            $this->integer('amount_try_top_'.$i, 'Попытки на топ');
            $this->integer('amount_try_zone_'.$i, 'Попытки на зону');
        }

    }

    public function html()
    {
        $event = Event::where('owner_id', '=', \Encore\Admin\Facades\Admin::user()->id)
            ->where('active', '=', 1)->first();
        if($event->is_semifinal){
            return "<a class='result-add btn btn-sm btn-primary'><i class='fa fa-arrow-down'></i> Внести результат</a>
                <style>
                  .result-add {margin-top:8px;}
                 @media screen and (max-width: 767px) {
                         .result-add {margin-top:8px;}
                    }
                </style>

                ";


        } else {
            return "<a disabled class='result-add btn btn-sm btn-warning' style='display: none'><i class='fa fa-info-circle'></i> Внести результат</a>";
        }
    }

}
