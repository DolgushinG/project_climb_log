<?php

namespace App\Admin\Actions;

use App\Models\Event;
use App\Models\ResultSemiFinalStage;
use Encore\Admin\Actions\Action;
use Illuminate\Http\Request;

class BatchForceRecoutingSemiFinalResultGender extends Action
{
    public $name = 'Пересчитать результаты c учетом только пола';

    protected $selector = '.recouting-gender';

    public function handle(Request $request)
    {
        $event = Event::where('owner_id', '=', \Encore\Admin\Facades\Admin::user()->id)->where('active', 1)->first();
        $event->is_sort_group_semifinal = 0;
        $event->save();

        $result_semifinal = ResultSemiFinalStage::where('event_id', $event->id)->first();
        if($result_semifinal){
            $result_semifinal->amount_try_top = null;
            $result_semifinal->amount_top = null;
            $result_semifinal->amount_try_zone = null;
            $result_semifinal->amount_zone = null;
            $result_semifinal->save();
        }


        Event::refresh_final_points_all_participant_in_semifinal($event->id);
        return $this->response()->success('Пересчитано')->refresh();
    }

    public function dialog()
    {
        $this->confirm('Подтвердить пересчет результата по полу');
    }
    public function html()
    {
        return "<a class='recouting-gender btn btn-sm btn-success'><i class='fa fa-female'></i><i class='fa fa-male'></i> Результаты </a>
         <style>
               .recouting-gender {margin-top:8px;}
              @media screen and (max-width: 767px) {
                    .recouting-gender {margin-top:8px;}
                }
            </style>
        ";
    }

}
