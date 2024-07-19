<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Posts extends Model
{
    public function likes(){
        return $this->hasMany('App\Models\LikeDislike')->sum('like');
    }
    public function dislikes(){
        return $this->hasMany('App\Models\LikeDislike')->sum('dislike');
    }
    public function postCategories()
    {
        return $this->belongsToMany(PostCategories::class);
    }
}
