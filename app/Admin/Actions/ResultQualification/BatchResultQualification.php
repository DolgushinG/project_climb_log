<?php

namespace App\Admin\Actions\ResultQualification;

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

class BatchResultQualification extends Action
{
    public $name = 'Экспорт';

    protected $selector = '.export';

    public function handle(Request $request)
    {
        return $this->response()->success('Success!')->download('exports/events/'.$request->format_export.'/qualification/'.$request->title);
    }

    public function form()
    {
        $this->modalSmall();
        $events = Event::where('owner_id', '=', \Encore\Admin\Facades\Admin::user()->id)->get()->pluck('title','id');
        $this->select('title', 'Сореванование')->options($events);
        $this->radio('format_export', 'Какой формат')->options(
            [
                "csv" => "csv",
                "ods" => "ods",
                "excel" => "excel"
            ]);
    }

    public function html()
    {
        return "<a class='export btn btn-sm btn-warning'><i class='fa fa-info-circle'></i>Экспорт</a>";
    }

}
