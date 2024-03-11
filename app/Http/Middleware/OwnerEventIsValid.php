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
        if ($event_id) {
            if(Admin::user()->isAdministrator()){
                return $next($request);
            }
            $event = Event::where('id', '=', $event_id)->first();
            if($event){
                if ($event->owner_id != Admin::user()->id) {
                    return redirect('/admin');
                }
            } else {
                return redirect('/admin');
            }
        }
        return $next($request);
    }
}
