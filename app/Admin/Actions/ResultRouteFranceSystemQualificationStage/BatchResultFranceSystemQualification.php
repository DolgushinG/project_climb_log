<?php

namespace App\Admin\Actions\ResultRouteFranceSystemQualificationStage;

use App\Models\Event;
use App\Models\Grades;
use App\Models\ParticipantCategory;
use App\Models\ResultFranceSystemQualification;
use App\Models\ResultRouteFinalStage;
use App\Models\ResultRouteFranceSystemQualification;
use App\Models\User;
use Encore\Admin\Actions\Action;
use Encore\Admin\Admin;
use Encore\Admin\Form;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BatchResultFranceSystemQualification extends Action
{
    public $name = 'Внести результат квалификации';

    public function __construct()
    {
        $this->initInteractor();
    }
    protected $selector = '.send-add';
    public function handle(Request $request)
    {
        $results = $request->toArray();
        $event = Event::find($results['event_id']);
        $grades = Grades::where('event_id', $event->id)->first();
        $result_qualification_like = ResultFranceSystemQualification::where('event_id', $results['event_id'])->where('user_id', $results['user_id'])->first();
        $data = array();
        $result_for_edit = [];
        for($i = 1; $i <= $grades->count_routes; $i++){
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
            $data[] = array('owner_id' => \Encore\Admin\Facades\Admin::user()->id,
                'user_id' => intval($results['user_id']),
                'category_id' => $result_qualification_like->category_id,
                'event_id' => intval($results['event_id']),
                'route_id' => intval($results['route_id_'.$i]),
                'gender' => $result_qualification_like->gender,
                'amount_top' => $amount_top,
                'amount_try_top' => intval($results['amount_try_top_'.$i]),
                'amount_zone' => $amount_zone,
                'amount_try_zone' => intval($results['amount_try_zone_'.$i]),
            );
            $result_for_edit[] = array(
                'Номер маршрута' => intval($results['route_id_'.$i]),
                'Попытки на топ' => intval($results['amount_try_top_'.$i]),
                'Попытки на зону' => intval($results['amount_try_zone_'.$i])
            );
        }
        $result = ResultRouteFranceSystemQualification::where('event_id', $results['event_id']
        )->where('user_id', $results['user_id'])->first();
        $user = User::find(intval($results['user_id']))->middlename;
        if($result){
            return $this->response()->error('Результат уже есть по '.$user);
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
        $this->modalLarge();
        $event = Event::where('owner_id', '=', \Encore\Admin\Facades\Admin::user()->id)
            ->where('active', '=', 1)->first();
        $grades = Grades::where('event_id', $event->id)->first();
        $participant_users_id = ResultFranceSystemQualification::where('event_id', '=', $event->id)->pluck('user_id')->toArray();
        $result = User::whereIn('id', $participant_users_id)->pluck('middlename','id');
        $result_france_system_qualification = ResultRouteFranceSystemQualification::where('event_id', '=', $event->id)->select('user_id')->distinct()->pluck('user_id')->toArray();
        foreach ($result as $index => $res){
            $user = User::where('middlename', $res)->first()->id;
            $res_fra = ResultFranceSystemQualification::where('event_id', $event->id)->where('user_id', $user)->first();
            if(!$res_fra){
                Log::error('Category id not found -event_id - '.$event->id.'user_id'.$user);
            }
            $category_id = $res_fra->category_id;
            $category = ParticipantCategory::find($category_id)->category;
            $result[$index] = $res.' ['.$category.']';
            if(in_array($index, $result_france_system_qualification)){
                $result[$index] = $res.' ['.$category.']'.' [Уже добавлен]';
            }
        }

        $this->hidden('event_id', '')->value($event->id);
        $this->hidden('event_for_style', '')->value($event->id);
        $this->select('user_id', 'Участник')->options($result)->required(true);
        if($grades){
            for($i = 1; $i <= $grades->count_routes; $i++){
                $this->integer('route_id_'.$i, 'Трасса')->value($i)->readOnly();
                $this->integer('amount_try_top_'.$i, 'Попытки на топ');
                $this->integer('amount_try_zone_'.$i, 'Попытки на зону');

            }
        }


    }

    public function html()
    {
        $event = Event::where('owner_id', '=', \Encore\Admin\Facades\Admin::user()->id)
            ->where('active', '=', 1)->first();
        $grades = Grades::where('event_id', $event->id)->first();
        if($grades){
            return "<a class='send-add btn btn-sm btn-success'><i class='fa fa-arrow-down'></i> Внести результат</a>
                <style>
                    .send-add {margin-top:8px;}
                 @media screen and (max-width: 767px) {
                        .send-add {margin-top:8px;}
                    }
                </style>

            ";
        } else {
            return "<a class='send-add btn btn-sm btn-success disabled'><i class='fa fa-arrow-down'></i>Внести результат (Необходимо настроить трассы) </a>
                <style>
                    .send-add {margin-top:8px;}
                 @media screen and (max-width: 767px) {
                        .send-add {margin-top:8px;}
                    }
                </style>

            ";
        }


    }

}
