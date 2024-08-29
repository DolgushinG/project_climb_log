<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Map extends Model
{
    protected $table = 'maps';
    protected $fillable = ['event_id','owner_id','author', 'grade','route_id', 'color', 'x', 'y'];
}
