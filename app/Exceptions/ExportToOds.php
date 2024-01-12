<?php

namespace App\Exceptions;
use App\Models\Event;
use Encore\Admin\Actions\Action;
use Encore\Admin\Facades\Admin;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class ExportToOds extends Action
{
    protected $selector = '.report-participant';

    public function handle(Request $request)
    {
        return $this->response()->download('exports/events/ods/'.$request->id);
    }

    public function form()
    {
        $events = Event::where('owner_id', '=', Admin::user()->id)->get()->pluck('title', 'id');

        $this->select('id', 'Соревнование')->options($events);
    }

    public function html()
    {
        return <<<EOT
            <a class="btn report-participant btn-group-xs btn-success">
                <i class="fa fa-file-excel-o"></i> Экспорт в ODS
            </a>&nbsp;
            EOT;

    }
}
