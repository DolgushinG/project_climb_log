<?php

namespace App\Models;

use Encore\Admin\Facades\Admin;
use Illuminate\Database\Eloquent\Model;

class ParticipantCategory extends Model
{
    protected $table = 'participant_categories';
    public function participant()
    {
        return $this->belongsTo(Participant::class);
    }

    public function getUserCategory($owner_id)
    {
        $event = Event::where('owner_id', '=', $owner_id)
            ->where('active', 1)->first();
        return ParticipantCategory::whereIn('category', $event->categories)->where('event_id', $event->id)->pluck('category', 'id')->toArray();
    }

}
