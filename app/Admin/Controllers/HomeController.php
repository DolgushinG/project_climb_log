<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Participant;
use App\Models\ParticipantCategory;
use App\Models\ResultParticipant;
use App\Models\ResultQualificationLikeFinal;
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
            ->row($this->title())
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
                        $row->column(4, function (Column $column) {
                            $column->append($this->events_participants());
                        });
                        $row->column(4, function (Column $column) {
                            $column->append($this->sets());
                        });
                        $row->column(4, function (Column $column) {
                            $column->row($this->male());
                            $column->row($this->female());
                        });
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
            if($event_active->is_qualification_counting_like_final){
                $event->count_participant = ResultQualificationLikeFinal::where('event_id', '=', $event_active->id)->count();
            } else {
                $event->count_participant = Participant::where('event_id', '=', $event_active->id)->count();
            }
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
        if($event){
            $sets = Set::where('owner_id', '=', $event->owner_id)->orderBy('day_of_week')->orderBy('number_set')->get();
            foreach ($sets as $set){
                if($event->is_qualification_counting_like_final){
                    $participants_event = ResultQualificationLikeFinal::where('event_id','=',$event->id)->where('number_set_id', '=', $set->id)->count();
                } else {
                    $participants_event = Participant::where('event_id','=',$event->id)->where('number_set_id', '=', $set->id)->count();
                }

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

        } else {
            $sets = null;
        }

        return Admin::component('admin::dashboard.sets', compact('sets'));
    }
    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public static function male()
    {
        $event = Event::where('owner_id', '=', Admin::user()->id)->where('active', '=', 1)->latest()->first();

        $categories = ParticipantCategory::where('event_id', $event->id)->get();
        $all_group = array();
        foreach ($categories as $category) {
            if($event->is_qualification_counting_like_final){
                $all_group['male'][] = ResultQualificationLikeFinal::where('event_id', '=', $event->id)->where('gender', '=', 'male')->where('category_id', '=', $category->id)->get()->count();
            } else {
                $all_group['male'][] = Participant::where('event_id', '=', $event->id)->where('gender', '=', 'male')->where('category_id', '=', $category->id)->get()->count();
            }
        }
        $categories_array = $categories->pluck('category')->toArray();
        return view('admin.charts.male', compact('all_group', 'categories_array'));
    }
    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public static function female()
    {
        $event = Event::where('owner_id', '=', Admin::user()->id)->where('active', '=', 1)->latest()->first();

        $categories = ParticipantCategory::where('event_id', $event->id)->get();
        $all_group = array();
        foreach ($categories as $category) {
            if($event->is_qualification_counting_like_final){
                $all_group['female'][] = ResultQualificationLikeFinal::where('event_id', '=', $event->id)->where('gender', '=', 'female')->where('category_id', '=', $category->id)->get()->count();
            } else {
                $all_group['female'][] = Participant::where('event_id', '=', $event->id)->where('gender', '=', 'female')->where('category_id', '=', $category->id)->get()->count();
            }
        }
        $categories_array = $categories->pluck('category')->toArray();
        return view('admin.charts.female', compact('all_group', 'categories_array'));
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public static function title()
    {
        $admin_info = \Encore\Admin\Facades\Admin::user();
        $event = Event::where('owner_id', '=', \Encore\Admin\Facades\Admin::user()->id)->where('active', '=', 1)->first();
        if($event){
            $admin_info['current_event'] = '/event/'.$event->climbing_gym_name_eng.'/'.$event->title_eng;
        }
        return view('admin::dashboard.title', compact('admin_info'));
    }

}
