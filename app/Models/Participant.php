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

    public static function participant_with_result($event_id, $gender)
    {
        $active_participant = Participant::where('event_id', '=', $event_id)->where('active', '=', 1)->pluck('user_id')->toArray();
        if ($active_participant) {
            return count(User::whereIn('id', $active_participant)->where('gender', '=', $gender)->get()->toArray());
        } else {
            return 1;
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
