<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResultRouteFinalStage extends Model
{
    protected $table = 'result_route_final_stage';
    public $timestamps = true;

    public function event()
    {
        return $this->belongsTo(Event::class)->where('active', '=', 1);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category(){
        return $this->belongsTo(ParticipantCategory::class);
    }

    public static function count_route_in_final_stage($event_id, $toArrayString = false){
        $count_routes = ResultRouteFinalStage::where('event_id', '=', $event_id)->distinct()->get('final_route_id')->count();
        if($toArrayString){
            $routes = [];
            for($i = 1; $i <= $count_routes; $i++){
                $routes[] =  $i.' трасса финала';
            }
            return $routes;
        }
        return $count_routes;

    }
}
