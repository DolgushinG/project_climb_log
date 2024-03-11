<?php

namespace App\Http\Middleware;

use App\Models\Event;
use App\Models\Participant;
use Closure;
use Encore\Admin\Facades\Admin;
use Illuminate\Http\Request;

class OwnerEventIsValid
{
    /**
     * Обработка входящего запроса.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $event_id = $request->route('event');
        $participant = $request->route('participant');
        if ($event_id) {
            if(Admin::user()->isAdministrator()){
                return $next($request);
            }
            if (Event::where('id', '=', $event_id)->first()->owner_id != Admin::user()->id) {
                return redirect('/admin');
            }
        }
//        if ($participant) {
//            if(Admin::user()->isAdministrator()){
//                return $next($request);
//            }
//            $event = Event::where('owner_id', '=', Admin::user()->id)->where('active', 1)->first();
//            if($event->is_qualification_counting_like_final){
//                $participant = R::find($participant);
//            } else {
//                $participant = Participant::find($participant);
//            }
//
//            if (Event::where('id', '=', $participant->event_id)->first()->owner_id != Admin::user()->id) {
//                return redirect('/admin');
//            }
//        }
        return $next($request);
    }
}
