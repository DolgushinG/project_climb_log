<?php

namespace App\Http\Controllers;

use App\Models\Event;
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
        $now = Carbon::now();
        $now->setTimezone('Europe/Moscow');

        $cities = [];
        foreach ($active_cities as $city) {
            $count = Event::where('is_public', '=', 1)->where('city', '=', $city)->get()->count();
            $cities[] = array('name' => $city, 'count_event' => $count);
        }
        $events = Event::where('is_public', '=', 1)->get();
        foreach ($events as $event)
        {
            # если дата окончания уже прошла от текущей даты
            if($now->gte($events->end_date)){

            }
        }

        return view('main', compact(['events', 'cities']));
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
