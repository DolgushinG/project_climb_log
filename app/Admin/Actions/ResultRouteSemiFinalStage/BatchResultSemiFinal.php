<?php

namespace App\Admin\Actions\ResultRouteSemiFinalStage;

use App\Models\Event;
use App\Models\Participant;
use App\Models\ResultRouteSemiFinalStage;
use Encore\Admin\Actions\Action;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BatchResultSemiFinal extends Action
{
    public $name = 'Внести результат полуфинала';

    protected $selector = '.result-add';

    public function handle(Request $request)
    {
        $results = $request->toArray();
        $event = Event::find($results['event_id']);
        $data = array();
        for($i = 1; $i <= $event->amount_routes_in_semifinal; $i++){
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
        DB::table('result_route_semifinal_stage')->insert($data);
        return $this->response()->success('Результат успешно внесен')->refresh();
    }

    public function form()
    {
        $this->modalSmall();
        $event = Event::where('owner_id', '=', \Encore\Admin\Facades\Admin::user()->id)
            ->where('active', '=', 1)->first();
        $users_male = Participant::better_participants($event->id, 'male', 10);
        $users_female = Participant::better_participants($event->id, 'female', 10);
        $merged_users = $users_male->merge($users_female);
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
            return "<a class='result-add btn btn-sm btn-warning'><i class='fa fa-info-circle'></i> Внести результат</a>";
        } else {
            return "<a disabled class='result-add btn btn-sm btn-warning' style='display: none'><i class='fa fa-info-circle'></i> Внести результат</a>";
        }
    }

}
