<?php

namespace App\Admin\Actions\ResultRouteQualificationLikeFinalStage;

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

class BatchExportResultQualificationLikeFinal extends Action
{
    public $name = 'Экспорт';

    protected $selector = '.export';

    public function handle(Request $request)
    {
        return $this->response()->download('exports/events/'.$request->format_export.'/qualification-final/'.$request->title);
    }

    public function form()
    {
        $this->modalSmall();
        $events = Event::where('owner_id', '=', \Encore\Admin\Facades\Admin::user()->id)->where('active', 1)->get()->pluck('title','id');
        $this->select('title', 'Сореванование')->options($events);
        $this->radio('format_export', 'Какой формат')->options(
            [
                "excel" => "excel"
            ]);
    }

    public function html()
    {
        return "<a class='export btn btn-sm btn-primary'><i class='fa fa-arrow-up'></i> Экспорт</a>
                   <style>
                    .export {margin-top:8px;}
                 @media screen and (max-width: 767px) {
                        .export {margin-top:8px;}
                    }
                </style>
                ";
    }

}
