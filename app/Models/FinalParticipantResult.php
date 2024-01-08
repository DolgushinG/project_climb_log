<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use stdClass;

class FinalParticipantResult extends Model
{
    protected $table = 'final_participant_result';

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function counting_final_place($event_id){
        $all_participant_event = FinalParticipantResult::where('event_id', '=', $event_id)->orderBy('final_points', 'DESC')->get();
        $user_places = array();
        foreach ($all_participant_event as $index => $user){
           $user_places[$user->user_id] = $index+1;
        }
        return $user_places;
    }

}
