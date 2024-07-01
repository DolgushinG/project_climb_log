<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Route extends Model
{

    protected $table = 'routes';

    public function event()
    {
        return $this->belongsTo(Event::class)->where('active', 1);
    }

    public static function generation_route($owner_id, $event_id, $amount_routes, $routes){
        $grades = array();
        foreach ($routes as $route){
            if(isset($route['Ценность'])){
                $grades[] = array('owner_id' => $owner_id ,'event_id' => $event_id, 'amount' => $route['Кол-во'], 'grade' => $route['Категория'], 'value' => $route['Ценность']);
            } else {
                $grades[] = array('owner_id' => $owner_id ,'event_id' => $event_id, 'grade' => $route['Категория'], 'amount' => $route['Кол-во']);
            }
        }
        Route::where('event_id', $event_id)->delete();

        Grades::settings_routes($amount_routes, $grades);
    }
}
