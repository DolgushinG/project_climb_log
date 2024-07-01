<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Set extends Model
{
    public static function getParticipantSets($owner_id)
    {
        $sets =  Set::where('owner_id', $owner_id)->pluck('number_set', 'id')->toArray();
        $sets[0] = '-';
        return $sets;
    }
    public function participant()
    {
        return $this->belongsTo(ResultQualificationClassic::class);
    }
    public function result_france_system_qualification()
    {
        return $this->belongsTo(ResultFranceSystemQualification::class);
    }

    public static function get_number_set_id_for_user($event, $user_id)
    {
        if($event->is_france_system_qualification){
            $participant = ResultFranceSystemQualification::where('event_id','=',$event->id)->where('user_id','=',$user_id)->first();
        } else {
            $participant = ResultQualificationClassic::where('event_id','=',$event->id)->where('user_id','=',$user_id)->first();
        }
        return $participant->number_set_id ?? null;
    }
}
