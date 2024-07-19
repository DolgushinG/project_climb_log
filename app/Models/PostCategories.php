<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostCategories extends Model
{
    protected $table = 'post_categories';

    public function posts()
    {
        return $this->belongsToMany(Posts::class);
    }
}

