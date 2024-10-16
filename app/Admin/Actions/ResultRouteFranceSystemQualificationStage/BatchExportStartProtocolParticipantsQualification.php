<?php

namespace App\Admin\Actions\ResultRouteFranceSystemQualificationStage;

use App\Models\Event;
use App\Models\ParticipantCategory;
use App\Models\Set;
use Encore\Admin\Actions\Action;
use Encore\Admin\Facades\Admin;
use Illuminate\Http\Request;

class BatchExportStartProtocolParticipantsQualification extends Action
{
    public $name = 'Стартовый протокол участников';

    protected $selector = '.start-protocol-participant';

    public function handle(Request $request)
    {
        $event = Event::where('owner_id', '=', \Encore\Admin\Facades\Admin::user()->id)->where('active', 1)->first();
        if($request->category_id){
            $category_id = $request->category_id;
        } else {
            $category_id = 'not_category';
        }
        return $this->response()->download('exports/start-protocol/events/'.$event->id.'/participant/'.$category_id)->success('Успешно');
    }

    public function form()
    {
        $this->modalSmall();
//        $event = Event::where('owner_id', '=', \Encore\Admin\Facades\Admin::user()->id)->where('active', 1)->first();
//        $sets = Set::where('event_id', $event->id)->get()->sortBy('number_set');
//        $sets_for = array();
//        foreach ($sets as $set){
//            $sets_for[$set->id] = $set->number_set.' ['.$set->time.']['.$set->day_of_week.']';
//        }
//        $this->select('set_id', 'Из какого сета брать список участников?')->options($sets_for)->required();
//        $this->select('gender', 'Пол')->options(['male' => 'Муж', 'female' => 'Жен'])->required();
        $event = Event::where('owner_id', '=', \Encore\Admin\Facades\Admin::user()->id)->where('active', 1)->first();
        $categories = ParticipantCategory::where('event_id', $event->id)->pluck('category', 'id');
        $this->select('category_id', 'Категория')->options($categories);
    }
    public function html()
    {
        return "<a class='start-protocol-participant btn btn-sm btn-info'><i class='fa fa-file-archive'></i> $this->name</a>
                    <style>
                .start-protocol-participant {margin-top:8px;}
                 @media screen and (max-width: 767px) {
                        .start-protocol-participant {margin-top:8px;}
                    }
                </style>
                ";
    }

}
