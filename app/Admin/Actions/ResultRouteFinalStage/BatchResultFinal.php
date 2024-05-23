<?php

namespace App\Admin\Actions\ResultRouteFinalStage;

use App\Models\Event;
use App\Models\ResultQualificationClassic;
use App\Models\ResultFinalStage;
use App\Models\ResultFranceSystemQualification;
use App\Models\ResultRouteFinalStage;
use Encore\Admin\Actions\Action;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BatchResultFinal extends Action
{
    public $name = 'Внести результат финала';

    protected $selector = '.send-add';

    public function handle(Request $request)
    {
        $results = $request->toArray();
        $event = Event::find($results['event_id']);
        if($event->is_france_system_qualification){
            $participant = ResultFranceSystemQualification::where('event_id', $results['event_id'])->where('user_id', $results['user_id'])->first();
        } else {
            $participant = ResultQualificationClassic::where('event_id', $results['event_id'])->where('user_id', $results['user_id'])->first();
        }
        $category_id = $participant->category_id;
        $gender = $participant->gender;
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
            $data[] = array('owner_id' => \Encore\Admin\Facades\Admin::user()->id,
                'user_id' => intval($results['user_id']),
                'category_id' => $category_id,
                'event_id' => intval($results['event_id']),
                'gender' => $gender,
                'final_route_id' => intval($results['final_route_id_'.$i]),
                'amount_top' => $amount_top,
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
        $merged_users = ResultFinalStage::get_final_participant($event);
        $result = $merged_users->pluck( 'middlename','id');
        $result_semifinal = ResultRouteFinalStage::where('event_id', '=', $event->id)->select('user_id')->distinct()->pluck('user_id')->toArray();
        foreach ($result as $index => $res){
            if(in_array($index, $result_semifinal)){
                $result[$index] = $res.' [Уже добавлен]';
            }
        }
        $this->select('user_id', 'Участник')->options($result)->required(true);
        $this->hidden('event_id', '')->value($event->id);
        for($i = 1; $i <= $event->amount_routes_in_final; $i++){
            $this->integer('final_route_id_'.$i, 'Трасса')->value($i)->readOnly();
            $this->integer('amount_try_top_'.$i, 'Попытки на топ');
            $this->integer('amount_try_zone_'.$i, 'Попытки на зону');
        }

    }

    public function html()
    {
        return "<a class='send-add btn btn-sm btn-primary'><i class='fa fa-arrow-down'></i> Внести результат</a>
                <style>
                  .send-add {margin-top:8px;}
                 @media screen and (max-width: 767px) {
                         .send-add {margin-top:8px;}
                    }
                </style>
            ";
    }

}
