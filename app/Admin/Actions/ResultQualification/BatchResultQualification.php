<?php

namespace App\Admin\Actions\ResultQualification;

use App\Models\Event;
use Encore\Admin\Actions\Action;
use Illuminate\Http\Request;

class BatchResultQualification extends Action
{
    public $name = 'Экспорт';

    protected $selector = '.export';

    public function handle(Request $request)
    {
        $event = Event::where('owner_id', '=', \Encore\Admin\Facades\Admin::user()->id)->where('active', 1)->first();
        return $this->response()->download('exports/events/'.$request->format_export.'/qualification/'.$event->id);
    }

    public function form()
    {
        $this->modalSmall();
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
