<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoutesOutdoor extends Model
{
    use HasFactory;
    public function event()
    {
        return $this->belongsTo(Event::class)->where('active', 1);
    }
    public function place()
    {
        return $this->belongsTo(Place::class);
    }
    public function place_route()
    {
        return $this->belongsTo(PlaceRoute::class);
    }
    public function area()
    {
        return $this->belongsTo(Area::class);
    }

}
