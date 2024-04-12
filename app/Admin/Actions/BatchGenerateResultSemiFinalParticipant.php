<?php

namespace App\Admin\Actions;

use App\Admin\CustomAction\ActionExport;
use App\Exports\QualificationResultExport;
use App\Helpers\Generators\Generators;
use App\Models\Event;
use App\Models\Participant;
use App\Models\ParticipantCategory;
use App\Models\ResultSemiFinalStage;
use Encore\Admin\Actions\Action;
use Encore\Admin\Actions\BatchAction;
use Encore\Admin\Facades\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class BatchGenerateResultSemiFinalParticipant extends Action
{
    public $name = 'Сгенерировать результаты полуфинала участников[beta]';

    protected $selector = '.generate-result-semifinal-participant';

    public function handle(Request $request)
    {
        $owner_id = \Encore\Admin\Facades\Admin::user()->id;
        $event_id = $request->title;
        $event = Event::find($event_id);
        if($event->is_qualification_counting_like_final){
            Generators::prepare_result_participant($owner_id, $event->id,'result_route_qualification_like_final');
        } else {
            Generators::prepare_result_participant($owner_id, $event->id,'result_route_semifinal_stage');
        }

        return $this->response()->success('Готово')->refresh();
    }

    public function form()
    {
        $this->modalSmall();
        $events = Event::where('owner_id', '=', \Encore\Admin\Facades\Admin::user()->id)->where('active', 1)->get()->pluck('title', 'id')->toArray();
        $this->select('title', 'Сореванование')->options($events);
    }

    public function html()
    {
        return "<a class='generate-result-semifinal-participant btn btn-sm btn-warning'><i class='fa fa-trophy'></i> Сгенерировать результаты[beta]</a>
            <style>
                 .generate-result-semifinal-participant {margin-top:8px;}
                 @media screen and (max-width: 767px) {
                    .generate-result-semifinal-participant {margin-top:8px;}
                    }
            </style>
        ";
    }

}
