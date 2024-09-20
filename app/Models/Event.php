<?php

namespace App\Models;

use App\Admin\Controllers\ResultRouteSemiFinalStageController;
use Carbon\Carbon;
use Encore\Admin\Facades\Admin;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use stdClass;

class Event extends Model
{
    use HasFactory;

    const DEFAULT_SEMIFINAL_PARTICIPANT = 20;
    const DEFAULT_FINAL_PARTICIPANT = 6;
    const INTER = 0;
    const LOCAL = 1;
    const COST_FOR_EACH_PARTICIPANT = 1;

    protected $casts = [
        'grade_and_amount' => 'json',
        'options_categories' => 'json',
        'categories' => 'json',
        'options_amount_price' => 'json',
        'up_price' => 'json',
        'discounts' => 'json',
        'products' => 'json',
        'helper_amount' => 'json',
        'list_merged_events' => 'json',
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

    public static function exist_events($owner_id)
    {
        return boolval(Event::where('owner_id', '=', $owner_id)->where('active', '=', 1)->first());
    }

    public function participant_final_stage()
    {
        return $this->belongsTo(ResultFinalStage::class);
    }

    public function participant_semifinal_stage()
    {
        return $this->belongsTo(ResultSemiFinalStage::class);
    }
    public function participant()
    {
        return $this->hasOne(ResultQualificationClassic::class);
    }
    public function participant_route()
    {
        return $this->hasOne(ResultRouteQualificationClassic::class);
    }
    public function sets()
    {
        return $this->hasOne(Set::class);
    }


    public function ownerPayments()
    {
        return $this->hasOne(OwnerPayments::class);
    }


    public function grades()
    {
        return $this->hasOne(Grades::class);
    }

    public function routes()
    {
        return $this->hasOne(Grades::class);
    }
    public function colors()
    {
        return $this->hasOne(Color::class);
    }

    public function result_semifinal_stage()
    {
        return $this->hasOne(ResultRouteSemiFinalStage::class);
    }

    public function result_france_system_qualification()
    {
        return $this->hasOne(ResultFranceSystemQualification::class);
    }

    public function result_final_stage()
    {
        return $this->hasOne(ResultFinalStage::class);
    }

    public function user()
    {
        return $this->belongsToMany(User::class);
    }

    public static function counting_amount_for_pay_participant($amount_start_price)
    {
        return $amount_start_price * (self::COST_FOR_EACH_PARTICIPANT / 100);
    }

    public function number_to_month($month): string
    {
        switch ($month) {
            case 1:
                $m = 'Январь';
                break;
            case 2:
                $m = 'Февраль';
                break;
            case 3:
                $m = 'Март';
                break;
            case 4:
                $m = 'Апрель';
                break;
            case 5:
                $m = 'Май';
                break;
            case 6:
                $m = 'Июнь';
                break;
            case 7:
                $m = 'Июль';
                break;
            case 8:
                $m = 'Август';
                break;
            case 9:
                $m = 'Сентябрь';
                break;
            case 10:
                $m = 'Октябрь';
                break;
            case 11:
                $m = 'Ноябрь';
                break;
            case 12:
                $m = 'Декабрь';
                break;
        }
        return $m;
    }

    public function translate_to_eng($text, $mode = 'eng')
    {
        $cyr = ['"','\'','а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я', 'А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я'
        ];
        $lat = ['','','a', 'b', 'v', 'g', 'd', 'e', 'io', 'zh', 'z', 'i', 'y', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'ts', 'ch', 'sh', 'sht', 'a', 'i', 'y', 'e', 'yu', 'ya', 'A', 'B', 'V', 'G', 'D', 'E', 'Io', 'Zh', 'Z', 'I', 'Y', 'K', 'L', 'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'F', 'H', 'Ts', 'Ch', 'Sh', 'Sht', 'A', 'I', 'Y', 'e', 'Yu', 'Ya'
        ];
        if ($mode == 'eng') {
            return str_replace($cyr, $lat, $text);
        } else {
            return str_replace($lat, $cyr, $text);
        }
    }

    public static function get_result_format_all_route($event, $participant)
    {
        $custom_red_point = $event->amount_point_redpoint;
        $custom_flash = $event->amount_point_flash;
        $routes_id_passed_with_red_point = ResultRouteQualificationClassic::where('event_id', $event->id)->where('user_id', $participant->id)->where('attempt', ResultRouteQualificationClassic::STATUS_PASSED_REDPOINT)->pluck('route_id');
        $counting_routes_with_red_point_passed = count($routes_id_passed_with_red_point);
        $routes_id_passed_with_flash = ResultRouteQualificationClassic::where('event_id', $event->id)->where('user_id', $participant->id)->where('attempt', ResultRouteQualificationClassic::STATUS_PASSED_FLASH)->pluck('route_id');
        $counting_routes_with_flash_passed = count($routes_id_passed_with_flash);
        if ($routes_id_passed_with_red_point->isNotEmpty()) {
            $sum_all_coefficients_rp = EventAndCoefficientRoute::where('event_id', '=', $event->id)->whereIn('route_id', $routes_id_passed_with_red_point)->get()->sum('coefficient_' . $participant->gender);
            if($sum_all_coefficients_rp == 0){
                Event::update_coefficient_for_all_route($event->id, $participant->gender);
                $sum_all_coefficients_rp = EventAndCoefficientRoute::where('event_id', '=', $event->id)->whereIn('route_id', $routes_id_passed_with_red_point)->get()->sum('coefficient_' . $participant->gender);
            }
            $result_red_point = $counting_routes_with_red_point_passed * $custom_red_point;
            $finish_red_point_result = ($sum_all_coefficients_rp * $result_red_point) / $counting_routes_with_red_point_passed;
        } else {
            $finish_red_point_result = 0;
        }
        if ($routes_id_passed_with_flash->isNotEmpty()) {
            $sum_all_coefficients_flash = EventAndCoefficientRoute::where('event_id', '=', $event->id)->whereIn('route_id', $routes_id_passed_with_flash)->get()->sum('coefficient_' . $participant->gender);
            if($sum_all_coefficients_flash == 0){
                Event::update_coefficient_for_all_route($event->id, $participant->gender);
                $sum_all_coefficients_flash = EventAndCoefficientRoute::where('event_id', '=', $event->id)->whereIn('route_id', $routes_id_passed_with_red_point)->get()->sum('coefficient_' . $participant->gender);
            }
            $result_flash = $counting_routes_with_flash_passed * $custom_flash;
            $finish_flash_result = ($sum_all_coefficients_flash * $result_flash) / $counting_routes_with_flash_passed;
        } else {
            $finish_flash_result = 0;
        }
        return $finish_flash_result + $finish_red_point_result;
    }
    public static function get_result_format_n_route($event, $participant)
    {
        $routes = ResultRouteQualificationClassic::where('event_id', $event->id)
            ->where('user_id', $participant->id)
            ->whereNotIn('attempt', [0])
            ->get();
        foreach ($routes as $route) {
            $event_route = Route::where('grade', '=', $route->grade)->where('event_id', '=', $event->id)->first();
            if (!$event_route) {
                Log::error('При создание соревнований было указано один формат все трасс или француская система, а были с
                сгенерировано трассы, потом изменен формат и пытаемся сгенерироват результат
                ', ['file' => __FILE__, 'line' => __LINE__]);
            }
            $route->value = (new \App\Models\ResultRouteQualificationClassic)->get_value_route($route->attempt, $event_route, $event->mode, $event);
        }
        $routes_id_passed_with_red_point = $routes->sortByDesc('value')->take($event->mode_amount_routes)->where('attempt', ResultRouteQualificationClassic::STATUS_PASSED_REDPOINT)->pluck('route_id');
        if ($routes_id_passed_with_red_point->isNotEmpty()) {
            $finish_red_point_result = $routes->sortByDesc('value')->take($event->mode_amount_routes)->where('attempt', ResultRouteQualificationClassic::STATUS_PASSED_REDPOINT)->sum('value');
        } else {
            $finish_red_point_result = 0;
        }
        $routes_id_passed_with_flash = $routes->sortByDesc('value')->take($event->mode_amount_routes)->where('attempt', ResultRouteQualificationClassic::STATUS_PASSED_FLASH)->pluck('route_id');
        if ($routes_id_passed_with_flash->isNotEmpty()) {
            $finish_flash_result = $routes->sortByDesc('value')->take($event->mode_amount_routes)->where('attempt', ResultRouteQualificationClassic::STATUS_PASSED_FLASH)->sum('value');
        } else {
            $finish_flash_result = 0;
        }
        $routes_id_passed_with_zone = $routes->sortByDesc('value')->take($event->mode_amount_routes)->where('attempt', ResultRouteQualificationClassic::STATUS_ZONE)->pluck('route_id');
        if ($routes_id_passed_with_zone->isNotEmpty()) {
            $finish_zone_result = $routes->sortByDesc('value')->take($event->mode_amount_routes)->where('attempt', ResultRouteQualificationClassic::STATUS_ZONE)->sum('value');
        } else {
            $finish_zone_result = 0;
        }
        return $finish_flash_result + $finish_red_point_result + $finish_zone_result;
    }
    public static function get_result_format_n_outdoor_route($event, $participant)
    {
        $routes = ResultRouteQualificationClassic::where('event_id', $event->id)
            ->where('user_id', $participant->id)
            ->whereNotIn('attempt', [0])
            ->get();
        foreach ($routes as $route) {
            $event_route = RoutesOutdoor::where('grade', '=', $route->grade)->where('event_id', '=', $event->id)->first();
            if (!$event_route) {
                Log::error('При создание соревнований было указано один формат все трасс или француская система, а были с
                сгенерировано трассы, потом изменен формат и пытаемся сгенерироват результат
                ', ['file' => __FILE__, 'line' => __LINE__]);
            }
            $route->value = (new \App\Models\ResultRouteQualificationClassic)->get_value_route($route->attempt, $event_route, $event->mode, $event);
        }
        $routes_id_passed_with_red_point = $routes->sortByDesc('value')->where('attempt', ResultRouteQualificationClassic::STATUS_PASSED_REDPOINT)->pluck('route_id');
        if ($routes_id_passed_with_red_point->isNotEmpty()) {
            $finish_red_point_result = $routes->sortByDesc('value')->where('attempt', ResultRouteQualificationClassic::STATUS_PASSED_REDPOINT)->sum('value');
        } else {
            $finish_red_point_result = 0;
        }
        $routes_id_passed_with_flash = $routes->sortByDesc('value')->where('attempt', ResultRouteQualificationClassic::STATUS_PASSED_FLASH)->pluck('route_id');
        if ($routes_id_passed_with_flash->isNotEmpty()) {
            $finish_flash_result = $routes->sortByDesc('value')->where('attempt', ResultRouteQualificationClassic::STATUS_PASSED_FLASH)->sum('value');
        } else {
            $finish_flash_result = 0;
        }
        $routes_id_passed_with_zone = $routes->sortByDesc('value')->where('attempt', ResultRouteQualificationClassic::STATUS_ZONE)->pluck('route_id');
        if ($routes_id_passed_with_zone->isNotEmpty()) {
            $finish_zone_result = $routes->sortByDesc('value')->where('attempt', ResultRouteQualificationClassic::STATUS_ZONE)->sum('value');
        } else {
            $finish_zone_result = 0;
        }
        return $finish_flash_result + $finish_red_point_result + $finish_zone_result;
    }

    public static function refresh_final_points_all_participant(Event $event)
    {
        $event_id = $event->id;
        $format = $event->mode ?? null;
        if (!$format) {
            Log::info('Обновление без формата 1 или 2, пока что недоступно потому что используется формат подсчета как финал)');
            return;
        }
        $participants = User::query()
            ->leftJoin('result_qualification_classic', 'users.id', '=', 'result_qualification_classic.user_id')
            ->where('result_qualification_classic.event_id', '=', $event_id)
            ->select(
                'users.id',
                'result_qualification_classic.category_id',
                'result_qualification_classic.gender',
                )->where('active', 1)->where('is_other_event', 0);
        $users_id = $participants->pluck('id');
        foreach ($participants->get() as $participant) {
            if($event->type_event){
                $points = self::get_result_format_n_outdoor_route($event, $participant);
            } else {
                if ($format == Format::N_ROUTE) {
                    $points = self::get_result_format_n_route($event, $participant);
                }
                if ($format == Format::ALL_ROUTE) {
                    $points = self::get_result_format_all_route($event, $participant);
                }
            }

            $final_participant_result = ResultQualificationClassic::where('user_id', '=', $participant->id)->where('event_id', '=', $event_id)->first();
            if(!$participant){
                Log::error('Category id not found -event_id - '.$participant->id.'user_id'.$event_id, ['file' => __FILE__, 'line' => __LINE__]);
            }
            if ($event->is_auto_categories) {
                $the_best_route_passed = Grades::findMaxIndices(Grades::grades(), ResultQualificationClassic::get_list_passed_route($event->id, $participant->id), 3);
                $count_passed_route = ResultQualificationClassic::get_list_passed_route($event->id, $participant->id);
                if(count($the_best_route_passed) === 0 || count($count_passed_route) < 3) {
                    $category_id = 0;
                    if(isset($final_participant_result->category_id)){
                        if($final_participant_result->category_id){
                            $category_id = $final_participant_result->category_id;
                        }
                    }
                } else {
                    $category = ResultQualificationClassic::get_category_from_result($event, $the_best_route_passed, $participant->id);
                    $category_id = ParticipantCategory::where('event_id', '=', $event_id)->where('category', $category)->first();
                    if(!$category_id){
                         Log::error('Не удалось определить категорию - у юзера'.$participant->id, ['file' => __FILE__, 'line' => __LINE__]);
                        $category_id = 0;
                    } else {
                        $category_id = $category_id->id;
                    }
                }
                $final_participant_result->category_id = $category_id;
            }
            $final_participant_result->points = $points;
            $final_participant_result->event_id = $event_id;
            $final_participant_result->user_id = $participant->id;
            $final_participant_result->save();
        }

        $categories = ParticipantCategory::where('event_id', '=', $event_id)->get();
        if ($event->is_sort_group_final) {
            foreach (['female', 'male'] as $gender) {
                foreach ($categories as $category) {
                    $participants_for_update = ResultQualificationClassic::whereIn('user_id', $users_id)
                        ->where('category_id', $category->id)
                        ->where('event_id', $event_id)
                        ->where('gender', $gender)
                        ->orderByDesc('points')
                        ->get();
                    ResultQualificationClassic::update_places_in_qualification_classic($participants_for_update);
                }
            }
        } else {
            foreach (['female', 'male'] as $gender){
                $participants_for_update = ResultQualificationClassic::whereIn('user_id', $users_id)->where('event_id', '=', $event_id)->where('gender', $gender)->orderByDesc('points')->get();
                ResultQualificationClassic::update_places_in_qualification_classic($participants_for_update);
            }
        }
    }
    public static function refresh_final_points_all_participant_in_semifinal($event_id)
    {
        $event = Event::find($event_id);
        $amount_the_best_participant = $event->amount_the_best_participant ?? self::DEFAULT_SEMIFINAL_PARTICIPANT;
        $fields = ['firstname', 'id', 'category', 'active', 'team', 'city', 'email', 'year', 'lastname', 'skill', 'sport_category', 'email_verified_at', 'created_at', 'updated_at'];
        if ($event->is_france_system_qualification) {
            if ($event->is_sort_group_semifinal) {
                $all_group_participants = array();
                foreach ($event->categories as $category) {
                    $category_id = ParticipantCategory::where('category', $category)->where('event_id', $event->id)->first()->id;
                    $all_group_participants['male'][$category] = ResultFranceSystemQualification::better_of_participants_france_system_qualification($event->id, 'male', $amount_the_best_participant, $category_id);
                    $all_group_participants['female'][$category] = ResultFranceSystemQualification::better_of_participants_france_system_qualification($event->id, 'female', $amount_the_best_participant, $category_id);
                }
                foreach ($all_group_participants as $group_participants) {
                    foreach ($group_participants as $participants) {
                        Event::getUsersSorted($participants, $fields, $event, 'semifinal', $event->owner_id);
                    }
                }
            } else {
                $users_female = ResultFranceSystemQualification::better_of_participants_france_system_qualification($event_id, 'female', $amount_the_best_participant);
                $users_male = ResultFranceSystemQualification::better_of_participants_france_system_qualification($event_id, 'male', $amount_the_best_participant);
                Event::getUsersSorted($users_female, $fields, $event, 'semifinal', $event->owner_id);
                Event::getUsersSorted($users_male, $fields, $event, 'semifinal', $event->owner_id);
            }
        } else {
            if ($event->is_sort_group_semifinal) {
                # Если выбран режим что финал для всех то отдаем лучшех 6 участников каждый категории
                $all_group_participants = array();
                foreach ($event->categories as $category) {
                    $category_id = ParticipantCategory::where('category', $category)->where('event_id', $event->id)->first()->id;
                    if($event->is_open_main_rating){
                        $all_group_participants['male'][$category] = ResultQualificationClassic::better_global_participants($event->id, 'male', $amount_the_best_participant, $category_id);
                        $all_group_participants['female'][$category] = ResultQualificationClassic::better_global_participants($event->id, 'female', $amount_the_best_participant, $category_id);
                    } else {
                        $all_group_participants['male'][$category] = ResultQualificationClassic::better_participants($event->id, 'male', $amount_the_best_participant, $category_id);
                        $all_group_participants['female'][$category] = ResultQualificationClassic::better_participants($event->id, 'female', $amount_the_best_participant, $category_id);
                    }

                }
                foreach ($all_group_participants as $group_participants) {
                    foreach ($group_participants as $participants) {
                        Event::getUsersSorted($participants, $fields, $event, 'semifinal', $event->owner_id);
                    }
                }
            } else {
                if($event->is_open_main_rating){
                    $users_male = ResultQualificationClassic::better_global_participants($event_id, 'male', $amount_the_best_participant);
                    $users_female = ResultQualificationClassic::better_global_participants($event_id, 'female', $amount_the_best_participant);
                } else {
                    $users_male = ResultQualificationClassic::better_participants($event_id, 'male', $amount_the_best_participant);
                    $users_female = ResultQualificationClassic::better_participants($event_id, 'female', $amount_the_best_participant);
                }
                Event::getUsersSorted($users_female, $fields, $event, 'semifinal', $event->owner_id);
                Event::getUsersSorted($users_male, $fields, $event, 'semifinal', $event->owner_id);
            }
        }

    }

    public static function refresh_france_system_qualification_counting($event)
    {
        $fields = ['firstname', 'id', 'category', 'active', 'team', 'city', 'email', 'year', 'lastname', 'skill', 'sport_category', 'email_verified_at', 'created_at', 'updated_at'];
        if ($event->is_sort_group_final) {
            $all_group_participants = array();
            foreach ($event->categories as $category) {
                $category_id = ParticipantCategory::where('category', $category)->where('event_id', $event->id)->first()->id;
                $part_male_nt = ResultRouteFranceSystemQualification::where('event_id', '=', $event->id)->where('gender', '=', 'male')->where('category_id', $category_id)->distinct()->pluck('user_id');
                $part_female_nt = ResultRouteFranceSystemQualification::where('event_id', '=', $event->id)->where('gender', '=', 'female')->where('category_id', $category_id)->distinct()->pluck('user_id');
                $all_group_participants['male'][$category] = User::whereIn('id', $part_male_nt)->get();
                $all_group_participants['female'][$category] = User::whereIn('id', $part_female_nt)->get();
            }
            foreach ($all_group_participants as $group_participants) {
                foreach ($group_participants as $participants) {
                    Event::getUsersSorted($participants, $fields, $event, 'france_system_qualification', Admin::user()->id);
                }
            }
        } else {
            $participant_users_female_id = ResultFranceSystemQualification::where('event_id', '=', $event->id)->where('gender', 'female')->pluck('user_id')->toArray();
            $participant_users_male_id = ResultFranceSystemQualification::where('event_id', '=', $event->id)->where('gender', 'male')->pluck('user_id')->toArray();
            $participants_female = User::whereIn('id', $participant_users_female_id)->get();
            $participants_male = User::whereIn('id', $participant_users_male_id)->get();
            Event::getUsersSorted($participants_female, $fields, $event, 'france_system_qualification', Admin::user()->id);
            Event::getUsersSorted($participants_male, $fields, $event, 'france_system_qualification', Admin::user()->id);
        }
    }

    public static function refresh_final_points_all_participant_in_final($event_id)
    {
        $event = Event::find($event_id);
        $amount_the_best_participant_to_go_final = $event->amount_the_best_participant_to_go_final ?? self::DEFAULT_FINAL_PARTICIPANT;
        $fields = ['firstname', 'id', 'category', 'active', 'team', 'city', 'email', 'year', 'lastname', 'skill', 'sport_category', 'email_verified_at', 'created_at', 'updated_at'];
        if ($event->is_semifinal) {
            if ($event->is_sort_group_final) {
                # Если выбран режим что финал для всех то отдаем лучшех 6 участников каждый категории
                $all_group_participants = array();
                foreach ($event->categories as $category) {
                    $category_id = ParticipantCategory::where('category', $category)->where('event_id', $event->id)->first()->id;
                    $all_group_participants['male'][$category] = ResultSemiFinalStage::better_of_participants_semifinal_stage($event->id, 'male', $amount_the_best_participant_to_go_final, $category_id);
                    $all_group_participants['female'][$category] = ResultSemiFinalStage::better_of_participants_semifinal_stage($event->id, 'female', $amount_the_best_participant_to_go_final, $category_id);
                }
                foreach ($all_group_participants as $group_participants) {
                    foreach ($group_participants as $participants) {
                        Event::getUsersSorted($participants, $fields, $event, 'final', $event->owner_id);
                    }
                }
            } else {
                $users_male = ResultSemiFinalStage::better_of_participants_semifinal_stage($event_id, 'male', $amount_the_best_participant_to_go_final);
                $users_female = ResultSemiFinalStage::better_of_participants_semifinal_stage($event_id, 'female', $amount_the_best_participant_to_go_final);
                Event::getUsersSorted($users_female, $fields, $event, 'final', $event->owner_id);
                Event::getUsersSorted($users_male, $fields, $event, 'final', $event->owner_id);
            }
        } else {
            if ($event->is_france_system_qualification) {
                if ($event->is_sort_group_final) {
                    $all_group_participants = array();
                    foreach ($event->categories as $category) {
                        $category_id = ParticipantCategory::where('category', $category)->where('event_id', $event->id)->first()->id;
                        $all_group_participants['male'][$category] = ResultFranceSystemQualification::better_of_participants_france_system_qualification($event->id, 'male', $amount_the_best_participant_to_go_final, $category_id);
                        $all_group_participants['female'][$category] = ResultFranceSystemQualification::better_of_participants_france_system_qualification($event->id, 'female', $amount_the_best_participant_to_go_final, $category_id);
                    }
                    foreach ($all_group_participants as $group_participants) {
                        foreach ($group_participants as $participants) {
                            Event::getUsersSorted($participants, $fields, $event, 'final', $event->owner_id);
                        }
                    }
                } else {
                    $users_female = ResultFranceSystemQualification::better_of_participants_france_system_qualification($event_id, 'female', $amount_the_best_participant_to_go_final);
                    $users_male = ResultFranceSystemQualification::better_of_participants_france_system_qualification($event_id, 'male', $amount_the_best_participant_to_go_final);
                    Event::getUsersSorted($users_female, $fields, $event, 'final', $event->owner_id);
                    Event::getUsersSorted($users_male, $fields, $event, 'final', $event->owner_id);
                }
            } else {
                if ($event->is_sort_group_final) {
                    # Если выбран режим что финал для всех то отдаем лучшех 6 участников каждый категории
                    $all_group_participants = array();
                    foreach ($event->categories as $category) {
                        $category_id = ParticipantCategory::where('category', $category)->where('event_id', $event->id)->first()->id;
                        if($event->is_open_main_rating){
                            $all_group_participants['male'][$category] = ResultQualificationClassic::better_global_participants($event->id, 'male', $amount_the_best_participant_to_go_final, $category_id);
                            $all_group_participants['female'][$category] = ResultQualificationClassic::better_global_participants($event->id, 'female', $amount_the_best_participant_to_go_final, $category_id);
                        } else {
                            $all_group_participants['male'][$category] = ResultQualificationClassic::better_participants($event->id, 'male', $amount_the_best_participant_to_go_final, $category_id);
                            $all_group_participants['female'][$category] = ResultQualificationClassic::better_participants($event->id, 'female', $amount_the_best_participant_to_go_final, $category_id);
                        }

                    }
                    foreach ($all_group_participants as $group_participants) {
                        foreach ($group_participants as $participants) {
                            Event::getUsersSorted($participants, $fields, $event, 'final', $event->owner_id);
                        }
                    }
                } else {
                    if($event->is_open_main_rating){
                        $users_female = ResultQualificationClassic::better_global_participants($event_id, 'female', $amount_the_best_participant_to_go_final);
                        $users_male = ResultQualificationClassic::better_global_participants($event_id, 'male', $amount_the_best_participant_to_go_final);
                    } else {
                        $users_female = ResultQualificationClassic::better_participants($event_id, 'female', $amount_the_best_participant_to_go_final);
                        $users_male = ResultQualificationClassic::better_participants($event_id, 'male', $amount_the_best_participant_to_go_final);
                    }
                    Event::getUsersSorted($users_female, $fields, $event, 'final', $event->owner_id);
                    Event::getUsersSorted($users_male, $fields, $event, 'final', $event->owner_id);
                }
            }

        }

    }
    public static function update_attempt_for_participant($event_id, $final_data)
    {
        foreach ($final_data as $data){
            $result = ResultRouteQualificationClassic::where('event_id', $event_id)->where('user_id', $data['user_id'])->where('route_id', $data['route_id'])->first();
            $result->attempt = $data['attempt'];
            $result->save();
        }
    }
    public function insert_final_participant_result($event_id, $points, $user_id)
    {
        $final_participant_result = ResultQualificationClassic::where('event_id', '=', $event_id)->where('user_id', '=', $user_id)->first();
        $final_participant_result->points = $final_participant_result->points + $points;
        $final_participant_result->active = 1;
        $final_participant_result->save();
    }

    public static function update_category_id($event, $user_id)
    {
        $participant_result = ResultQualificationClassic::where('event_id', '=', $event->id)->where('user_id', '=', $user_id)->first();
        if ($event->is_auto_categories && $participant_result->category_id == 0) {
            $result_qualification = ResultRouteQualificationClassic::where('event_id', '=', $event->id)->where('user_id', '=', $user_id)->first();
            if($result_qualification){
                $the_best_route_passed = Grades::findMaxIndices(Grades::grades(), ResultQualificationClassic::get_list_passed_route($event->id, $user_id), 3);
                if(count($the_best_route_passed) === 0){
                    $category_id = 0;
                } else {
                    $category = ResultQualificationClassic::get_category_from_result($event, $the_best_route_passed, $user_id);
                    $category_id = ParticipantCategory::where('event_id', '=', $event->id)->where('category', $category)->first();
                    if(!$category_id){
                        Log::error('Не удалось определить категорию - у юзера'.$user_id, ['file' => __FILE__, 'line' => __LINE__]);
                        $category_id = 0;
                    } else {
                        $category_id = $category_id->id;
                    }

                }
                $participant_result->category_id = $category_id;
                $participant_result->save();
            }
        }
    }

    public static function force_update_category_id($event, $user_id)
    {
        $participant_result = ResultQualificationClassic::where('event_id', '=', $event->id)->where('user_id', '=', $user_id)->first();
        if ($event->is_auto_categories) {
            $result_qualification = ResultRouteQualificationClassic::where('event_id', '=', $event->id)->where('user_id', '=', $user_id)->first();
            if($result_qualification){
                $the_best_route_passed = Grades::findMaxIndices(Grades::grades(), ResultQualificationClassic::get_list_passed_route($event->id, $user_id), 3);
                if(count($the_best_route_passed) === 0){
                    $category_id = 0;
                } else {
                    $category = ResultQualificationClassic::get_category_from_result($event, $the_best_route_passed, $user_id);
                    $category_id = ParticipantCategory::where('event_id', '=', $event->id)->where('category', $category)->first();
                    if(!$category_id){
                        Log::error('Не удалось определить категорию - у юзера'.$user_id, ['file' => __FILE__, 'line' => __LINE__]);
                        $category_id = 0;
                    } else {
                        $category_id = $category_id->id;
                    }
                }
                $participant_result->category_id = $category_id;
                $participant_result->save();
            }
        }
    }


    public static function get_france_system_result($table, $event_id, $gender, $category = null)
    {
        $event = Event::find($event_id);
        $max_routes = Grades::where('event_id', $event->id)->first()->count_routes ?? 0;
        $users = User::query()
            ->leftJoin($table, 'users.id', '=', $table . '.user_id')
            ->where($table . '.event_id', '=', $event_id)
            ->where($table . '.amount_top', '!=', null)
            ->where($table . '.amount_try_top', '!=', null)
            ->where($table . '.amount_zone', '!=', null)
            ->where($table . '.amount_try_zone', '!=', null)
            ->select(
                $table . '.place',
                'users.id',
                'users.middlename',
                'users.city',
                $table . '.category_id',
                $table . '.gender',
                $table . '.amount_top',
                $table . '.amount_try_top',
                $table . '.amount_zone',
                $table . '.amount_try_zone',
            )->where($table . '.gender', '=', $gender);

        if ($category) {
            $users = $users->where('category_id', '=', $category->id);
        }
        $users = $users->get()->sortBy('place')->toArray();

        foreach ($users as $index => $user) {
            if ($table == 'result_final_stage') {
                $final_result = ResultRouteFinalStage::where('event_id', '=', $event_id)->where('user_id', '=', $user['id'])->get();
            }
            if ($table === "result_france_system_qualification") {
                $final_result = ResultRouteFranceSystemQualification::where('event_id', '=', $event_id)->where('user_id', '=', $user['id'])->get();
            }
            if ($table === "result_semifinal_stage") {
                $final_result = ResultRouteSemiFinalStage::where('event_id', '=', $event_id)->where('user_id', '=', $user['id'])->get();
            }
            foreach ($final_result as $result) {
                if ($table == 'result_final_stage' || $table === "result_semifinal_stage") {
                    $route_id = $result->final_route_id;
                } else {
                    $route_id = $result->route_id;
                }
                $users[$index]['amount_top_' . $route_id] = $result->amount_top;
                $users[$index]['amount_try_top_' . $route_id] = $result->amount_try_top;
                $users[$index]['amount_zone_' . $route_id] = $result->amount_zone;
                $users[$index]['amount_try_zone_' . $route_id] = $result->amount_try_zone;
            }
            // Заполняем 0 для трасс, которые не были добавлены
            for ($i = 1; $i <= $max_routes; $i++) {
                if (!isset($users[$index]['amount_top_' . $i])) {
                    $users[$index]['amount_top_' . $i] = 0;
                    $users[$index]['amount_try_top_' . $i] = 0;
                    $users[$index]['amount_zone_' . $i] = 0;
                    $users[$index]['amount_try_zone_' . $i] = 0;
                }
            }
            $users[$index] = collect($users[$index])->except('id');
        }
        return collect($users);
    }

    /**
     * @param $users
     * @param $fields
     * @param $model
     * @param $type
     * @return array
     */
    public static function getUsersSorted($users, $fields, $model, $type, $owner_id): array
    {

        if (count($users->toArray()) == 0) {
            return [];
        }
        $users_with_result = [];
        foreach ($users as $index => $user) {
            switch ($type) {
                case 'final':
                    $result_user = ResultRouteFinalStage::where('owner_id', '=', $owner_id)
                        ->where('event_id', '=', $model->id)
                        ->where('user_id', '=', $user->id)
                        ->get();
                    break;
                case 'france_system_qualification':
                    $result_user = ResultRouteFranceSystemQualification::where('owner_id', '=', $owner_id)
                        ->where('event_id', '=', $model->id)
                        ->where('user_id', '=', $user->id)
                        ->get();
                    break;
                case 'semifinal':
                    $result_user = ResultRouteSemiFinalStage::where('owner_id', '=', $owner_id)
                        ->where('event_id', '=', $model->id)
                        ->where('user_id', '=', $user->id)
                        ->get();
            }

            $result = ResultRouteSemiFinalStage::merge_result_user_in_stage($result_user);
            if ($result['amount_top'] !== null && $result['amount_try_top'] !== null && $result['amount_zone'] !== null && $result['amount_try_zone'] !== null) {
                $users_with_result[$index] = collect($user->toArray())->except($fields);
                $users_with_result[$index]['result'] = $result;
                $users_with_result[$index]['place'] = null;
                $users_with_result[$index]['category_id'] = $result['category_id'];
                $users_with_result[$index]['owner_id'] = $owner_id;
                $users_with_result[$index]['user_id'] = $user->id;
                $users_with_result[$index]['event_id'] = $model->id;
                $users_with_result[$index]['gender'] = trans_choice('somewords.' . $user->gender, 10);
                $users_with_result[$index]['amount_top'] = $result['amount_top'];
                $users_with_result[$index]['amount_zone'] = $result['amount_zone'];
                $users_with_result[$index]['amount_try_top'] = $result['amount_try_top'];
                $users_with_result[$index]['amount_try_zone'] = $result['amount_try_zone'];
            }
        }

        $users_sorted = ResultQualificationClassic::counting_final_place($model->id, $users_with_result, $type);
//        $users_sorted = Participant::counting_final_place($model->id, $users_sorted, 'qualification');
        ### ПРОВЕРИТЬ НЕ СОХРАНЯЕМ ЛИ МЫ ДВА РАЗА ЗДЕСЬ И ПОСЛЕ КУДА ВОЗРАЩАЕТ $users_sorted
        foreach ($users_sorted as $index => $user) {
            $fields = ['result'];
            $users_sorted[$index] = collect($user)->except($fields)->toArray();
            if ($type == 'final' || $type == 'france_system_qualification') {
                if ($type == 'france_system_qualification') {
                    $result = ResultFranceSystemQualification::where('user_id', '=', $users_sorted[$index]['user_id'])->where('event_id', '=', $model->id)->first();
                    if (!$result) {
                        $result = new ResultFranceSystemQualification;
                    }
                } else {
                    $result = ResultFinalStage::where('user_id', '=', $users_sorted[$index]['user_id'])->where('event_id', '=', $model->id)->first();
                    if (!$result) {
                        $result = new ResultFinalStage;
                    }
                }
            } else {
                $result = ResultSemiFinalStage::where('user_id', '=', $users_sorted[$index]['user_id'])->where('event_id', '=', $model->id)->first();
                if (!$result) {
                    $result = new ResultSemiFinalStage;
                }
            }
            $category_id = ParticipantCategory::where('id', $users_sorted[$index]['category_id'])->where('event_id', $model->id)->first();
            if ($category_id) {
                $category_id = $category_id->id;
                $result->category_id = $category_id;
            } else {
                Log::error('It has not found category_id ' . $users_sorted[$index]['category_id'] . ' ' . $model->id, ['file' => __FILE__, 'line' => __LINE__]);
            }
            $result->event_id = $users_sorted[$index]['event_id'];
            $result->user_id = $users_sorted[$index]['user_id'];
//            $result->gender = trans_choice('somewords.' . $users_sorted[$index]['gender'], 10);
            $result->owner_id = $users_sorted[$index]['owner_id'];
            $result->amount_top = $users_sorted[$index]['amount_top'];
            $result->amount_zone = $users_sorted[$index]['amount_zone'];
            $result->amount_try_top = $users_sorted[$index]['amount_try_top'];
            $result->amount_try_zone = $users_sorted[$index]['amount_try_zone'];
            $result->place = $users_sorted[$index]['place'];

            $result->save();
        }
        return $users_sorted;
    }

    public static function validate_result($result)
    {
        $amount_false = 0;
        foreach ($result as $res) {
            if (str_contains($res[0], 'flash') && $res[1] == "false") {
                $amount_false++;
            }
            if (str_contains($res[0], 'redpoint') && $res[1] == "false") {
                $amount_false++;
            }
            if (str_contains($res[0], 'failed') && $res[1] == "false") {
                $amount_false++;
            }
            if (str_contains($res[0], 'zone') && $res[1] == "false") {
                $amount_false++;
            }
        }
        return $amount_false;
    }


    public static function send_result_final($event_id, $owner_id, $user_id, $category_id, $result_for_edit, $gender)
    {
        $participant = ResultFinalStage::where('event_id', $event_id)->where('user_id', $user_id)->first();
        if(!$participant){
            $participant = new ResultFinalStage;
            $new_result_for_edit = $result_for_edit;
        } else {
            // Сортируем массив по "Номеру маршрута"
            $new_result_for_edit = array_merge($participant->result_for_edit_final, $result_for_edit);
            usort($new_result_for_edit, function ($a, $b) {
                return $a['Номер маршрута'] <=> $b['Номер маршрута'];
            });
        }
        $participant->owner_id = $owner_id;
        $participant->event_id = $event_id;
        $participant->user_id = $user_id;
        $participant->category_id = $category_id;
        $participant->gender = $gender;
        $participant->result_for_edit_final = $new_result_for_edit;
        $participant->save();
    }
    public static function send_result_semifinal($event_id, $owner_id,$user_id, $category_id, $result_for_edit, $gender)
    {
        $participant = ResultSemiFinalStage::where('event_id', $event_id)->where('user_id', $user_id)->first();
        if(!$participant){
            $participant = new ResultSemiFinalStage;
            $new_result_for_edit = $result_for_edit;
        } else {
            $new_result_for_edit = array_merge($participant->result_for_edit_semifinal, $result_for_edit);
            usort($new_result_for_edit, function ($a, $b) {
                return $a['Номер маршрута'] <=> $b['Номер маршрута'];
            });
        }

        $participant->owner_id = $owner_id;
        $participant->event_id = $event_id;
        $participant->user_id = $user_id;
        $participant->category_id = $category_id;
        $participant->gender = $gender;
        $participant->result_for_edit_semifinal = $new_result_for_edit;
        $participant->save();
    }

    public static function merge_point($users_ids, $event_ids, $active_event)
    {
        # Чисто подсчет очков
        foreach ($users_ids as $user_id) {
            $sum_points = ResultQualificationClassic::whereIn('event_id', $event_ids)->where('user_id', $user_id)->get()->sum('points');
            $all_points = ResultQualificationClassic::whereIn('event_id', $event_ids)->where('user_id', $user_id)->get()->pluck('points')->toArray();
            $all_user_places = ResultQualificationClassic::whereIn('event_id', $event_ids)->where('user_id', $user_id)->get()->pluck('user_place')->toArray();
            $all_categories = ResultQualificationClassic::whereIn('event_id', $event_ids)->where('user_id', $user_id)->get()->pluck('category_id', 'event_id')->toArray();
            $users_result = ResultQualificationClassic::whereIn('event_id', $event_ids)->where('active', 1)->where('user_id', $user_id)->first();
            $active_event_result = ResultQualificationClassic::where('event_id', $active_event->id)->where('user_id', $user_id)->first();
            if ($users_result) {
                $gender = $users_result->gender;
            }
            if ($active_event_result) {
                $categories_name = [];
                $active_event_result->last_points_after_merged = $all_points;
                $active_event_result->last_user_place_after_merged = $all_user_places;
                if(count($all_categories) > 0){
                    foreach ($all_categories as $category){
                        if($category != 0){
                            $participant_category = ParticipantCategory::find($category);
                            if(!$participant_category){
                                Log::error('category - '.$category.' user_id - '.$user_id, ['file' => __FILE__, 'line' => __LINE__ ]);
                                $categories_name[] = 'Не определена';
                            } else {
                                $categories_name[] = $participant_category->category;
                            }
                        } else {
                            $categories_name[] = 'Не определена';
                        }
                    }
                }
                $active_event_result->last_category_after_merged = $categories_name;
                $active_event_result->global_points = $sum_points;
                $active_event_result->save();
            } else {
                if ($active_event->is_auto_categories) {
                    $category_id = 0;
                } else {
                    $participant_category = ParticipantCategory::find($users_result->category_id);
                    $active_event_category = ParticipantCategory::where('event_id', $active_event->id)->where('category', $participant_category->category)->first();
                    if ($active_event_category) {
                        $category_id = $active_event_category->id;
                    }
                }
                $owner_id = Admin::user()->id;
                $active_event_result = new ResultQualificationClassic;
                $active_event_result->owner_id = $owner_id;
                $active_event_result->event_id = $active_event->id;
                $active_event_result->user_id = $user_id;
                $active_event_result->gender = $gender;
                $categories_name = [];
                $active_event_result->last_points_after_merged = $all_points;
                $active_event_result->last_user_place_after_merged = $all_user_places;
                if(count($all_categories) > 0){
                    foreach ($all_categories as $category){
                        if($category != 0){
                            $participant_category = ParticipantCategory::find($category);
                            if(!$participant_category){
                                Log::error('category - '.$category.' user_id - '.$user_id, ['file' => __FILE__, 'line' => __LINE__ ]);
                                $categories_name[] = 'Не определена';
                            } else {
                                $categories_name[] = $participant_category->category;
                            }
                        } else {
                            $categories_name[] = 'Не определена';
                        }
                    }
                }
                $active_event_result->last_category_after_merged = $categories_name;
                $active_event_result->global_points = $sum_points;
                $active_event_result->category_id = $category_id;
                $active_event_result->number_set_id = 0;
                $active_event_result->active = 0;
                $active_event_result->is_other_event = 1;
            }
            $active_event_result->save();
            }
    }
    public static function merge_auto_categories($event, $users_ids, $event_ids)
    {
        foreach ($users_ids as $user_id){

            $users_result = ResultQualificationClassic::where('event_id', $event->id)->where('user_id', $user_id)->first();
            if(isset($users_result)){
                $the_best_route_passed = Grades::findMaxIndices(Grades::grades(), ResultQualificationClassic::get_global_list_passed_route($event_ids, $user_id), 3);
                $category = ResultQualificationClassic::get_category_from_result($event, $the_best_route_passed, $user_id);
                $category_id = ParticipantCategory::where('event_id', '=', $event->id)->where('category', $category)->first();
                if(!$category_id){
                    Log::error('Не удалось определить категорию - у юзера'.$user_id, ['file' => __FILE__, 'line' => __LINE__]);
                    $category_id = 0;
                } else {
                    $category_id = $category_id->id;
                }
                $users_result->global_category_id = $category_id;
                $users_result->save();
            }
        }
    }
    public static function merge_categories($event, $users_ids)
    {
        foreach ($users_ids as $user_id){
            $users_result = ResultQualificationClassic::where('event_id', $event->id)->where('user_id', $user_id)->first();
            if($users_result){
                $users_result->global_category_id = $users_result->category_id;
                $users_result->save();
            }
        }
    }
    public static function update_event_after_merged(Event $event, array $event_ids)
    {
        $event->is_open_main_rating = 1;
        $event->list_merged_events = $event_ids;
        $event->save();
    }

    public static function counting_global_category_place($event)
    {
        $participants = User::query()
            ->leftJoin('result_qualification_classic', 'users.id', '=', 'result_qualification_classic.user_id')
            ->where('result_qualification_classic.event_id', '=', $event->id)
            ->select(
                'users.id',
                'result_qualification_classic.global_category_id',
                'result_qualification_classic.gender',
            );
        $users_id = $participants->pluck('id');
        $categories = ParticipantCategory::where('event_id', '=', $event->id)->get();
        if ($event->is_sort_group_final) {
            foreach (['male', 'female'] as $gender) {
                foreach ($categories as $category) {
                    $participants_for_update = ResultQualificationClassic::whereIn('user_id', $users_id)
                        ->where('global_category_id', $category->id)
                        ->where('event_id', $event->id)
                        ->where('gender', $gender)
                        ->orderBy('global_points', 'DESC')
                        ->get();
                    ResultQualificationClassic::update_global_places_in_qualification_classic($event->id, $participants_for_update);
                }
            }
        } else {
            foreach (['female', 'male'] as $gender){
                $participants_for_update = ResultQualificationClassic::whereIn('user_id', $users_id)->where('event_id', '=', $event->id)->where('gender', $gender)->orderBy('global_points', 'DESC')->get();
                ResultQualificationClassic::update_global_places_in_qualification_classic($event->id, $participants_for_update);
            }
        }
    }

    public static function counting_global_points_place($event)
    {
        $participants = User::query()
            ->leftJoin('result_qualification_classic', 'users.id', '=', 'result_qualification_classic.user_id')
            ->where('result_qualification_classic.event_id', '=', $event->id)
            ->select(
                'users.id',
                'result_qualification_classic.global_category_id',
                'result_qualification_classic.gender',
            )->where('active', 1);
        $users_id = $participants->pluck('id');
        $categories = ParticipantCategory::where('event_id', '=', $event->id)->get();
        if ($event->is_sort_group_final) {
            foreach (['female', 'male'] as $gender) {
                foreach ($categories as $category) {
                    $participants_for_update = ResultQualificationClassic::whereIn('user_id', $users_id)
                        ->where('global_category_id', $category->id)
                        ->where('event_id', $event->id)
                        ->where('gender', $gender)
                        ->orderBy('global_points', 'desc')
                        ->get();
                    ResultQualificationClassic::update_global_places_in_qualification_classic($event->id, $participants_for_update);
                }
            }
        } else {
            foreach (['female', 'male'] as $gender){
                $participants_for_update = ResultQualificationClassic::whereIn('user_id', $users_id)->where('event_id', '=', $event->id)->where('gender', $gender)->orderBy('global_points', 'DESC')->get();
                ResultQualificationClassic::update_global_places_in_qualification_classic($event->id, $participants_for_update);
            }
        }


    }
    public static function update_coefficient_for_all_route($event_id, $gender)
    {
        $result_with_routes = Route::where('event_id', $event_id)->get();
        $active_participant = ResultQualificationClassic::participant_with_result($event_id, $gender);
        foreach ($result_with_routes as $routes){
            $record = EventAndCoefficientRoute::where('event_id', '=', $event_id)->where('route_id', '=', $routes->route_id)->first();
            if ($record === null) {
                $event_and_coefficient_route = new EventAndCoefficientRoute;
            } else {
                $event_and_coefficient_route = $record;
            }
            $count_route_passed = ResultRouteQualificationClassic::counting_result($event_id, $routes->route_id, $gender);
            $coefficient = ResultRouteQualificationClassic::get_coefficient($active_participant, $count_route_passed);
            $event_and_coefficient_route->event_id = $event_id;
            $event_and_coefficient_route->route_id = $routes->route_id;
            $event_and_coefficient_route->owner_id = $routes->owner_id;
            if($gender === 'male') {
                $event_and_coefficient_route->coefficient_male = $coefficient;
            } else {
                $event_and_coefficient_route->coefficient_female = $coefficient;
            }
            $event_and_coefficient_route->save();
        }


    }

    /**
     * Этап закрыт если
     * Регистрация закрыта
     * Закрыт доступ к редактированию
     * Закрыт доступ к отправке результатов
     * Даты конца соревнования прошли
     *
     * @return bool
     * @var Event $event
     */
    public static function event_is_open(Event $event): bool
    {
        $now = Carbon::today();
        $now->setTimezone('Europe/Moscow');
        if(!$event->is_access_user_edit_result && !$event->is_send_result_state && !$event->is_registration_state && $event->end_date < $now){
            return false;
        } else {
            return true;
        }
    }

    public static function refresh_grade_all_participant_in_result_for_edit($event, $route, $replace_to_grade)
    {
        $event_id = $event->id;
        $participants = ResultQualificationClassic::where('event_id', $event_id)
            ->where('active', 1)
            ->where('is_other_event', 0)
            ->get();
        foreach ($participants as $participant) {
            $results = $participant->result_for_edit; // Получаем текущий массив результатов
            // Проверка, если result_for_edit действительно массив
            if (is_array($results)) {
                foreach ($results as &$result) { // Обрабатываем массив по ссылке
                    // Приведение к одному типу перед сравнением (например, к строке)
                    if ((string)$result['route_id'] === (string)$route->route_id) {
                        $result['grade'] = $replace_to_grade; // Обновляем значение grade
                        break; // Прекращаем цикл после изменения
                    }
                }
            }
            // Сохраняем измененный массив обратно в модель
            $participant->result_for_edit = $results;
            $participant->save(); // Сохраняем изменения в базе данных
        }
    }

    public static function refresh_grade_all_participant_in_route_result($event, $route, $grade)
    {
        $event_id = $event->id;
        $owner_id = $event->owner_id;
        $format = $event->mode ?? null;

        if (!$format) {
            Log::info('Обновление без формата 1 или 2, пока что недоступно, потому что используется формат подсчета как финал');
            return;
        }
        // Получаем всех участников, соответствующих заданному event_id и route_id
        $participants = ResultRouteQualificationClassic::where('event_id', $event_id)
            ->where('route_id', $route->route_id)
            ->get(); // Выбираем только нужные поля
        // Обрабатываем каждого участника
        foreach ($participants as $participant) {
            $participant->grade = $grade; // Обновляем grade
            if ($participant->value) { // Если значение value существует
                // Получаем новое значение value
                $value_route = (new \App\Models\ResultRouteQualificationClassic)->get_value_route($route->attempt, $owner_id, $format, $event);
                $participant->value = $value_route; // Обновляем value
            }
            $participant->save();
        }

    }

    public static function get_type_counting_france_system(array $results, int $type)
    {
        switch ($type) {
            case self::INTER:
               usort($results, function ($a, $b) {
                    return $b['amount_top'] <=> $a['amount_top']
                        ?: $b['amount_zone'] <=> $a['amount_zone']
                            ?: $a['amount_try_top'] <=> $b['amount_try_top']
                                ?: $a['amount_try_zone'] <=> $b['amount_try_zone'];
                });
                break;
            case self::LOCAL:
                usort($results, function ($a, $b) {
                    return $b['amount_top'] <=> $a['amount_top']
                        ?: $a['amount_try_top'] <=> $b['amount_try_top']
                            ?: $b['amount_zone'] <=> $a['amount_zone']
                                ?: $a['amount_try_zone'] <=> $b['amount_try_zone'];
                });
        }
        return $results;
    }
}
