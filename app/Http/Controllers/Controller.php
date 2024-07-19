<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Posts;
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
        $recentlyPost = Posts::latest('created_at')->where('status', '=', 'PUBLISHED')->paginate(3);
        return view('main', compact(['events','recentlyPost', 'cities']));
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
