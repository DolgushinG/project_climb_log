<?php

namespace App\Admin\Actions\ResultRouteQualificationLikeFinalStage;

use App\Admin\Controllers\ResultRouteSemiFinalStageController;
use App\Models\Event;
use App\Models\Participant;
use App\Models\ParticipantCategory;
use App\Models\ResultRouteFinalStage;
use App\Models\ResultRouteQualificationLikeFinal;
use App\Models\ResultRouteSemiFinalStage;
use App\Models\ResultSemiFinalStage;
use App\Models\User;
use Encore\Admin\Actions\Action;
use Encore\Admin\Actions\BatchAction;
use Encore\Admin\Facades\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BatchResultQualificationLikeFinal extends Action
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
                'category_id' => intval($results['category_id']),
                'event_id' => intval($results['event_id']),
                'route_id' => intval($results['route_id_'.$i]),
                'amount_top' => $amount_top,
                'amount_try_top' => intval($results['amount_try_top_'.$i]),
                'amount_zone' => $amount_zone,
                'amount_try_zone' => intval($results['amount_try_zone_'.$i]),
            );
        }
        DB::table('result_route_qualification_like_final')->insert($data);
        return $this->response()->success('Результат успешно внесен')->refresh();
    }

    public function form()
    {
        $this->modalSmall();
        $event = Event::where('owner_id', '=', \Encore\Admin\Facades\Admin::user()->id)
            ->where('active', '=', 1)->first();
        $participant_users_id = Participant::where('event_id', '=', $event->id)->pluck('user_id')->toArray();
        $result = User::whereIn('id', $participant_users_id)->pluck( 'middlename','id');
        $result_qualification_like_final = ResultRouteQualificationLikeFinal::where('event_id', '=', $event->id)->select('user_id')->distinct()->pluck('user_id')->toArray();
        foreach ($result as $index => $res){
            $user = User::where('middlename', $res)->first()->id;
            $category_id = Participant::where('event_id', $event->id)->where('user_id', $user)->first()->category_id;
            $category = ParticipantCategory::find($category_id)->category;
            $result[$index] = $res.' ['.$category.']';
            if(in_array($index, $result_qualification_like_final)){
                $result[$index] = $res.' ['.$category.']'.' [Уже добавлен]';
            }
        }
        $this->select('user_id', 'Участник')->options($result)->required(true);
        $this->hidden('event_id', '')->value($event->id);
        $this->select('category_id', 'Категория')
            ->options((new \App\Models\ParticipantCategory)->getUserCategory(Admin::user()->id))->required(true);
        for($i = 1; $i <= $event->amount_routes_in_final; $i++){
            $this->integer('route_id_'.$i, 'Трасса')->value($i)->readOnly();
            $this->integer('amount_try_top_'.$i, 'Попытки на топ');
            $this->integer('amount_try_zone_'.$i, 'Попытки на зону');
        }

    }

    public function html()
    {
        return "<a class='send-add btn btn-sm btn-success'><i class='fa fa-arrow-down'></i> Внести результат</a>";
    }

}
