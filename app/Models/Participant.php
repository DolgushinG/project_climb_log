<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Participant extends Model
{
    use HasFactory;

    protected $table = 'participants';

    public function owner()
    {
        return $this->belongsTo(User::class);
    }

    public static function counting_final_place($event_id, $user_id = null){
        $all_participant_event = Participant::where('event_id', '=', $event_id)->orderBy('points', 'DESC')->get();
        $user_places = array();
        foreach ($all_participant_event as $index => $user){
            $user_places[$user->user_id] = $index+1;
        }
        if($user_id){
            return $user_places[$user_id];
        }
        return $user_places;

    }

    public static function participant_with_result($event_id, $gender)
    {
        $active_participant = Participant::where('event_id', '=', $event_id)->where('active', '=', 1)->pluck('user_id')->toArray();
        if ($active_participant) {
            return count(User::whereIn('id', $active_participant)->where('gender', '=', $gender)->get()->toArray());
        } else {
            return 1;
        }
    }
    public static function is_active_participant($event_id, $user_id)
    {
        $active_participant = Participant::where('event_id', '=', $event_id)->where('user_id', '=', $user_id)->where('active', '=', 1)->first();
        if ($active_participant) {
            return true;
        } else {
            return false;
        }
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
