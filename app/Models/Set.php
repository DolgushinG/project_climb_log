<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Set extends Model
{
    public static function getParticipantSets($owner_id)
    {
        return Set::where('owner_id', $owner_id)->pluck('number_set', 'id')->toArray();
    }
    public function participant()
    {
        return $this->belongsTo(Participant::class);
    }
    public function result_qualification_like_final()
    {
        return $this->belongsTo(ResultQualificationLikeFinal::class);
    }
}
