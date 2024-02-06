<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParticipantCategory extends Model
{
    protected $table = 'participant_categories';

    public function participant()
    {
        return $this->belongsTo(Participant::class);
    }
}
