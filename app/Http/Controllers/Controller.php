<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\User;
use Encore\Admin\Facades\Admin;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function main() {
        $events = Event::where('active', '=', 1)->get();
        return view('main', compact('events'));
    }
}
