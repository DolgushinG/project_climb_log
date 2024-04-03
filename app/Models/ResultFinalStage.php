<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResultFinalStage extends Model
{
    protected $table = 'result_final_stage';

    public function event()
    {
        return $this->belongsTo(Event::class)->where('active', 1);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
