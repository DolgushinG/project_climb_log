<?php

namespace App\Admin\Actions\ResultRouteFinalStage;

use App\Admin\CustomAction\ActionExport;
use App\Exports\ExportProtocolRouteParticipant;
use App\Exports\QualificationResultExport;
use App\Models\Event;
use App\Models\Participant;
use App\Models\ParticipantCategory;
use App\Models\ResultQualificationLikeFinal;
use App\Models\ResultSemiFinalStage;
use App\Models\Set;
use App\Models\User;
use Encore\Admin\Actions\Action;
use Encore\Admin\Actions\BatchAction;
use Encore\Admin\Facades\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use function Symfony\Component\String\s;

class BatchExportProtocolRouteParticipantFinal extends Action
{
    public $name = 'Протокол трассы';

    protected $selector = '.protocol-route';

    public function handle(Request $request)
    {
        $event = Event::where('owner_id', '=', \Encore\Admin\Facades\Admin::user()->id)->where('active', 1)->first();
        if($request->category_id){
            $category_id = $request->category_id;
        } else {
            $category_id = 'not_category';
        }
        return $this->response()->download('exports/events/'.$event->id.'/final/'.$request->gender.'/'.$category_id);
    }

    public function form()
    {
        $this->modalSmall();
        $this->select('gender', 'Пол')->options(['male' => 'Муж', 'female' => 'Жен'])->required();
        $event = Event::where('owner_id', '=', \Encore\Admin\Facades\Admin::user()->id)->where('active', 1)->first();
        $categories = ParticipantCategory::where('event_id', $event->id)->pluck('category', 'id');
        $this->select('category_id', 'Категория')->options($categories);
    }
    public function html()
    {
        return "<a class='protocol-route btn btn-sm btn-warning'><i class='fa fa-file-archive'></i> $this->name</a>
                    <style>
                .protocol-route {margin-top:8px;}
                 @media screen and (max-width: 767px) {
                        .protocol-route {margin-top:8px;}
                    }
                </style>
                ";
    }

}
