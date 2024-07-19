<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LikeDislike extends Model
{
    public function likes(){

        return $this->belongsToMany('App\Models\Posts');
    }
    // Dislikes
    public function dislikes(){
        return $this->belongsToMany('App\Models\Posts');
    }

    public function likesGyms(){

        return $this->belongsToMany('App\Models\AllGyms');
    }
    // Dislikes
    public function dislikesGyms(){
        return $this->belongsToMany('App\Models\AllGyms');
    }

    public function likesMoves(){

        return $this->belongsToMany('App\Models\ClimbingMoves');
    }
}
