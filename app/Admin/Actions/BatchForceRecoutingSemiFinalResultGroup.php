<?php

namespace App\Admin\Actions;

use App\Admin\CustomAction\ActionExport;
use App\Exports\QualificationResultExport;
use App\Models\Event;
use App\Models\ResultSemiFinalStage;
use Encore\Admin\Actions\Action;
use Encore\Admin\Actions\BatchAction;
use Encore\Admin\Facades\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class BatchForceRecoutingSemiFinalResultGroup extends Action
{
    public $name = 'Пересчитать результаты c учетом пола и категории участников';

    protected $selector = '.recouting-group';

    public function handle(Request $request)
    {
        $event_id = $request->title;
        $event = Event::find(intval($event_id));
        $event->is_additional_semifinal = 1;
        $event->save();
        ResultSemiFinalStage::where('event_id', intval($event_id))->delete();
        Event::refresh_final_points_all_participant_in_semifinal(intval($event_id));
        return $this->response()->success('Пересчитано')->refresh();
    }

    public function form()
    {
        $this->modalSmall();
        $events = Event::where('owner_id', '=', \Encore\Admin\Facades\Admin::user()->id)->where('active', 1)->get()->pluck('title','id');
        $this->select('title', 'Сореванование')->options($events);
    }

    public function html()
    {
        return "<a class='recouting-group btn btn-sm btn-success'><i class='fa fa-users'></i></i> Результаты<</a>
         <style>
                @media screen and (max-width: 767px) {
                        .recouting-group {margin-top:8px;}
                    }
            </style>
        ";
    }

}
