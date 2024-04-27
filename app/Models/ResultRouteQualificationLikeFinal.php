<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResultRouteQualificationLikeFinal extends Model
{
    protected $table = 'result_route_qualification_like_final';
    public $timestamps = true;

    public static function count_route_in_qualification_final($event_id, $toArrayString=null){
        $count_routes = ResultRouteQualificationLikeFinal::where('event_id', '=', $event_id)->distinct()->get('route_id')->count();
        if($toArrayString){
            $routes = [];
            for($i = 1; $i <= $count_routes; $i++){
                $routes[] =  $i.' трасса финала';
            }
            return $routes;
        }
        return $count_routes;
    }


    public function event()
    {
        return $this->belongsTo(Event::class)->where('active', '=', 1);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
