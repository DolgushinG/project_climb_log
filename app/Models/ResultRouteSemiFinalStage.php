<?php

namespace App\Models;

use Encore\Admin\Facades\Admin;
use Illuminate\Database\Eloquent\Model;
use stdClass;

class ResultRouteSemiFinalStage extends Model
{
    protected $table = 'result_route_semifinal_stage';
    public $timestamps = true;

    public function event()
    {
        return $this->belongsTo(Event::class)->where('active', '=', 1);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function merge_result_user_in_stage($result){
        $final_result = array('user_id' => null, 'event_id' => null, 'amount_top' => null,'amount_try_top' => null, 'amount_zone' => null, 'amount_try_zone' => null);
        foreach ($result as $res)
        {
            $final_result['user_id'] = $res->user_id;
            $final_result['event_id'] = $res->event_id;
            $final_result['category_id'] = $res->category_id;
            $final_result['amount_try_top'] += $res->amount_try_top;
            $final_result['amount_top'] += $res->amount_top;
            $final_result['amount_try_zone'] += $res->amount_try_zone;
            $final_result['amount_zone'] += $res->amount_zone;
        }
        return $final_result;
    }

    public static function count_route_in_semifinal_stage($event_id, $toArrayString = false){

        $count_routes = ResultRouteSemiFinalStage::where('event_id', '=', $event_id)->distinct()->get('final_route_id')->count();
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
