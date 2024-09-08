<?php

namespace App\Admin\Actions;

use App\Helpers\Generators\Generators;
use App\Helpers\Helpers;
use App\Models\Event;
use App\Models\Grades;
use App\Models\ResultQualificationClassic;
use App\Models\ParticipantCategory;
use App\Models\ResultRouteQualificationClassic;
use App\Models\ResultFranceSystemQualification;
use App\Models\ResultRouteFranceSystemQualification;
use App\Models\Route;
use App\Models\User;
use Encore\Admin\Actions\Action;
use Encore\Admin\Facades\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class BatchGenerateParticipant extends Action
{
    public $name = 'Сгенерировать участников[beta](Ожидание до ~ 2 мин)';

    protected $selector = '.generate-participant';

    public function handle(Request $request)
    {
        $owner_id = \Encore\Admin\Facades\Admin::user()->id;
        $event = Event::where('owner_id', $owner_id)->where('active', 1)->first();
        $count = intval($request->count);
        if($event->is_france_system_qualification){
            $table_result = 'result_france_system_qualification';
            $table_result_routes = 'result_route_france_system_qualification';
            $text = 'готово';
            ResultRouteFranceSystemQualification::where('event_id',  $event->id)->delete();
            ResultFranceSystemQualification::where('event_id',  $event->id)->delete();
        } else {
            $table_result = 'result_qualification_classic';
            $table_result_routes = 'result_route_qualification_classic';
            $text = 'Обязательно пересчитайте результаты для правильного расставление мест';
            ResultQualificationClassic::where('event_id',  $event->id)->delete();
            ResultRouteQualificationClassic::where('event_id',  $event->id)->delete();
        }
        $start_number_participant = User::first();
        if($event->is_auto_categories){
            Generators::prepare_participant_with_owner($owner_id, $event->id, $count, $table_result, $start_number_participant->id);
        } else {
            $part_category = ParticipantCategory::where('event_id', $event->id)->get();
            $amount_categories = count($event->categories);
            $parts = intval($count / $amount_categories);
            $next = $parts;
            $start = $start_number_participant->id;

            foreach($part_category as $category){
                Generators::prepare_participant_with_owner($owner_id, $event->id, $next, $table_result, $start, $category->category);
                $next = $next+$parts;
                $start = $start+$parts;

            }
        }

        Generators::prepare_result_route_qualification_classic($owner_id, $event->id, $table_result_routes, $count);
        if($event->is_france_system_qualification){
            Event::refresh_france_system_qualification_counting($event);

        } else {
            Event::refresh_final_points_all_participant($event);
//            Event::refresh_final_points_all_participant($event);
        }

        Helpers::clear_cache($event);
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
        if($event->is_france_system_qualification){
            $is_enabled = Grades::where('event_id', $event->id)->first();
        } else {
            $is_enabled = Route::where('event_id', $event->id)->first();
        }
        if($is_enabled){
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
