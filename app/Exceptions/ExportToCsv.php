<?php

namespace App\Exceptions;
use App\Models\Event;
use App\Admin\CustomView\Actions\Action;
use App\Admin\CustomView\Facades\Admin;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class ExportToCsv extends Action
{
    protected $selector = '.report-participant';

    public function handle(Request $request)
    {
        return $this->response()->download('exports/events/csv/'.$request->id);
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
                <i class="fa fa-file-excel-o"></i> Экспорт в CSV
            </a>&nbsp;
            EOT;

    }
}
