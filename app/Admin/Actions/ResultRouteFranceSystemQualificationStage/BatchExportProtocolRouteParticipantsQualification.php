<?php

namespace App\Admin\Actions\ResultRouteFranceSystemQualificationStage;

use App\Models\Event;
use App\Models\ParticipantCategory;
use App\Models\Set;
use Encore\Admin\Actions\Action;
use Encore\Admin\Facades\Admin;
use Illuminate\Http\Request;

class BatchExportProtocolRouteParticipantsQualification extends Action
{
    public $name = 'Протокол трассы';

    protected $selector = '.protocol-route';

    public function handle(Request $request)
    {
        $event = Event::where('owner_id', '=', \Encore\Admin\Facades\Admin::user()->id)->where('active', 1)->first();
        return $this->response()->download('exports/events/'.$event->id.'/qualification/'.$request->set_id)->success('Успешно');
    }

    public function form()
    {
        $this->modalSmall();
        $event = Event::where('owner_id', '=', \Encore\Admin\Facades\Admin::user()->id)->where('active', 1)->first();
        $sets = Set::where('event_id', $event->id)->get()->sortBy('number_set');
        $sets_for = array();
        foreach ($sets as $set){
            $sets_for[$set->id] = $set->number_set.' ['.$set->time.']['.trans_choice('somewords.' . $set->day_of_week, 10).']';
        }
        $this->select('set_id', 'Из какого сета брать список участников?')->options($sets_for)->required();
    }
    public function html()
    {
        return "<a class='protocol-route btn btn-sm btn-info'><i class='fa fa-file-archive'></i> $this->name</a>
                    <style>
                .protocol-route {margin-top:8px;}
                 @media screen and (max-width: 767px) {
                        .protocol-route {margin-top:8px;}
                    }
                </style>
                ";
    }

}
