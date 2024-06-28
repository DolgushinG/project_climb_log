<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResultRouteFranceSystemQualification extends Model
{
    protected $table = 'result_route_france_system_qualification';
    public $timestamps = true;
    protected $casts = [
        'result_for_edit_france_system_qualification' =>'json',
    ];
    public static function count_route_in_qualification_final($event_id, $toArrayString=null){
        $count_routes = ResultRouteFranceSystemQualification::where('event_id', '=', $event_id)->distinct()->get('route_id')->count();
        if($toArrayString){
            $routes = [];
            for($i = 1; $i <= $count_routes; $i++){
                $routes[] =  $i.' трасса финала';
            }
            return $routes;
        }
        return $count_routes;
    }

    public function category(){
        return $this->belongsTo(ParticipantCategory::class);
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
