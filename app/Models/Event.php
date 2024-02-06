<?php

namespace App\Models;

use App\Admin\Controllers\ResultRouteSemiFinalStageController;
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
        'transfer_to_next_category' =>'json',
        'categories' =>'json',
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

    public static function generation_route($owner_id, $event_id, $routes){
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

    public function result_semifinal_stage()
    {
        return $this->hasOne(ResultRouteSemiFinalStage::class);
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
            $gender = User::find($user)->gender;
            $user_and_increase_category = array();
            foreach ($routes as $route) {
                $user_model = ResultParticipant::where('event_id', '=', $event_id)
                    ->where('user_id', '=', $user)
                    ->where('route_id', '=', $route['route_id'])
                    ->first();


                # Если предыдущий метод вернул массив с user_id и тем что участник пролез категорию после которой нужно переводить в другую
                # Сохраняем их в масссив и ждем вхождение больше 1 или 2 раза для перевода
//                $participant = Participant::where('event_id', '=', $event_id)->where('user_id', '=', $user_model->user_id)->first();
//                foreach ($event->transfer_to_next_category as $value){
//                    $is_increase_category = (new \App\Models\ResultParticipant)->get_increase_category($user_model, $value);
//                    if($is_increase_category){
//                        if($user_and_increase_category != []){
//                            foreach ($user_and_increase_category as $index => $user){
//                                $user_and_increase_category['amount_pass_route_for_increase'] += 1;
//                            }
//                        } else {
//                            $user_and_increase_category = array('user_id' => $is_increase_category['user_id'],
//                                'next_category' => $is_increase_category['next_category'], 'amount_pass_route_for_increase' => 1);
//                        }
//                    }
//                }
                (new \App\Models\EventAndCoefficientRoute)->update_coefficitient($event_id, $route['route_id'], $event->owner_id, $gender);
                if($user_model->attempt != 0) {
                    $value_category = Grades::where('grade','=', $user_model->grade)->where('owner_id','=', $event->owner_id)->first()->value;
                    $coefficient = ResultParticipant::get_coefficient($event_id, $route['route_id'], $gender);
                    $value_route = (new \App\Models\ResultParticipant)->get_value_route($user_model->attempt, $value_category, $event->mode);
                    $points += $coefficient + $value_route;
                    $point_route = $coefficient + $value_route;
                    $user_model->points = $point_route;
                    $routes_only_passed[] = $user_model;
                }

            }

            if(isset($user_and_increase_category['amount_pass_route_for_increase'])){
                if($user_and_increase_category['amount_pass_route_for_increase'] >= $transfer_to_next_category["Кол-во трасс для перевода"]){
                    $participant = Participant::where('event_id', '=', $event_id)->where('user_id', '=', $user_and_increase_category['user_id'])->first();
                    $participant->category_id = $user_and_increase_category['next_category'];
                    $participant->save();
                }
            }
            # Производим повырешение категории в рамках условий
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
    public static function refresh_final_points_all_participant_in_semifinal($event_id, $owner_id) {
        $event = Event::find($event_id);
        $result_female = Participant::better_participants($event_id, 'female', 10);
        $result_male = Participant::better_participants($event_id, 'male', 10);
        $fields = ['firstname','id','category','active','team','city', 'email','year','lastname','skill','sport_category','email_verified_at', 'created_at', 'updated_at'];
        ResultRouteSemiFinalStageController::getUsersSorted($result_female, $fields, $event, 'semifinal', $owner_id);
        ResultRouteSemiFinalStageController::getUsersSorted($result_male, $fields, $event, 'semifinal', $owner_id);
    }
    public static function refresh_final_points_all_participant_in_final($event_id, $owner_id){
        $event = Event::find($event_id);
        $users_male = ResultSemiFinalStage::better_of_participants_semifinal_stage($event_id, 'male', 6);
        $users_female = ResultSemiFinalStage::better_of_participants_semifinal_stage($event_id, 'female', 6);
        $fields = ['firstname','id','category','active','team','city', 'email','year','lastname','skill','sport_category','email_verified_at', 'created_at', 'updated_at'];
        ResultRouteSemiFinalStageController::getUsersSorted($users_female, $fields, $event, 'final', $owner_id);
        ResultRouteSemiFinalStageController::getUsersSorted($users_male, $fields, $event, 'final', $owner_id);
    }


}
