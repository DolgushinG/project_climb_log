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

class BatchForceRecouting extends Action
{
    public $name = 'Пересчитать результаты';

    protected $selector = '.recouting';

    public function handle(Request $request)
    {
        $event_id = $request->title;
        Event::refresh_final_points_all_participant(intval($event_id));
        return $this->response()->success('Пересчитано')->refresh();
    }

    public function form()
    {
        $this->modalSmall();
        $events = Event::where('owner_id', '=', \Encore\Admin\Facades\Admin::user()->id)->get()->pluck('title','id');
        $this->select('title', 'Сореванование')->options($events);
    }

    public function html()
    {
        return "<a class='recouting btn btn-sm btn-success'><i class='fa fa-fast-backward'></i> Пересчитать результаты</a>";
    }

}
