<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Set extends Model
{
    public function free_slots($set_id, $event_id){
        return Participant::where('number_set', '=', $set_id)->where('event_id', '=', $event_id)->count();
    }
}
