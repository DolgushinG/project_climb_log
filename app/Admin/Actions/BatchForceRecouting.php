<?php

namespace App\Admin\Actions;

use App\Models\Event;
use App\Models\ParticipantCategory;
use Encore\Admin\Actions\Action;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class BatchForceRecouting extends Action
{
    public $name = 'Пересчитать результаты';

    protected $selector = '.recouting';

    public function handle(Request $request)
    {
        $event = Event::where('owner_id', '=', \Encore\Admin\Facades\Admin::user()->id)->where('active', 1)->first();
        $categories = ParticipantCategory::where('event_id', $event->id)->get();
        foreach ($categories as $category) {
            Cache::forget('result_male_cache_' . $category->category.'_event_id_'.$event->id);
            Cache::forget('result_female_cache_' . $category->category.'_event_id_'.$event->id);
        }
        Event::refresh_final_points_all_participant($event);
        return $this->response()->success('Пересчитано')->refresh();
    }

    public function dialog()
    {
        $this->confirm('Подтвердить перерасчет');
    }

    public function html()
    {
        return "<a class='recouting btn btn-sm btn-success'><i class='fa fa-refresh'></i> Результаты</a>
                    <style>
                .recouting {margin-top:8px;}
                 @media screen and (max-width: 767px) {
                        .recouting {margin-top:8px;}
                    }
                </style>
                ";
    }

}
