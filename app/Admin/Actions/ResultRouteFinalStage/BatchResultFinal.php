<?php

namespace App\Admin\Actions\ResultRouteFinalStage;

use App\Models\Event;
use App\Models\Participant;
use App\Models\ResultSemiFinalStage;
use Encore\Admin\Actions\Action;
use Encore\Admin\Actions\BatchAction;
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
                'event_id' => intval($results['event_id']),
                'final_route_id' => intval($results['final_route_id_'.$i]),
                'amount_top' => $amount_top,
                'amount_try_top' => intval($results['amount_try_top_'.$i]),
                'amount_zone' => $amount_zone,
                'amount_try_zone' => intval($results['amount_try_zone_'.$i]),
            );
        }
        DB::table('result_route_final_stage')->insert($data);
        return $this->response()->success('Результат успешно внесен')->refresh();
    }

    public function form()
    {
        $this->modalSmall();
        $event = Event::where('owner_id', '=', \Encore\Admin\Facades\Admin::user()->id)
            ->where('active', '=', 1)->first();
        if($event->is_semifinal){
            $users_male = ResultSemiFinalStage::better_of_participants_semifinal_stage($event->id, 'male', 6);
            $users_female = ResultSemiFinalStage::better_of_participants_semifinal_stage($event->id, 'female', 6);
        } else {
            $users_male = Participant::better_participants($event->id, 'male', 6);
            $users_female = Participant::better_participants($event->id, 'female', 6);
        }
        $merged_users = $users_male->merge($users_female);
        $result = $merged_users->pluck( 'middlename','id');
        $this->select('user_id', 'Участник')->options($result);
        $this->hidden('event_id', '')->value($event->id);
        for($i = 1; $i <= $event->amount_routes_in_final; $i++){
            $this->integer('final_route_id_'.$i, 'Трасса')->value($i)->readOnly();
            $this->integer('amount_try_top_'.$i, 'Попытки на топ');
            $this->integer('amount_try_zone_'.$i, 'Попытки на зону');
        }

    }

    public function html()
    {
        return "<a class='send-add btn btn-sm btn-warning'><i class='fa fa-info-circle'></i>Внести результат</a>";
    }

}
