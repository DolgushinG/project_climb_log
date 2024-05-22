<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\ParticipantCategory;
use App\Models\User;
use Encore\Admin\Facades\Admin;
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
        $cities = [];
        foreach ($active_cities as $city) {
            $count = Event::where('is_public', '=', 1)->where('city', '=', $city)->get()->count();
            $cities[] = array('name' => $city, 'count_event' => $count);
        }
        $events = Event::where('is_public', '=', 1)->get();
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
