<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Participant;
use App\Models\ResultParticipant;
use App\Models\Set;
use App\Models\User;
use Encore\Admin\Controllers\Dashboard;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;

class HomeController extends Controller
{
    public function index(Content $content)
    {
        return $content
//            ->row($this->title())
//            ->body(view('admin.charts.bar'));

            ->row(function (Row $row) {
                if(Admin::user()->isAdministrator()){
                    $row->column(4, function (Column $column) {
                        $column->append(Dashboard::dependencies());
                    });
                    $row->column(4, function (Column $column) {
                        $column->append(Dashboard::extensions());
                    });
                    $row->column(4, function (Column $column) {
                        $column->append(Dashboard::environment());
                    });
                } else {
                    if(Event::exist_events(Admin::user()->id)){
                        $row->column(4, function (Column $column) {
                            $column->append($this->events_participants());
                        });
                        $row->column(4, function (Column $column) {
                            $column->append($this->sets());
                        });

                        $row->column(4, function (Column $column) {
                            $column->append($this->gender());
                        });
                    }
                }



            });
    }
    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public static function events_participants()
    {
        $events_active = Event::where('owner_id', '=', Admin::user()->id)->where('active', '=', 1)->latest()->get();
        $events = array();
        foreach ($events_active as $event_active){
            $event = new \stdClass();
            $event->title = $event_active->title;
            $event->count_participant = Participant::where('event_id', '=', $event_active->id)->count();
            $events[] = $event;
        }
        return Admin::component('admin::dashboard.events_participants', compact('events'));
    }
    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public static function sets()
    {
        $event = Event::where('owner_id', '=', Admin::user()->id)->where('active', '=', 1)->latest()->first();
        $sets = Set::where('owner_id', '=', $event->owner_id)->orderBy('day_of_week')->orderBy('number_set')->get();
        foreach ($sets as $set){
            $participants_event = Participant::where('event_id','=',$event->id)->where('owner_id','=',$event->owner_id)->where('set', '=', $set->number_set)->count();
            $set->free = $set->max_participants - $participants_event;
            $a = $set->max_participants;
            $b = $set->free;

            if ($a === $b) {
                $percent = 0;
            } elseif ($a < $b) {
                $diff = $b - $a;
                $percent = $diff / $b * 100;
            } else {
                $diff = $a - $b;
                $percent = $diff / $a * 100;
            }
            $set->procent = intval($percent);

        }
        return Admin::component('admin::dashboard.sets', compact('sets'));
    }
    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public static function gender()
    {
        $event_id = Event::where('owner_id', '=', Admin::user()->id)->get()->pluck('id');
        $users_id = Participant::whereIn('event_id',$event_id)->get()->pluck('user_id');
        $users_female = User::whereIn('id', $users_id)->where('gender', '=', 'female')->get()->count();
        $users_male = User::whereIn('id', $users_id)->where('gender', '=', 'male')->get()->count();
        $gender = array('female' => $users_female, 'male' => $users_male);
        return view('admin.charts.gender', compact('gender'));
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public static function title()
    {
        $admin_info = \Encore\Admin\Facades\Admin::user();
        $event = Event::where('owner_id', '=', \Encore\Admin\Facades\Admin::user()->id)->where('active', '=', 1)->latest()->get();
        $admin_info['current_event'] = '/event/'.$event->climbing_gym_name_eng.'/'.$event->title_eng;
        return view('admin::dashboard.title', compact('admin_info'));
    }

}
