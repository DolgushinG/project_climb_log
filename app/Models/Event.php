<?php

namespace App\Models;

use App\Admin\Controllers\ResultRouteSemiFinalStageController;
use Encore\Admin\Facades\Admin;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Event extends Model
{
    use HasFactory;

    const DEFAULT_SEMIFINAL_PARTICIPANT = 10;
    const DEFAULT_FINAL_PARTICIPANT = 6;

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
    public function result_qualification_like_final()
    {
        return $this->hasOne(ResultRouteQualificationLikeFinal::class);
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
    public static function get_result_format_all_route($event, $participant){
        $custom_red_point = $event->amount_point_redpoint ?? ResultParticipant::REDPOINT;
        $custom_flash = $event->amount_point_flash ?? ResultParticipant::FLASH;
        $routes_id_passed_with_red_point = ResultParticipant::where('event_id', $event->id)->where('user_id', $participant->id)->where('attempt', ResultParticipant::STATUS_PASSED_REDPOINT)->pluck('route_id');
        $counting_routes_with_red_point_passed = count($routes_id_passed_with_red_point);
        $routes_id_passed_with_flash = ResultParticipant::where('event_id', $event->id)->where('user_id', $participant->id)->where('attempt', ResultParticipant::STATUS_PASSED_FLASH)->pluck('route_id');
        $counting_routes_with_flash_passed = count($routes_id_passed_with_flash);
        if($routes_id_passed_with_red_point->isNotEmpty()){
            $sum_all_coefficients_rp = EventAndCoefficientRoute::where('event_id', '=', $event->id)->whereIn('route_id', $routes_id_passed_with_red_point)->get()->sum('coefficient_'.$participant->gender);
            $result_red_point = $counting_routes_with_red_point_passed * $custom_red_point;
            $finish_red_point_result = ($sum_all_coefficients_rp * $result_red_point)/$counting_routes_with_red_point_passed;
        } else {
            $finish_red_point_result = 0;
        }
        if($routes_id_passed_with_flash->isNotEmpty()){
            $sum_all_coefficients_flash = EventAndCoefficientRoute::where('event_id', '=', $event->id)->whereIn('route_id', $routes_id_passed_with_flash)->get()->sum('coefficient_'.$participant->gender);
            $result_flash = $counting_routes_with_flash_passed * $custom_flash;
            $finish_flash_result = ($sum_all_coefficients_flash * $result_flash)/$counting_routes_with_flash_passed;
        } else {
            $finish_flash_result = 0;
        }

        return $finish_flash_result + $finish_red_point_result;
    }
    public static function get_result_format_n_route($event, $participant){
        $routes = ResultParticipant::where('event_id', $event->id)
            ->where('user_id', $participant->id)
            ->whereNotIn('attempt', [0])
            ->get();

        foreach ($routes as $route){
            $value = Grades::where('grade','=', $route->grade)->where('event_id','=', $event->id)->first()->value;
            $route->value = (new \App\Models\ResultParticipant)->get_value_route($route->attempt, $value, $event->mode);
        }
        $routes_id_passed_with_red_point = $routes->sortByDesc('value')->take($event->mode_amount_routes)->where('attempt', ResultParticipant::STATUS_PASSED_REDPOINT)->pluck('route_id');
        if($routes_id_passed_with_red_point->isNotEmpty()){
            $routes_id_passed_with_red_point_sum_value = $routes->sortByDesc('value')->take($event->mode_amount_routes)->where('attempt', ResultParticipant::STATUS_PASSED_REDPOINT)->sum('value');
            $sum_all_coefficients_rp = EventAndCoefficientRoute::where('event_id', '=', $event->id)->whereIn('route_id', $routes_id_passed_with_red_point)->get()->sum('coefficient_'.$participant->gender);
            $finish_red_point_result = ($routes_id_passed_with_red_point_sum_value * $sum_all_coefficients_rp )/ $routes_id_passed_with_red_point->count();
        } else {
            $finish_red_point_result = 0;
        }
        $routes_id_passed_with_flash = $routes->sortByDesc('value')->take($event->mode_amount_routes)->where('attempt', ResultParticipant::STATUS_PASSED_FLASH)->pluck('route_id');
        if($routes_id_passed_with_flash->isNotEmpty()){
            $routes_id_passed_with_flash_sum_value = $routes->sortByDesc('value')->take($event->mode_amount_routes)->where('attempt', ResultParticipant::STATUS_PASSED_FLASH)->sum('value');
            $sum_all_coefficients_flash = EventAndCoefficientRoute::where('event_id', '=', $event->id)->whereIn('route_id', $routes_id_passed_with_flash)->get()->sum('coefficient_'.$participant->gender);
            $finish_flash_result = ($routes_id_passed_with_flash_sum_value * $sum_all_coefficients_flash) / $routes_id_passed_with_flash->count();
        } else {
            $finish_flash_result = 0;
        }
        return $finish_flash_result + $finish_red_point_result;
    }

    public static function refresh_final_points_all_participant($event_id) {
        $event = Event::find($event_id);
        $format = $event->mode ?? null;
        if(!$format){
            Log::info('Обновление без формата 1 или 2, пока что недоступно потому что используется формат подсчета как финал)');
            return;
        }
        $participants = User::query()
            ->leftJoin('participants', 'users.id', '=', 'participants.user_id')
            ->where('participants.event_id', '=',$event_id)
            ->select(
                'users.id',
                'participants.category_id',
                'users.gender',
            )->get();
        foreach ($participants as $participant) {
            if($format == 1){
                $points = self::get_result_format_n_route($event, $participant);
            }
            if($format == 2){
                $points = self::get_result_format_all_route($event, $participant);
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
    }
    public static function refresh_final_points_all_participant_in_semifinal($event_id, $owner_id) {
        $event = Event::find($event_id);
        $amount_routes_in_semifinal = $event->amount_routes_in_semifinal ?? self::DEFAULT_SEMIFINAL_PARTICIPANT;
        $result_female = Participant::better_participants($event_id, 'female', $amount_routes_in_semifinal);
        $result_male = Participant::better_participants($event_id, 'male', $amount_routes_in_semifinal);
        $fields = ['firstname','id','category','active','team','city', 'email','year','lastname','skill','sport_category','email_verified_at', 'created_at', 'updated_at'];
        ResultRouteSemiFinalStageController::getUsersSorted($result_female, $fields, $event, 'semifinal', $owner_id);
        ResultRouteSemiFinalStageController::getUsersSorted($result_male, $fields, $event, 'semifinal', $owner_id);
    }
    public static function refresh_final_points_all_participant_in_final($event_id, $owner_id){
        $event = Event::find($event_id);
        $amount_routes_in_final = $event->amount_routes_in_final ?? self::DEFAULT_FINAL_PARTICIPANT;
        $amount_routes_in_semifinal = $event->amount_routes_in_semifinal ?? self::DEFAULT_SEMIFINAL_PARTICIPANT;
        $fields = ['firstname','id','category','active','team','city', 'email','year','lastname','skill','sport_category','email_verified_at', 'created_at', 'updated_at'];
        if($event->is_additional_final){
            # Если выбран режим что финал для всех то отдаем лучшех 6 участников каждый категории
            $all_group_participants = array();
            foreach ($event->categories as $category){
                $category_id = ParticipantCategory::where('category', $category)->where('event_id', $event->id)->first()->id;
                $all_group_participants['male'][$category] = Participant::better_participants($event->id, 'male', $amount_routes_in_final, $category_id);
                $all_group_participants['female'][$category] = Participant::better_participants($event->id, 'female', $amount_routes_in_final, $category_id);
            }
            foreach ($all_group_participants as $group_participants){
                foreach ($group_participants as $participants){
                    ResultRouteSemiFinalStageController::getUsersSorted($participants, $fields, $event, 'final', $owner_id);
                }
            }
        } else {
            if($event->is_semifinal){
                $users_male = ResultSemiFinalStage::better_of_participants_semifinal_stage($event_id, 'male', $amount_routes_in_semifinal);
                $users_female = ResultSemiFinalStage::better_of_participants_semifinal_stage($event_id, 'female', $amount_routes_in_semifinal);
            } else {
                $users_female = Participant::better_participants($event_id, 'female', $amount_routes_in_semifinal);
                $users_male = Participant::better_participants($event_id, 'male', $amount_routes_in_semifinal);
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
