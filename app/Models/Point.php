<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Point extends Model
{
    protected $table = 'points';
    protected $fillable = ['event_id','owner_id','author', 'grade','route_id', 'color', 'x', 'y'];
}
