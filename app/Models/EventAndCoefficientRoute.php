<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventAndCoefficientRoute extends Model
{
    protected $table = 'event_and_coefficient_route';

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function update_coefficitient($event_id, $route_id, $owner_id, $gender){
        $record = EventAndCoefficientRoute::where('event_id', '=', $event_id)->where('route_id', '=', $route_id)->first();
        if ($record === null) {
            $event_and_coefficient_route = new EventAndCoefficientRoute;
        } else {
            $event_and_coefficient_route = $record;
        }
        $coefficient = ResultParticipant::get_coefficient(intval($event_id), intval($route_id), $gender);
        $event_and_coefficient_route->event_id = $event_id;
        $event_and_coefficient_route->route_id = $route_id;
        $event_and_coefficient_route->owner_id = $owner_id;
        if($gender === 'male') {
            $event_and_coefficient_route->coefficient_male = $coefficient;
        } else {
            $event_and_coefficient_route->coefficient_female = $coefficient;
        }
        $event_and_coefficient_route->save();
    }
}
