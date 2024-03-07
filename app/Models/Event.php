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
            if(isset($route['Ценность'])){
                $grades[] = array('owner_id' => $owner_id ,'event_id' => $event_id, 'grade' => $route['Категория'], 'amount' => $route['Кол-во'], 'value' => $route['Ценность']);
            } else {
                $grades[] = array('owner_id' => $owner_id ,'event_id' => $event_id, 'grade' => $route['Категория'], 'amount' => $route['Кол-во']);
            }
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

    public function number_to_month($month): string
    {
        switch ($month){
            case 1: $m='Январь'; break;
            case 2: $m='Февраль'; break;
            case 3: $m='Март'; break;
            case 4: $m='Апрель'; break;
            case 5: $m='Май'; break;
            case 6: $m='Июнь'; break;
            case 7: $m='Июль'; break;
            case 8: $m='Август'; break;
            case 9: $m='Сентябрь'; break;
            case 10: $m='Октябрь'; break;
            case 11: $m='Ноябрь'; break;
            case 12: $m='Декабрь'; break;
        }
        return $m;
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
        $participants = User::query()
            ->leftJoin('participants', 'users.id', '=', 'participants.user_id')
            ->where('participants.event_id', '=',$event_id)
            ->select(
                'users.id',
                'participants.category_id',
                'users.gender',
            )->get();
        foreach ($participants as $participant) {
            # Points для Формата все трассы считаем сразу
            $points = 0;
            $routes_only_passed = array();
            foreach ($routes as $route) {
                $user_model = ResultParticipant::where('event_id', '=', $event_id)
                    ->where('user_id', '=', $participant->id)
                    ->where('route_id', '=', $route['route_id'])
                    ->first();
                if($user_model->attempt != 0) {
                    $value_category = Grades::where('grade','=', $user_model->grade)->where('owner_id','=', $event->owner_id)->first()->value;
//                    (new \App\Models\EventAndCoefficientRoute)->update_coefficitient($event->id, $route['route_id'], $event->owner_id, $participant->gender);
                    $coefficient = EventAndCoefficientRoute::where('event_id', '=', $event_id)
                        ->where('route_id', '=', $route['route_id'])->first()->toArray();
                    $value_route = (new \App\Models\ResultParticipant)->get_value_route($user_model->attempt, $value_category, $event->mode);
                    $points += $coefficient['coefficient_'.$participant->gender] * $value_route;
                    $point_route = $coefficient['coefficient_'.$participant->gender] * $value_route;
                    $user_model->points = $point_route;
                    $routes_only_passed[] = $user_model;
                }
            }
            if($format == 1){
                $points = 0;
                usort($routes_only_passed, function($a, $b) {
                    return $a['points'] <=> $b['points'];
                });
                $amount = $event->mode_amount_routes;
                $lastElems = array_slice($routes_only_passed, -$amount, $amount);
                foreach ($lastElems as $lastElem) {
                    $points += $lastElem->points;
                }
            }
            $final_participant_result = Participant::where('user_id', '=', $participant->id)->where('event_id', '=', $event_id)->first();
            $final_participant_result->points = $points;
            $final_participant_result->event_id = $event_id;
            $final_participant_result->user_id = $participant->id;
            $final_participant_result->save();

            $place = Participant::get_places_participant_in_qualification($event_id, $participant->id, $participant->gender, $participant->category_id,true);
            $participant_result = Participant::where('user_id', '=', $participant->id)->where('event_id', '=', $event_id)->first();
            $participant_result->user_place = $place;
            $participant_result->save();
        }
        $update = UpdateParticipantResult::where('event_id', '=', $event_id)->first();
        if(!$update){
            $update = new UpdateParticipantResult;
        }
        $amount_participant = Participant::where('event_id', '=', 1)->select('user_id')->get()->count();
        $update->amount_participant = $amount_participant;
        $update->event_id = $event_id;
        $update->save();
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
        $fields = ['firstname','id','category','active','team','city', 'email','year','lastname','skill','sport_category','email_verified_at', 'created_at', 'updated_at'];
        $event = Event::find($event_id);
        if($event->is_additional_final){
            # Если выбран режим что финал для всех то отдаем лучшех 6 участников каждый категории
            $all_group_participants = array();
            foreach ($event->categories as $category){
                $category_id = ParticipantCategory::where('category', $category)->where('event_id', $event->id)->first()->id;
                $all_group_participants['male'][$category] = Participant::better_participants($event->id, 'male', 6, $category_id);
                $all_group_participants['female'][$category] = Participant::better_participants($event->id, 'female', 6, $category_id);
            }
            foreach ($all_group_participants as $group_participants){
                foreach ($group_participants as $participants){
                    ResultRouteSemiFinalStageController::getUsersSorted($participants, $fields, $event, 'final', Admin::user()->id);
                }
            }
        } else {
            if($event->is_semifinal){
                $users_male = ResultSemiFinalStage::better_of_participants_semifinal_stage($event_id, 'male', 6);
                $users_female = ResultSemiFinalStage::better_of_participants_semifinal_stage($event_id, 'female', 6);
            } else {
                $users_female = Participant::better_participants($event_id, 'female', 6);
                $users_male = Participant::better_participants($event_id, 'male', 6);
            }
            ResultRouteSemiFinalStageController::getUsersSorted($users_female, $fields, $event, 'final', $owner_id);
            ResultRouteSemiFinalStageController::getUsersSorted($users_male, $fields, $event, 'final', $owner_id);
        }
    }

    public function insert_final_participant_result($event_id, $points, $user_id, $gender){
        $final_participant_result = Participant::where('event_id', '=', $event_id)->where('user_id', '=', $user_id)->first();
        $final_participant_result->points = $final_participant_result->points + $points;
        $final_participant_result->active = 1;
        $final_participant_result->user_place = Participant::get_places_participant_in_qualification($event_id, $user_id, $gender, $final_participant_result->category_id,true);
        $final_participant_result->save();
    }

    public static function update_participant_place($event_id, $user_id, $gender){
        $final_participant_result = Participant::where('event_id', '=', $event_id)->where('user_id', '=', $user_id)->first();
        $final_participant_result->user_place = Participant::get_places_participant_in_qualification($event_id, $user_id, $gender, $final_participant_result->category_id,true);
        $final_participant_result->save();
    }

}
