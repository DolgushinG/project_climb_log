<?php

namespace App\Admin\Actions;

use App\Admin\CustomAction\ActionExport;
use App\Exports\QualificationResultExport;
use App\Helpers\Generators\Generators;
use App\Models\Event;
use App\Models\Participant;
use App\Models\ParticipantCategory;
use App\Models\ResultParticipant;
use App\Models\ResultQualificationLikeFinal;
use App\Models\ResultRouteQualificationLikeFinal;
use App\Models\ResultSemiFinalStage;
use App\Models\Route;
use Encore\Admin\Actions\Action;
use Encore\Admin\Actions\BatchAction;
use Encore\Admin\Facades\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class BatchGenerateParticipant extends Action
{
    public $name = 'Сгенерировать участников[beta](Ожидание до ~ 2 мин)';

    protected $selector = '.generate-participant';

    public function handle(Request $request)
    {
        $owner_id = \Encore\Admin\Facades\Admin::user()->id;
        $event = Event::where('owner_id', '=', \Encore\Admin\Facades\Admin::user()->id)->where('active', 1)->first();
        $count = intval($request->count);
        if($event->is_qualification_counting_like_final){
            $table_result = 'result_qualification_like_final';
            $table_result_routes = 'result_route_qualification_like_final';
            $text = 'готово';
            ResultRouteQualificationLikeFinal::where('event_id',  $event->id)->delete();
            ResultQualificationLikeFinal::where('event_id',  $event->id)->delete();
        } else {
            $table_result = 'participants';
            $table_result_routes = 'result_participant';
            $text = 'Обязательно пересчитайте результаты для правильного расставление мест';
            Participant::where('event_id',  $event->id)->delete();
            ResultParticipant::where('event_id',  $event->id)->delete();
        }
        if($event->is_auto_categories){
            Generators::prepare_participant_with_owner($owner_id, $event->id, $count, $table_result);
        } else {
            $part_category = ParticipantCategory::where('event_id', $event->id)->get();
            $amount_categories = count($event->categories);
            $parts = intval($count / $amount_categories);
            $next = $parts;
            $start = 1;
            foreach($part_category as $category){
                Generators::prepare_participant_with_owner($owner_id, $event->id, $next, $table_result, $start, $category->category);
                $next = $next+$parts;
                $start = $start+$parts;
            }
        }
        Generators::prepare_result_participant($owner_id, $event->id, $table_result_routes, $count);
        if($event->is_qualification_counting_like_final){
            Event::refresh_qualification_counting_like_final($event);
        } else {
            Event::refresh_final_points_all_participant($event);
//            Event::refresh_final_points_all_participant($event);
        }

        return $this->response()->success($text)->refresh();
    }

    public function form()
    {
        $this->modalSmall();
        $this->integer('count', 'Сколько участников');
    }

    public function html()
    {
        $event = Event::where('owner_id', '=', \Encore\Admin\Facades\Admin::user()->id)->where('active', 1)->first();
        $exist_routes = Route::where('event_id', $event->id)->first();
        if($exist_routes){
            return "<a class='generate-participant btn btn-sm btn-warning '><i class='fa fa-trophy'></i> Сгенерировать участников [beta](Ожидание до ~ 2 мин)</a>
            <style>
                 .generate-participant {margin-top:8px;}
                @media screen and (max-width: 767px) {
                    .generate-participant {margin-top:8px;}
                    }
            </style>
        ";
        } else {
            return "<button class='generate-participant btn btn-sm btn-warning' disabled><i class='fa fa-trophy'></i> Сгенерировать участников (Неообходимо настроить трассы)</button>
            <style>
                 .generate-participant {margin-top:8px;}
                @media screen and (max-width: 767px) {
                    .generate-participant {margin-top:8px;}
                    }
            </style>
        ";
        }

    }

}
