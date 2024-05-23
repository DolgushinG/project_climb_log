<?php

namespace App\Admin\Actions\ResultRouteSemiFinalStage;

use App\Models\Event;
use Encore\Admin\Actions\Action;
use Illuminate\Http\Request;

class BatchExportProtocolRouteParticipantSemiFinal extends Action
{
    public $name = 'Карточки для полуфиналистов';

    protected $selector = '.protocol-route-semifinal';

    public function handle(Request $request)
    {
        $event = Event::where('owner_id', '=', \Encore\Admin\Facades\Admin::user()->id)->where('active', 1)->first();
        return $this->response()->download('exports/events/'.$event->id.'/semifinal/participants')->success('успешно');
    }
    public function dialog()
    {
        $this->confirm('Создать карточки для полуфиналистов?');
    }

    public function html()
    {
        return "<a class='protocol-route-semifinal btn btn-sm btn-info'><i class='fa fa-file-archive'></i> $this->name</a>
                    <style>
                .protocol-route-semifinal {margin-top:8px;}
                 @media screen and (max-width: 767px) {
                        .protocol-route {margin-top:8px;}
                    }
                </style>
                ";
    }

}
