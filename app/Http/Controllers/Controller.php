<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\ResultFranceSystemQualification;
use App\Models\ResultQualificationClassic;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function main()
    {
        $active_cities = Event::where('is_public', '=', 1)->distinct()->pluck('city')->toArray();
        $now = Carbon::today();
        $now->setTimezone('Europe/Moscow');

        $cities = [];
        foreach ($active_cities as $city) {
            $count = Event::where('is_public', '=', 1)->whereDate('end_date', '>=', $now)->where('city', '=', $city)->get()->count();
            $cities[] = array('name' => $city, 'count_event' => $count);
        }
        $events = Event::where('is_public', '=', 1)->whereDate('end_date', '>=', $now)->get();
        return view('main', compact(['events','cities']));
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
                $amount_participant[$event->id] = ResultQualificationClassic::where('event_id','=',$event->id)->count();
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
}
