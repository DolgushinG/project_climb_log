<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResultRouteAdditionalFinalStage extends Model
{
    protected $table = 'result_route_additional_final_stage';

    public static function count_route_in_additional_final_stage($event_id, $toArrayString = false){
        $count_routes = ResultRouteAdditionalFinalStage::where('event_id', '=', $event_id)->distinct()->get('final_route_id')->count();
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
