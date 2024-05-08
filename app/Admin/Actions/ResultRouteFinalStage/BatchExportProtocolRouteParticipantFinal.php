<?php

namespace App\Admin\Actions\ResultRouteFinalStage;

use App\Models\Event;
use App\Models\ParticipantCategory;
use App\Models\Set;
use Encore\Admin\Actions\Action;
use Encore\Admin\Facades\Admin;
use Illuminate\Http\Request;

class BatchExportProtocolRouteParticipantFinal extends Action
{
    public $name = 'Карточки для финалистов';

    protected $selector = '.protocol-route-final';

    public function handle(Request $request)
    {
        $event = Event::where('owner_id', '=', \Encore\Admin\Facades\Admin::user()->id)->where('active', 1)->first();
        return $this->response()->download('exports/events/'.$event->id.'/final/participants')->success('успешно');
    }
    public function dialog()
    {
        $this->confirm('Создать карточки для финалистов?');
    }

    public function html()
    {
        return "<a class='protocol-route-final btn btn-sm btn-info'><i class='fa fa-file-archive'></i> $this->name</a>
                    <style>
                .protocol-route-final {margin-top:8px;}
                 @media screen and (max-width: 767px) {
                        .protocol-route {margin-top:8px;}
                    }
                </style>
                ";
    }

}
