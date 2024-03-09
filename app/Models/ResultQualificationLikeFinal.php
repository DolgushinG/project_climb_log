<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResultQualificationLikeFinal extends Model
{
    protected $table = 'result_qualification_like_final';

    public function event()
    {
        return $this->belongsTo(Event::class)->where('active', '=', 1);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
