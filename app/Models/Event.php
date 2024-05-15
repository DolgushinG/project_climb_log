<?php

namespace App\Models;

use App\Admin\Controllers\ResultRouteSemiFinalStageController;
use Encore\Admin\Facades\Admin;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Event extends Model
{
    use HasFactory;

    const DEFAULT_SEMIFINAL_PARTICIPANT = 20;
    const DEFAULT_FINAL_PARTICIPANT = 6;

    const COST_FOR_EACH_PARTICIPANT = 1;

    protected $casts = [
        'grade_and_amount' =>'json',
        'options_categories' => 'json',
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

    public function participant(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Participant::class);
    }

    public function ownerPayments(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(OwnerPayments::class);
    }


    public function grades(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Grades::class);
    }
    public function routes(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Grades::class);
    }

    public function result_semifinal_stage(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ResultRouteSemiFinalStage::class);
    }
    public function result_qualification_like_final(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ResultQualificationLikeFinal::class);
    }

    public function result_final_stage(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ResultFinalStage::class);
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public static function counting_amount_for_pay_event($event_id): float|int
    {
        $event = Event::find($event_id);
        if($event->is_qualification_counting_like_final){
            $amount_participant = ResultQualificationLikeFinal::where('event_id', $event_id)->where('is_paid', 1)->count();
        } else {
            $amount_participant = Participant::where('event_id', $event_id)->where('is_paid', 1)->count();
        }
        return ($amount_participant * $event->amount_start_price) * (self::COST_FOR_EACH_PARTICIPANT / 100);
    }

    public static function counting_amount_for_pay_participant($event_id): float|int
    {
        $event = Event::find($event_id);
        return $event->amount_start_price * (self::COST_FOR_EACH_PARTICIPANT / 100);
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

    public function translate_to_eng($text, $mode='eng'): array|string
    {
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
    public static function get_result_format_all_route($event, $participant): float|int
    {
        $custom_red_point = $event->amount_point_redpoint;
        $custom_flash = $event->amount_point_flash;
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
            $event_route = Route::where('grade','=', $route->grade)->where('event_id','=', $event->id)->first();
            if(!$event_route){
                Log::error('При создание соревнований было указано один формат все трасс или француская система, а были с
                сгенерировано трассы, потом изменен формат и пытаемся сгенерироват результат
                ');
            }
            $route->value = (new \App\Models\ResultParticipant)->get_value_route($route->attempt, $event_route, $event->mode, $event->id);
        }
        $routes_id_passed_with_red_point = $routes->sortByDesc('value')->take($event->mode_amount_routes)->where('attempt', ResultParticipant::STATUS_PASSED_REDPOINT)->pluck('route_id');
        if($routes_id_passed_with_red_point->isNotEmpty()){
            $finish_red_point_result = $routes->sortByDesc('value')->take($event->mode_amount_routes)->where('attempt', ResultParticipant::STATUS_PASSED_REDPOINT)->sum('value');
        } else {
            $finish_red_point_result = 0;
        }
        $routes_id_passed_with_flash = $routes->sortByDesc('value')->take($event->mode_amount_routes)->where('attempt', ResultParticipant::STATUS_PASSED_FLASH)->pluck('route_id');
        if($routes_id_passed_with_flash->isNotEmpty()){
            $finish_flash_result = $routes->sortByDesc('value')->take($event->mode_amount_routes)->where('attempt', ResultParticipant::STATUS_PASSED_FLASH)->sum('value');
        } else {
            $finish_flash_result = 0;
        }
        return $finish_flash_result + $finish_red_point_result;
    }

    public static function refresh_final_points_all_participant($event): void
    {
        $format = $event->mode ?? null;
        if(!$format){
            Log::info('Обновление без формата 1 или 2, пока что недоступно потому что используется формат подсчета как финал)');
            return;
        }
        $participants = User::query()
            ->leftJoin('participants', 'users.id', '=', 'participants.user_id')
            ->where('participants.event_id', '=',$event->id)
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
            $final_participant_result = Participant::where('user_id', '=', $participant->id)->where('event_id', '=', $event->id)->first();
            $category_id = $participant->category_id;
            if($event->is_auto_categories && $category_id == null){
                $the_best_route_passed = Grades::findMaxIndices(Grades::grades(), Participant::get_list_passed_route($event->id, $participant->id), 3);
                $category = Participant::get_category_from_result($event, $the_best_route_passed);
                $category_id = ParticipantCategory::where('event_id', '=', $event->id)->where('category', $category)->first()->id;
                $final_participant_result->category_id = $category_id;
            }
            $final_participant_result->points = $points;
            $final_participant_result->event_id = $event->id;
            $final_participant_result->user_id = $participant->id;
            $final_participant_result->save();
            if($event->is_additional_final){
                $place = Participant::get_places_participant_in_qualification($event->id, $participant->id, $participant->gender, $category_id,true);
            } else {
                $place = Participant::get_places_participant_in_qualification(event_id: $event->id, user_id: $participant->id, gender: $participant->gender, get_place_user: true);
            }
            $participant_result = Participant::where('user_id', '=', $participant->id)->where('event_id', '=', $event->id)->first();
            $participant_result->user_place = $place;
            $participant_result->save();
        }
    }
    public static function refresh_final_points_all_participant_in_semifinal($event_id): void
    {
        $event = Event::find($event_id);
        $amount_the_best_participant = $event->amount_the_best_participant ?? self::DEFAULT_SEMIFINAL_PARTICIPANT;
        $fields = ['firstname','id','category','active','team','city', 'email','year','lastname','skill','sport_category','email_verified_at', 'created_at', 'updated_at'];
        if($event->is_qualification_counting_like_final){
            if($event->is_additional_semifinal) {
                $all_group_participants = array();
                foreach ($event->categories as $category) {
                    $category_id = ParticipantCategory::where('category', $category)->where('event_id', $event->id)->first()->id;
                    $all_group_participants['male'][$category] = ResultQualificationLikeFinal::better_of_participants_qualification_like_final_stage($event->id, 'male', $amount_the_best_participant, $category_id);
                    $all_group_participants['female'][$category] = ResultQualificationLikeFinal::better_of_participants_qualification_like_final_stage($event->id, 'female', $amount_the_best_participant, $category_id);
                }
                foreach ($all_group_participants as $group_participants) {
                    foreach ($group_participants as $participants) {
                        ResultRouteSemiFinalStageController::getUsersSorted($participants, $fields, $event, 'semifinal', $event->owner_id);
                    }
                }
            } else {
                $users_female = ResultQualificationLikeFinal::better_of_participants_qualification_like_final_stage($event_id, 'female', $amount_the_best_participant);
                $users_male = ResultQualificationLikeFinal::better_of_participants_qualification_like_final_stage($event_id, 'male', $amount_the_best_participant);
                ResultRouteSemiFinalStageController::getUsersSorted($users_female, $fields, $event, 'semifinal', $event->owner_id);
                ResultRouteSemiFinalStageController::getUsersSorted($users_male, $fields, $event, 'semifinal', $event->owner_id);
            }
        } else {
            if($event->is_additional_semifinal) {
                # Если выбран режим что финал для всех то отдаем лучшех 6 участников каждый категории
                $all_group_participants = array();
                foreach ($event->categories as $category) {
                    $category_id = ParticipantCategory::where('category', $category)->where('event_id', $event->id)->first()->id;
                    $all_group_participants['male'][$category] = Participant::better_participants($event->id, 'male', $amount_the_best_participant, $category_id);
                    $all_group_participants['female'][$category] = Participant::better_participants($event->id, 'female', $amount_the_best_participant, $category_id);
                }
                foreach ($all_group_participants as $group_participants) {
                    foreach ($group_participants as $participants) {
                        ResultRouteSemiFinalStageController::getUsersSorted($participants, $fields, $event, 'semifinal', $event->owner_id);
                    }
                }
            } else {
                $users_male = Participant::better_participants($event_id, 'male', $amount_the_best_participant);
                $users_female = Participant::better_participants($event_id, 'female', $amount_the_best_participant);
                ResultRouteSemiFinalStageController::getUsersSorted($users_female, $fields, $event, 'semifinal', $event->owner_id);
                ResultRouteSemiFinalStageController::getUsersSorted($users_male, $fields, $event, 'semifinal', $event->owner_id);
            }
        }

    }

    public static function refresh_qualification_counting_like_final($event): void
    {
        $fields = ['firstname','id','category','active','team','city', 'email','year','lastname','skill','sport_category','email_verified_at', 'created_at', 'updated_at'];
        if($event->is_additional_final) {
            $all_group_participants = array();
            foreach ($event->categories as $category) {
                $category_id = ParticipantCategory::where('category', $category)->where('event_id', $event->id)->first()->id;
                $part_nt = ResultRouteQualificationLikeFinal::where('event_id', '=', $event->id)->where('category_id', $category_id)->distinct()->pluck('user_id');
                $all_group_participants['male'][$category] = User::whereIn('id', $part_nt)->where('gender','=', 'male')->get();
                $all_group_participants['female'][$category] = User::whereIn('id', $part_nt)->where('gender','=', 'female')->get();
            }
            foreach ($all_group_participants as $group_participants) {
                foreach ($group_participants as $participants) {
                    ResultRouteSemiFinalStageController::getUsersSorted($participants, $fields, $event, 'qualification_like_final', Admin::user()->id);
                }
            }
        } else {
            $participant_users_id = ResultQualificationLikeFinal::where('event_id', '=', $event->id)->pluck('user_id')->toArray();
            $participants_female = User::whereIn('id', $participant_users_id)->where('gender', 'female')->get();
            $participants_male = User::whereIn('id', $participant_users_id)->where('gender', 'male')->get();
            ResultRouteSemiFinalStageController::getUsersSorted($participants_female, $fields, $event, 'qualification_like_final', Admin::user()->id);
            ResultRouteSemiFinalStageController::getUsersSorted($participants_male, $fields, $event, 'qualification_like_final', Admin::user()->id);
        }
    }

    public static function refresh_final_points_all_participant_in_final($event_id): void
    {
        $event = Event::find($event_id);
        $amount_the_best_participant_to_go_final = $event->amount_the_best_participant_to_go_final ?? self::DEFAULT_FINAL_PARTICIPANT;
        $amount_the_best_participant = $event->amount_the_best_participant ?? self::DEFAULT_SEMIFINAL_PARTICIPANT;
        $fields = ['firstname','id','category','active','team','city', 'email','year','lastname','skill','sport_category','email_verified_at', 'created_at', 'updated_at'];
        if($event->is_qualification_counting_like_final){
            if($event->is_additional_final) {
                $all_group_participants = array();
                foreach ($event->categories as $category) {
                    $category_id = ParticipantCategory::where('category', $category)->where('event_id', $event->id)->first()->id;
                    $all_group_participants['male'][$category] = ResultQualificationLikeFinal::better_of_participants_qualification_like_final_stage($event->id, 'male', $amount_the_best_participant_to_go_final, $category_id);
                    $all_group_participants['female'][$category] = ResultQualificationLikeFinal::better_of_participants_qualification_like_final_stage($event->id, 'female', $amount_the_best_participant_to_go_final, $category_id);
                }
                foreach ($all_group_participants as $group_participants) {
                    foreach ($group_participants as $participants) {
                        ResultRouteSemiFinalStageController::getUsersSorted($participants, $fields, $event, 'final', $event->owner_id);
                    }
                }
            } else {
                $users_female = ResultQualificationLikeFinal::better_of_participants_qualification_like_final_stage($event_id, 'female', $amount_the_best_participant);
                $users_male = ResultQualificationLikeFinal::better_of_participants_qualification_like_final_stage($event_id, 'male', $amount_the_best_participant);
                ResultRouteSemiFinalStageController::getUsersSorted($users_female, $fields, $event, 'final', $event->owner_id);
                ResultRouteSemiFinalStageController::getUsersSorted($users_male, $fields, $event, 'final', $event->owner_id);
            }
        } else {
            if($event->is_semifinal){
                if($event->is_additional_final) {
                    # Если выбран режим что финал для всех то отдаем лучшех 6 участников каждый категории
                    $all_group_participants = array();
                    foreach ($event->categories as $category) {
                        $category_id = ParticipantCategory::where('category', $category)->where('event_id', $event->id)->first()->id;
                        $all_group_participants['male'][$category] = ResultSemiFinalStage::better_of_participants_semifinal_stage($event->id, 'male', $amount_the_best_participant_to_go_final, $category_id);
                        $all_group_participants['female'][$category] = ResultSemiFinalStage::better_of_participants_semifinal_stage($event->id, 'female', $amount_the_best_participant_to_go_final, $category_id);
                    }

                    foreach ($all_group_participants as $group_participants) {
                        foreach ($group_participants as $participants) {
                            ResultRouteSemiFinalStageController::getUsersSorted($participants, $fields, $event, 'final', $event->owner_id);
                        }
                    }
                } else {
                    $users_male = ResultSemiFinalStage::better_of_participants_semifinal_stage($event_id, 'male', $amount_the_best_participant);
                    $users_female = ResultSemiFinalStage::better_of_participants_semifinal_stage($event_id, 'female', $amount_the_best_participant);
                    ResultRouteSemiFinalStageController::getUsersSorted($users_female, $fields, $event, 'final', $event->owner_id);
                    ResultRouteSemiFinalStageController::getUsersSorted($users_male, $fields, $event, 'final', $event->owner_id);
                }
            } else {
                if($event->is_additional_final){
                    # Если выбран режим что финал для всех то отдаем лучшех 6 участников каждый категории
                    $all_group_participants = array();
                    foreach ($event->categories as $category){
                        $category_id = ParticipantCategory::where('category', $category)->where('event_id', $event->id)->first()->id;
                        $all_group_participants['male'][$category] = Participant::better_participants($event->id, 'male', $amount_the_best_participant_to_go_final, $category_id);
                        $all_group_participants['female'][$category] = Participant::better_participants($event->id, 'female', $amount_the_best_participant_to_go_final, $category_id);
                    }
                    foreach ($all_group_participants as $group_participants){
                        foreach ($group_participants as $participants){
                            ResultRouteSemiFinalStageController::getUsersSorted($participants, $fields, $event, 'final', $event->owner_id);
                        }
                    }
                } else {
                    $users_female = Participant::better_participants($event_id, 'female', $amount_the_best_participant);
                    $users_male = Participant::better_participants($event_id, 'male', $amount_the_best_participant);
                    ResultRouteSemiFinalStageController::getUsersSorted($users_female, $fields, $event, 'final', $event->owner_id);
                    ResultRouteSemiFinalStageController::getUsersSorted($users_male, $fields, $event, 'final', $event->owner_id);
                }
            }
        }
    }

    public function insert_final_participant_result($event_id, $points, $user_id, $gender): void
    {
        $final_participant_result = Participant::where('event_id', '=', $event_id)->where('user_id', '=', $user_id)->first();
        $final_participant_result->points = $final_participant_result->points + $points;
        $final_participant_result->active = 1;
        $final_participant_result->user_place = Participant::get_places_participant_in_qualification($event_id, $user_id, $gender, $final_participant_result->category_id,true);
        $final_participant_result->save();
    }

    public static function update_participant_place($event, $user_id, $gender): void
    {
        $final_participant_result = Participant::where('event_id', '=', $event->id)->where('user_id', '=', $user_id)->first();
        if(!$event->is_auto_categories && $final_participant_result->category_id == null){
            $the_best_route_passed = Grades::findMaxIndices(Grades::grades(), Participant::get_list_passed_route($event->id, $user_id), 3);
            $category = Participant::get_category_from_result($event, $the_best_route_passed);
            $category_id = ParticipantCategory::where('event_id', '=', $event->id)->where('category', $category)->first()->id;
        } else {
            $category_id = $final_participant_result->category_id;
        }
        if($final_participant_result->user_place){
            $final_participant_result->user_place = Participant::get_places_participant_in_qualification($event->id, $user_id, $gender, $category_id,true);
        }
        $final_participant_result->save();
    }

    public static function get_france_system_result($table, $event_id, $gender, $category=null): \Illuminate\Support\Collection
    {
        $users = User::query()
            ->leftJoin($table, 'users.id', '=', $table.'.user_id')
            ->where($table.'.event_id', '=', $event_id)
            ->select(
                $table.'.place',
                'users.id',
                'users.middlename',
                'users.city',
                $table.'.category_id',
                $table.'.gender',
                $table.'.amount_top',
                $table.'.amount_try_top',
                $table.'.amount_zone',
                $table.'.amount_try_zone',
            )->where($table.'.gender', '=', $gender);
        if($category){
            $users = $users->where('category_id', '=', $category->id);
        }
        $users = $users->get()->sortBy('place')->toArray();
        foreach ($users as $index => $user){
            if($table == 'result_final_stage'){
                $final_result = ResultRouteFinalStage::where('event_id', '=', $event_id)->where('user_id', '=', $user['id'])->get();
            }
            if($table === "result_qualification_like_final"){
                $final_result = ResultRouteQualificationLikeFinal::where('event_id', '=', $event_id)->where('user_id', '=', $user['id'])->get();
            }
            if($table === "result_semifinal_stage"){
                $final_result = ResultRouteSemiFinalStage::where('event_id', '=', $event_id)->where('user_id', '=', $user['id'])->get();
            }
            foreach ($final_result as $result){
                if($table == 'result_final_stage' || $table === "result_semifinal_stage"){
                    $route_id = $result->final_route_id;
                } else {
                    $route_id = $result->route_id;
                }
                $users[$index]['amount_top_'.$route_id] = $result->amount_top;
                $users[$index]['amount_try_top_'.$route_id] = $result->amount_try_top;
                $users[$index]['amount_zone_'.$route_id] = $result->amount_zone;
                $users[$index]['amount_try_zone_'.$route_id] = $result->amount_try_zone;
            }
            $users[$index] = collect($users[$index])->except('id');
        }
        return collect($users);
    }
}
