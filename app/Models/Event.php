<?php

namespace App\Models;

use Encore\Admin\Facades\Admin;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Event extends Model
{
    use HasFactory;

    protected $casts = [
        'grade_and_amount' =>'json',
    ];
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'location',
        'document',
        'image',
        'city',
        'title',
        'subtitle',
        'climbing_gym_name',
        'description',
        'link',
        'count_routes',
        'active'
    ];

    public static function generation_route($owner_id,$event_id, $routes){
        $grades = array();
        foreach ($routes as $route){
            $grades[] = array('owner_id' => $owner_id ,'event_id' => $event_id, 'grade' => $route['Категория'], 'amount' => $route['Кол-во'], 'value' => $route['Ценность']);
        }
        DB::table('grades')->insert($grades);
    }

    public static function exist_events($owner_id){
        return boolval(Event::where('owner_id', '=', $owner_id)->where('active', '=', 1)->first());
    }
    public function participant()
    {
        return $this->hasOne(Participant::class);
    }

    public function result_final_stage()
    {
        return $this->hasOne(ResultRouteFinalStage::class);
    }

    public function user()
    {
        return $this->belongsToMany(User::class);
    }

    public function translate_to_eng($text, $mode='eng'){
        $cyr = ['а','б','в','г','д','е','ё','ж','з','и','й','к','л','м','н','о','п', 'р','с','т','у','ф','х','ц','ч','ш','щ','ъ','ы','ь','э','ю','я', 'А','Б','В','Г','Д','Е','Ё','Ж','З','И','Й','К','Л','М','Н','О','П', 'Р','С','Т','У','Ф','Х','Ц','Ч','Ш','Щ','Ъ','Ы','Ь','Э','Ю','Я'
        ];
        $lat = ['a','b','v','g','d','e','io','zh','z','i','y','k','l','m','n','o','p', 'r','s','t','u','f','h','ts','ch','sh','sht','a','i','y','e','yu','ya', 'A','B','V','G','D','E','Io','Zh','Z','I','Y','K','L','M','N','O','P', 'R','S','T','U','F','H','Ts','Ch','Sh','Sht','A','I','Y','e','Yu','Ya'
        ];
        if($mode == 'eng'){
            return str_replace($cyr, $lat, $text);
        } else {
            return str_replace($lat, $cyr, $text);
        }
    }

    public static function refresh_final_points_all_participant($event_id) {
        $routes = ResultParticipant::where('event_id', '=', $event_id)->select('route_id')->distinct()->get()->toArray();
        $event = Event::find($event_id);
        $format = $event->mode;
        $final_participant = Participant::where('event_id', '=', $event_id)->pluck('user_id')->toArray();
        foreach ($final_participant as $user) {
            $points = 0;
            $routes_only_passed = array();
            foreach ($routes as $route) {

                $user_model = ResultParticipant::where('event_id', '=', $event_id)
                    ->where('user_id', '=', $user)
                    ->where('route_id', '=', $route['route_id'])
                    ->first();
                if($user_model->attempt != 0) {
                    $gender = User::gender($user);
                    $value_category = Grades::where('grade','=', $user_model->grade)->where('owner_id','=', $event->owner_id)->first()->value;
                    $coefficient = ResultParticipant::get_coefficient($event_id, $route['route_id'], $gender);
                    $value_route = (new \App\Models\ResultParticipant)->get_value_route($user_model->attempt, $value_category, $event->mode);
                    $points += $coefficient + $value_route;
                    $point_route = $coefficient + $value_route;
                    $user_model->points = $point_route;
                    $routes_only_passed[] = $user_model;
                }
            }
            if($format == 1){
                $points = 0;
                usort($routes_only_passed, function($a, $b) {
                    return $a['points'] <=> $b['points'];
                });
                $lastElems = array_slice($routes_only_passed, -10, 10);
                foreach ($lastElems as $lastElem) {
                    $points += $lastElem->points;
                }
            }
            $final_participant_result = Participant::where('user_id', '=', $user)->where('event_id', '=', $event_id)->first();
            $final_participant_result->points = $points;
            $final_participant_result->event_id = $event_id;
            $final_participant_result->user_id = $user;
            $final_participant_result->user_place = Participant::get_places_participant_in_qualification($event_id, $user, true);
            $final_participant_result->save();


        }

    }

}
