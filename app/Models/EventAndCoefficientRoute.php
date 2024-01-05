<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventAndCoefficientRoute extends Model
{
    protected $table = 'event_and_coefficient_route';

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
