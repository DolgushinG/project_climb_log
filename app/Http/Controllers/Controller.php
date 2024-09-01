<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Posts;
use App\Models\ResultFranceSystemQualification;
use App\Models\ResultQualificationClassic;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function main()
    {
        $now = Carbon::today();
        $now->setTimezone('Europe/Moscow');
        $active_cities = Event::where('is_public', '=', 1)->whereDate('end_date', '>=', $now)->distinct()->pluck('city')->toArray();
        $cities = [];
        foreach ($active_cities as $city) {
            $count = Event::where('is_public', '=', 1)->whereDate('end_date', '>=', $now)->where('city', '=', $city)->get()->count();
            $cities[] = array('name' => $city, 'count_event' => $count);
        }
        $events = Event::where('is_public', '=', 1)->whereDate('end_date', '>=', $now)->get();
        $info_climbing_events = [];
        $info_climbing_events['amount_events'] = Event::where('is_public', 1)->get()->count();
        $info_climbing_events['amount_users'] = User::all()->count();
        $recentlyPost = Posts::latest('created_at')->where('status', '=', 'PUBLISHED')->paginate(3);
        $info_climbing_events['amount_climbing_gym'] = DB::table('admin_users')->get()->count() - 2;
        $info_climbing_events['amount_city'] = User::where('city', '!=', null)->select('city')->distinct()->get()->count();
        return view('main', compact(['info_climbing_events','events','cities', 'recentlyPost']));
    }

    public function list_events()
    {
        $active_cities = Event::where('is_public', '=', 1)->distinct()->pluck('city')->toArray();
        $cities = [];
        foreach ($active_cities as $city) {
            $count = Event::where('is_public', '=', 1)->where('city', '=', $city)->get()->count();
            $cities[] = array('name' => $city, 'count_event' => $count);
        }
        $amount_participant = [];
        $for_amount_events = Event::where('is_public', '=', 1)->get();
        $events = Event::where('is_public', '=', 1)
            ->orderBy('created_at', 'DESC')
            ->paginate(10);
        foreach ($for_amount_events as $event){
            if($event->is_france_system_qualification){
                $amount_participant[$event->id] = ResultFranceSystemQualification::where('event_id','=',$event->id)->count();
            } else {
                $amount_participant[$event->id] = ResultQualificationClassic::where('event_id','=',$event->id)->where('is_other_event', 0)->count();
            }
        }
        return view('event.list_events', compact(['events','amount_participant','cities']));
    }

    public function indexPrivacy()
    {
        return view('privacy.policiesconf');
    }

    public function indexPrivacyData()
    {
        return view('privacy.privatedata');
    }
    public function indexBlog()
    {
        return view('blog.index');
    }
}
