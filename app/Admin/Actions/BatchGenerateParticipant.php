<?php

namespace App\Admin\Actions;

use App\Admin\CustomAction\ActionExport;
use App\Exports\QualificationResultExport;
use App\Helpers\Generators\Generators;
use App\Models\Event;
use App\Models\Participant;
use App\Models\ParticipantCategory;
use App\Models\ResultSemiFinalStage;
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
        $event_id = $request->title;
        $count = intval($request->count);
        $event = Event::find($event_id);
        if($event->is_qualification_counting_like_final){
            $table_result = 'result_qualification_like_final';
            $table_result_routes = 'result_route_qualification_like_final';
            $text = 'готово';

        } else {
            $table_result = 'participants';
            $table_result_routes = 'result_participant';
            $text = 'Обязательно пересчитайте результаты для правильного расставление мест';
        }
        DB::table($table_result_routes)->truncate();
        $part_category = ParticipantCategory::where('event_id', $event->id)->get();
        $amount_categories = count($event->categories);
        $parts = intval($count / $amount_categories);
        $next = $parts;
        $start = 1;
        foreach($part_category as $category){
            Generators::prepare_participant_with_owner($owner_id, $event->id, $next, $category->category, $start, $table_result);
            $next = $next+$parts;
            $start = $start+$parts;
        }
        Generators::prepare_result_participant($owner_id, $event->id,$table_result_routes, $count);
        return $this->response()->success($text)->refresh();
    }

    public function form()
    {
        $this->modalSmall();
        $events = Event::where('owner_id', '=', \Encore\Admin\Facades\Admin::user()->id)->where('active', 1)->get()->pluck('title', 'id')->toArray();
        $this->integer('count', 'Сколько участников');
        $this->select('title', 'Сореванование')->options($events);
    }

    public function html()
    {
        return "<a class='generate-participant btn btn-sm btn-warning'><i class='fa fa-gears'></i> Сгенерировать участников [beta](Ожидание до ~ 2 мин)</a>";
    }

}
