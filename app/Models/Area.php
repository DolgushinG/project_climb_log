<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    use HasFactory;

    public static function get_image($area_name)
    {
        $area = Area::where('name', $area_name)->first();
        if($area){
            return $area->image;
        } else {
            return '';
        }

    }
}
