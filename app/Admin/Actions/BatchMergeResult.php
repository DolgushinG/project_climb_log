<?php

namespace App\Admin\Actions;

use App\Helpers\Helpers;
use App\Jobs\MergeResultsParticipants;
use App\Models\Event;
use App\Models\Grades;
use App\Models\ParticipantCategory;
use App\Models\ResultQualificationClassic;
use App\Models\User;
use Encore\Admin\Actions\Action;
use Encore\Admin\Facades\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use function Symfony\Component\String\s;

class BatchMergeResult extends Action
{
    public $name = 'Объединить результаты';

    protected $selector = '.merge-result';

    public function handle(Request $request)
    {
        $owner_id = Admin::user()->id;

        $active_event = Event::where('owner_id', $owner_id)->where('active', 1)->first();
        $active_event->is_open_main_rating = 1;
        $active_event->save();

        $categories = ParticipantCategory::where('event_id', $active_event->id)->get();
        foreach ($categories as $category) {
            Cache::forget('result_male_cache_' . $category->category.'_event_id_'.$active_event->id);
            Cache::forget('result_female_cache_' . $category->category.'_event_id_'.$active_event->id);
        }

        if($request && $active_event){
            $event_ids = array_filter($request->event_id);
            $event_ids[] = $active_event->id;
            if(!Helpers::is_categories_events_same($event_ids)){
                return $this->response()->error('Обьединение невозможно, так как категории разные');
            }
            $users_ids = ResultQualificationClassic::whereIn('event_id', $event_ids)->distinct()->pluck('user_id')->toArray();
            Event::merge_point($users_ids, $event_ids, $active_event);
            if($active_event->is_auto_categories){
                Event::merge_auto_categories($active_event, $users_ids, $event_ids);
                Event::counting_global_category_place($active_event);
            } else {
                Event::counting_global_points_place($active_event);
            }
//            MergeResultsParticipants::dispatch($active_event->id, $users_ids, $event_ids, 'merge_point');
//            MergeResultsParticipants::dispatch($active_event->id, $users_ids, $event_ids, 'merge_auto_categories');
//            MergeResultsParticipants::dispatch($active_event->id, $users_ids, $event_ids, 'counting_global_place');
        }

        return $this->response()->success('Результат будет чуть позже обновляйте через 2-5 мин')->refresh();
    }

    public function form()
    {
        $this->modalSmall();
        $events = Event::where('owner_id', '=', \Encore\Admin\Facades\Admin::user()->id)->where('active', 0)->get()->pluck('title', 'id')->toArray();
        $this->checkbox('event_id', 'Соревнование')->options($events);
    }

    public function html()
    {
        return "<a class='merge-result btn btn-sm btn-success'><i class='fa fa-circle'></i><i class='fa fa-circle'></i> Объединить результаты</a>
                    <style>
                .merge-result {margin-top:8px;}
                 @media screen and (max-width: 767px) {
                        .merge-result {margin-top:8px;}
                    }
                </style>
                ";
    }


}
