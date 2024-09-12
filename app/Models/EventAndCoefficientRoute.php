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
        $event = Event::find($event_id);
        if($event->type_event){
            $column = 'route_name';
        } else {
            $column = 'route_id';
        }
        $record = EventAndCoefficientRoute::where('event_id', '=', $event_id)->where($column, '=', $route_id)->first();
        $active_participant = ResultQualificationClassic::participant_with_result($event_id, $gender);
        if ($record === null) {
            $event_and_coefficient_route = new EventAndCoefficientRoute;
        } else {
            $event_and_coefficient_route = $record;
        }
        $count_route_passed = ResultRouteQualificationClassic::counting_result($event_id, $route_id, $gender);
        $coefficient = ResultRouteQualificationClassic::get_coefficient($active_participant, $count_route_passed);
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
