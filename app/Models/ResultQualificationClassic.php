<?php

namespace App\Models;

use App\Helpers\Helpers;
use App\Scopes\EventScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ResultQualificationClassic extends Model
{
    use HasFactory;
    public $timestamps = true;

//    protected static function booted()
//    {
//        $eventId = session('event_id');
//        static::addGlobalScope(new EventScope($eventId));
//    }

    protected $table = 'result_qualification_classic';

    protected $fillable = [
        'id',
        'owner_id',
        'event_id',
        'gender',
        'number_set_id',
        'user_id',
        'is_other_event',
        'category_id',
        'global_category_id',
        'last_category_after_merged',
        'points',
        'user_place',
        'user_global_place',
        'last_user_place_after_merged',
        'global_points',
        'last_points_after_merged',
        'active',
        'amount_start_price',
        'is_paid',
        'is_recheck',
        'bill',
        'document',
        'products_and_discounts',
        'result_for_edit',
    ];

    protected $casts = [
        'result_for_edit' =>'json',
        'products_and_discounts' =>'json',
        'last_category_after_merged' =>'json',
        'last_user_place_after_merged' =>'json',
        'last_points_after_merged' =>'json',
        'helper_amount' =>'json',
    ];

    public static function get_amount_prizes_place($qualification_classic_events, $user_id)
    {
        $totalPrizePlaces = 0;
        foreach ($qualification_classic_events as $res){
            $res_final = ResultFinalStage::where('event_id', $res->event_id)->first();
            $res_semifinal = ResultSemiFinalStage::where('event_id', $res->event_id)->where('user_id', $user_id)->first();
            if($res_semifinal){
                if($res_semifinal->place <= 3 && $res_semifinal->place != 0 && $res_semifinal->place != null){
                    $totalPrizePlaces += 1;
                }
            }
            if(!$res_final){
                if($res->user_place <= 3){
                    $totalPrizePlaces += 1;
                }
            } else {
                if($res_final->place <= 3 && $res_final->place != 0 && $res_final->place != null){
                    $totalPrizePlaces += 1;
                }
            }
        }
        return $totalPrizePlaces;

    }


    public function owner()
    {
        return $this->belongsTo(User::class);
    }



    public static function number_sets($event_id)
    {
        return Set::where('event_id', $event_id)->pluck('number_set', 'id')->toArray();
    }
    public static function getUserPlaces($event, $type) {
        // Определяем модель для получения результатов в зависимости от флага и этапа

        if($event->is_open_main_rating){
            $column_place_q = 'global_user_place';
            $column_place_f = 'global_place';
        } else {
            $column_place_q = 'user_place';
            $column_place_f = 'place';
        }
        if ($type == 'final') {
            if ($event->is_semifinal) {
                $otherRoundResults = ResultSemiFinalStage::where('event_id', $event->id)->get(['user_id', 'place']);
                $column_place = 'place';
            } else {
                $otherRoundResults = $event->is_france_system_qualification
                    ? ResultFranceSystemQualification::where('event_id', $event->id)->where('active', 1)->get(['user_id', $column_place_f])
                    : ResultQualificationClassic::where('event_id', $event->id)->where('active', 1)->get(['user_id', $column_place_q]);
                $column_place = $event->is_france_system_qualification
                    ? $column_place_f
                    : $column_place_q;
            }
        } elseif ($type == 'semifinal') {
            $column_place = $event->is_france_system_qualification
                ? $column_place_f
                : $column_place_q;
            $otherRoundResults = $event->is_france_system_qualification
                ? ResultFranceSystemQualification::where('event_id', $event->id)->where('active', 1)->get(['user_id', $column_place_f])
                : ResultQualificationClassic::where('event_id', $event->id)->where('active', 1)->get(['user_id', $column_place_q]);
        } else {
            return null;
        }
        $formattedResults = $otherRoundResults->map(function ($item) use ($column_place) {
            return [
                'user_id' => $item->user_id,
                'place' => $item->{$column_place},
            ];
        })->toArray();
        // Возвращаем место (ранг) из другого раунда, если оно существует
        return $formattedResults ?? null;
    }

    public static function counting_final_place($event_id, $result_final){
        // Сортировка по amount_top в убывающем порядке, затем по amount_try_top в возрастающем порядке,
        // затем по amount_zone в убывающем порядке, затем по amount_try_zone в возрастающем порядке
        $event = Event::find($event_id);
        $results = Event::get_type_counting_france_system($result_final, $event->type_counting_france_system);
        $rank = 1;
        $previousRank = null;
        foreach ($results as $index => &$item) {
            if ($index > 0 && (
                    $item['amount_top'] === $results[$index - 1]['amount_top'] &&
                    $item['amount_zone'] === $results[$index - 1]['amount_zone'] &&
                    $item['amount_try_top'] === $results[$index - 1]['amount_try_top'] &&
                    $item['amount_try_zone'] === $results[$index - 1]['amount_try_zone']
                )) {
                $item['place'] = $previousRank;
            } else {
                $item['place'] = $rank;
                $previousRank = $rank;
            }
            $rank++;
        }
        return $results;
    }

    public static function set_new_places($participants, $places)
    {
        // Обновление мест по user_id
        foreach ($places as $place) {
            $userId = $place['user_id'];
            $userPlace = $place['place'];

            // Проходим по массиву участников
            foreach ($participants as $participant) {
                if ($participant['user_id'] === $userId) {
                    $participant['place'] = $userPlace; // Устанавливаем новое место
                    break; // Выходим из цикла, если нашли участника
                }
            }
        }
        return $participants;
    }

    public static function assign_dublicate_place($current_result, $previous_result)
    {
        $previous_places_map = [];
        foreach ($previous_result as $result) {
            $previous_places_map[$result['user_id']] = $result['place'];
        }

        $places_map = [];
        foreach ($current_result as $result) {
            $places_map[$result['place']][] = $result['user_id'];
        }
        $corrected_result = [];
        ksort($places_map);

        $assigned_places = []; // Массив для отслеживания уже присвоенных мест
        $current_place = 1; // Начальное место

        foreach ($places_map as $place => $user_ids) {
            if (count($user_ids) > 1) {
                $previous_places = [];
                foreach ($user_ids as $user_id) {
                    if (isset($previous_places_map[$user_id])) {
                        $previous_places[$user_id] = $previous_places_map[$user_id];
                    }
                }

                asort($previous_places);
                foreach ($previous_places as $user_id => $prev_place) {
                    while (in_array($current_place, $assigned_places)) {
                        $current_place++;
                    }
                    $corrected_result[] = ['user_id' => $user_id, 'place' => $current_place];
                    $assigned_places[] = $current_place; // Запоминаем присвоенное место
                    $current_place++; // Увеличиваем текущее место
                }
            } else {
                while (in_array($current_place, $assigned_places)) {
                    $current_place++;
                }
                $corrected_result[] = ['user_id' => $user_ids[0], 'place' => $current_place];
                $assigned_places[] = $current_place; // Запоминаем присвоенное место
                $current_place++; // Увеличиваем текущее место
            }
        }
        $unique_result = [];
        foreach ($corrected_result as $entry) {
            while (in_array($entry['place'], array_column($unique_result, 'place'))) {
                $entry['place']++;
            }
            $unique_result[] = $entry;
        }
        return $unique_result;
    }
    public static function findIndexBy($array, $element, $needle) {
        foreach ($array as $key => $item) {
            if ($item[$needle] == $element) {
                return $key;
            }
        }
        return -1; // Если не найдено
    }

    public static function generate_empty_json_result($event_id)
    {
        $result = [];
        $routes = Route::where('event_id', $event_id)->get();
        foreach ($routes as $route){
            $result[] = array('grade' => $route->grade, 'route_id' => $route->route_id, 'attempt'=> 0);
        }
        return $result;
    }

    public static function get_place_participant_in_semifinal($event_id, $user_id){
        return ResultSemiFinalStage::where('user_id','=', $user_id)->where('event_id', '=', $event_id)->first()->place;
    }
    public static function get_place_participant_in_france_system_qualification($event_id, $user_id){
        return ResultFranceSystemQualification::where('user_id','=', $user_id)->where('event_id', '=', $event_id)->first()->place;
    }

    public static function update_places_in_qualification_classic($participants)
    {
        // Присваиваем места с учетом одинаковых баллов
        $currentPlace = 1;
        foreach ($participants as $index => $result) {
            if ($index > 0 && $result->points == $participants[$index - 1]->points) {
                // Если баллы одинаковые, присваиваем одинаковое место
                $result->user_place = $currentPlace;
            } else {
                // Если баллы разные, присваиваем новое место
                $currentPlace = $index + 1;
                $result->user_place = $currentPlace;
            }
            $result->save();
        }
    }
    public static function has_bill($event, $user_id)
    {
        $has_bill = false;
        if($event->is_france_system_qualification){
            $participant = ResultFranceSystemQualification::where('event_id','=',$event->id)->where('user_id', $user_id)->first();
            if($participant){
                $has_bill = boolval($participant->bill);
            }
        } else {
            $participant = ResultQualificationClassic::where('event_id','=',$event->id)->where('user_id', $user_id)->first();
            if($participant){
                $has_bill = boolval($participant->bill);
            }
        }
        return $has_bill;
    }

    public static function update_global_places_in_qualification_classic($event_id, $participants)
    {
        foreach ($participants as $index => $participant){

            $participant_result = ResultQualificationClassic::where('event_id', '=', $event_id)->where('user_id', '=', $participant->user_id)->first();
            $participant_result->user_global_place = $index+1;
            $participant_result->save();
        }
    }
    public static function get_places_participant_in_qualification($event_id, $filter_users, $user_id, $gender, $category_id=null, $get_place_user = false, $global = false){

        if($global){
            $column_points = 'global_points';
            $event = Event::find($event_id);
            if($event->is_auto_categoies){
                $column_category_id = 'global_category_id';
            } else {
                $column_category_id = 'category_id';
            }

        } else {
            $column_points = 'points';
            $column_category_id = 'category_id';
        }
        $users_id = User::whereIn('id', $filter_users)->pluck('id');
        if($category_id){
            $all_participant_event = ResultQualificationClassic::whereIn('user_id', $users_id)->where('gender', '=', $gender)->where($column_category_id, '=', $category_id)->where('event_id', '=', $event_id)->orderBy($column_points, 'DESC')->get();
        } else {
            $all_participant_event = ResultQualificationClassic::whereIn('user_id', $users_id)->where('gender', '=', $gender)->where('event_id', '=', $event_id)->orderBy($column_points, 'DESC')->get();
        }
        $user_places = array();
        foreach ($all_participant_event as $index => $user){
            $user_places[$user->user_id] = $index+1;
        }
        if ($get_place_user){
            if(isset($user_places[$user_id])){
                return $user_places[$user_id];
            } else {
                return null;
            }
        }

        return $user_places;
    }

    public static function participant_with_result($event_id, $gender, $category_id=null)
    {

        if($category_id){
            $active_participant = ResultQualificationClassic::where('event_id', '=', $event_id)->where('gender', '=', $gender)->where('category_id', '=', $category_id)->where('active', '=', 1)->pluck('user_id')->toArray();
        } else {
            $active_participant = ResultQualificationClassic::where('event_id', '=', $event_id)->where('gender', '=', $gender)->where('active', '=', 1)->where('is_other_event', '=', 0)->pluck('user_id')->toArray();
        }
        if ($active_participant) {
            return count(User::whereIn('id', $active_participant)->get()->toArray());
        } else {
            return 1;
        }
    }
    public static function is_active_participant($event_id, $user_id)
    {
        $active_participant = ResultQualificationClassic::where('event_id', '=', $event_id)->where('user_id', '=', $user_id)->where('active', '=', 1)->first();
        if ($active_participant) {
            return true;
        } else {
            return false;
        }
    }

    public static function better_participants($event_id, $gender, $amount_better, $category_id = null){
        if($category_id){
            $participant_users_id = ResultQualificationClassic::where('event_id', '=', $event_id)->where('gender', '=', $gender)->where('category_id', '=', $category_id)->pluck('user_id')->toArray();
        } else {
            $participant_users_id = ResultQualificationClassic::where('event_id', '=', $event_id)->where('gender', '=', $gender)->pluck('user_id')->toArray();
        }
        $users_id = User::whereIn('id', $participant_users_id)->pluck('id');
        $participant_sort_id = ResultQualificationClassic::whereIn('user_id', $users_id)->where('event_id', '=', $event_id)->where('active', '=', 1)->get()->sortByDesc('points')->pluck('user_id');
        $after_slice_participant_final_sort_id = array_slice($participant_sort_id->toArray(), 0, $amount_better);
        return User::whereIn('id', $after_slice_participant_final_sort_id)->get();
    }
    public static function better_global_participants($event_id, $gender, $amount_better, $category_id = null){
        $event = Event::find($event_id);
        $column_category_id = 'global_category_id';
        if($category_id){
            $participant_users_id = ResultQualificationClassic::where('event_id', '=', $event_id)->where('gender', '=', $gender)->where($column_category_id, '=', $category_id)->pluck('user_id')->toArray();
        } else {
            $participant_users_id = ResultQualificationClassic::where('event_id', '=', $event_id)->where('gender', '=', $gender)->pluck('user_id')->toArray();
        }
        $users_id = User::whereIn('id', $participant_users_id)->pluck('id');
        $participant_sort_id = ResultQualificationClassic::whereIn('user_id', $users_id)->where('event_id', '=', $event_id)->get()->sortByDesc('global_points')->pluck('user_id');
        $after_slice_participant_final_sort_id = array_slice($participant_sort_id->toArray(), 0, $amount_better);
        return User::whereIn('id', $after_slice_participant_final_sort_id)->get();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function participant_number_set($user_id, $event_id){
        if($user_id && $event_id){
            $event = Event::find($event_id);
            if($event->is_france_system_qualification){
                $number_set_id = ResultFranceSystemQualification::where('user_id', $user_id)->where('event_id', $event_id)->first()->number_set_id ?? null;
            } else {
                $number_set_id = ResultQualificationClassic::where('user_id', $user_id)->where('event_id', $event_id)->first()->number_set_id ?? null;
            }
            return Set::find($number_set_id)->number_set;
        }
        return null;
    }

    public function event()
    {
        return $this->belongsTo(Event::class)->where('active', '=', 1);
    }

    public function category(){
        return $this->belongsTo(ParticipantCategory::class);
    }

    public function number_set(){
        return $this->belongsTo(Set::class);
    }

    public static function get_sorted_group_participant($event_id, $gender, $category_id)
    {
        $column_place = 'user_place';
        $column_points = 'points';
        $column_category_id = 'category_id';
        $users = User::query()
            ->leftJoin('result_qualification_classic', 'users.id', '=', 'result_qualification_classic.user_id')
            ->where('result_qualification_classic.event_id', '=', $event_id)
            ->where(function($query) {
                $query->where(function($subQuery) {
                    // Проверяем значение поля is_other_event в таблице
                    $subQuery->where('result_qualification_classic.is_other_event', '=', 1)
                        ->where(function($q) {
                            $q->where('result_qualification_classic.active', '=', 1)
                                ->orWhere('result_qualification_classic.active', '=', 0);
                        });
                })->orWhere(function($subQuery) {
                    // Если is_other_event не равно 1, проверяем только active = 1
                    $subQuery->where('result_qualification_classic.is_other_event', '!=', 1)
                        ->where('result_qualification_classic.active', '=', 1);
                });
            })
            ->where('result_qualification_classic.' . $column_category_id, '=', $category_id)
            ->where('result_qualification_classic.gender', '=', $gender)
            ->select(
                'users.id',
                'users.city',
                'result_qualification_classic.' . $column_place,
                'users.middlename',
                'result_qualification_classic.' . $column_points,
                'result_qualification_classic.owner_id',
                'result_qualification_classic.gender',
                'result_qualification_classic.' . $column_category_id,
                'result_qualification_classic.number_set_id',
            )
            ->get()
            ->sortBy($column_place)
            ->toArray();
        $users_need_sorted = collect($users)->toArray();
        usort($users_need_sorted, function ($a, $b) {
            // Проверяем, если значение 'user_place' пустое, перемещаем его в конец
            if (empty($a['user_place'])) {
                return 1; // $a должно быть после $b
            } elseif (empty($b['user_place'])) {
                return -1; // $a должно быть перед $b
            } else {
                return $a['user_place'] <=> $b['user_place'];
            }
        });
        return collect($users_need_sorted);
    }
    public static function get_sorted_sex_participant($event_id, $gender)
    {
        $column_place = 'user_place';
        $column_points = 'points';
        $users = User::query()
            ->leftJoin('result_qualification_classic', 'users.id', '=', 'result_qualification_classic.user_id')
            ->where('result_qualification_classic.event_id', '=', $event_id)
            ->where(function($query) {
                $query->where(function($subQuery) {
                    // Проверяем значение поля is_other_event в таблице
                    $subQuery->where('result_qualification_classic.is_other_event', '=', 1)
                        ->where(function($q) {
                            $q->where('result_qualification_classic.active', '=', 1)
                                ->orWhere('result_qualification_classic.active', '=', 0);
                        });
                })->orWhere(function($subQuery) {
                    // Если is_other_event не равно 1, проверяем только active = 1
                    $subQuery->where('result_qualification_classic.is_other_event', '!=', 1)
                        ->where('result_qualification_classic.active', '=', 1);
                });
            })
            ->where('result_qualification_classic.gender', '=', $gender)
            ->select(
                'users.id',
                'users.city',
                'result_qualification_classic.' . $column_place,
                'users.middlename',
                'result_qualification_classic.' . $column_points,
                'result_qualification_classic.owner_id',
                'result_qualification_classic.gender',
                'result_qualification_classic.number_set_id',
            )
            ->get()
            ->sortBy($column_place)
            ->toArray();
        $users_need_sorted = collect($users)->toArray();
        usort($users_need_sorted, function ($a, $b) {
            // Проверяем, если значение 'user_place' пустое, перемещаем его в конец
            if (empty($a['user_place'])) {
                return 1; // $a должно быть после $b
            } elseif (empty($b['user_place'])) {
                return -1; // $a должно быть перед $b
            } else {
                return $a['user_place'] <=> $b['user_place'];
            }
        });
        return collect($users_need_sorted);
    }
    public static function get_global_sorted_sex_participant($event_id, $gender)
    {
        $column_place = 'global_user_place';
        $column_points = 'global_points';
        $users = User::query()
            ->leftJoin('result_qualification_classic', 'users.id', '=', 'result_qualification_classic.user_id')
            ->where('result_qualification_classic.event_id', '=', $event_id)
            ->where(function($query) {
                $query->where(function($subQuery) {
                    // Проверяем значение поля is_other_event в таблице
                    $subQuery->where('result_qualification_classic.is_other_event', '=', 1)
                        ->where(function($q) {
                            $q->where('result_qualification_classic.active', '=', 1)
                                ->orWhere('result_qualification_classic.active', '=', 0);
                        });
                })->orWhere(function($subQuery) {
                    // Если is_other_event не равно 1, проверяем только active = 1
                    $subQuery->where('result_qualification_classic.is_other_event', '!=', 1)
                        ->where('result_qualification_classic.active', '=', 1);
                });
            })
            ->where('result_qualification_classic.gender', '=', $gender)
            ->select(
                'users.id',
                'users.city',
                'result_qualification_classic.' . $column_place,
                'users.middlename',
                'result_qualification_classic.' . $column_points,
                'result_qualification_classic.owner_id',
                'result_qualification_classic.gender',
                'result_qualification_classic.number_set_id',
            )
            ->get()
            ->sortBy($column_place)
            ->toArray();
        $users_need_sorted = collect($users)->toArray();
        usort($users_need_sorted, function ($a, $b) {
            // Проверяем, если значение 'user_place' пустое, перемещаем его в конец
            if (empty($a['global_user_place'])) {
                return 1; // $a должно быть после $b
            } elseif (empty($b['global_user_place'])) {
                return -1; // $a должно быть перед $b
            } else {
                return $a['global_user_place'] <=> $b['global_user_place'];
            }
        });
        return collect($users_need_sorted);
    }
    public static function get_global_sorted_group_participant($event_id, $gender, $category_id)
    {
        $event = Event::find($event_id);
        $column_place = 'user_global_place';
        $column_points = 'global_points';
        $column_category_id = 'global_category_id';
        $users = User::query()
            ->leftJoin('result_qualification_classic', 'users.id', '=', 'result_qualification_classic.user_id')
            ->where('result_qualification_classic.event_id', '=', $event_id)
//            ->where('result_qualification_classic.active', '=', 1)
            ->where('result_qualification_classic.'.$column_category_id, '=', $category_id)
            ->select(
                'users.id',
                'users.city',
                'result_qualification_classic.'.$column_place,
                'users.middlename',
                'result_qualification_classic.'.$column_points,
                'result_qualification_classic.owner_id',
                'result_qualification_classic.gender',
                'result_qualification_classic.'.$column_category_id,
                'result_qualification_classic.number_set_id',
            )
            ->where('result_qualification_classic.gender', '=', $gender)->get()->sortBy($column_place)->toArray();
        $users_need_sorted = collect($users)->toArray();
        usort($users_need_sorted, function ($a, $b) {
            // Проверяем, если значение 'user_place' пустое, перемещаем его в конец
            if (empty($a['user_global_place'])) {
                return 1; // $a должно быть после $b
            } elseif (empty($b['user_global_place'])) {
                return -1; // $a должно быть перед $b
            } else {
                return $a['user_global_place'] <=> $b['user_global_place'];
            }
        });
        return collect($users_need_sorted);
    }
    public static function sorted_team_points($result_team)
    {
        asort($result_team);
        $count = count($result_team);
        foreach ($result_team as $index => $team){
            $result_team[$index] = array('place' => $count, 'team' => $index, 'points' => $result_team[$index]);
            $count--;
        }
        usort($result_team, function ($a, $b) {
            // Проверяем, если значение 'user_place' пустое, перемещаем его в конец
            if (empty($a['place'])) {
                return 1; // $a должно быть после $b
            } elseif (empty($b['place'])) {
                return -1; // $a должно быть перед $b
            } else {
                return $a['place'] <=> $b['place'];
            }
        });
        return $result_team;
    }
    public static function get_list_team_and_points_participant($event_id, $team)
    {
        $column_place = 'user_place';
        $column_points = 'points';
        $teams = 0;
        $users = User::query()
            ->where('users.team', '=', $team)
            ->leftJoin('result_qualification_classic', 'users.id', '=', 'result_qualification_classic.user_id')
            ->where('result_qualification_classic.event_id', '=', $event_id)
            ->where('result_qualification_classic.active', '=', 1)
            ->select(
                'users.id',
                'result_qualification_classic.'.$column_place,
                'users.team',
                'result_qualification_classic.'.$column_points,
            )
            ->get()->sortBy($column_place)->toArray();
        foreach ($users as $user){
            $teams += $user[$column_points];
        }
        return $teams;
    }

    public static function get_global_list_team_and_points_participant($event_id, $team)
    {
        $column_place = 'user_global_place';
        $column_points = 'global_points';
        $teams = 0;
        $users = User::query()
            ->where('users.team', '=', $team)
            ->leftJoin('result_qualification_classic', 'users.id', '=', 'result_qualification_classic.user_id')
            ->where('result_qualification_classic.event_id', $event_id)
            ->where(function ($query) {
                $query->where('result_qualification_classic.active', '=', 1)
                    ->orWhere(function ($query) {
                        $query->where('result_qualification_classic.active', '=', 0)
                            ->where('result_qualification_classic.is_other_event', '=', 1);
                    });
            })
            ->select(
                'users.id',
                'result_qualification_classic.' . $column_place,
                'users.team',
                'result_qualification_classic.' . $column_points
            )
            ->get()
            ->sortBy($column_place)
            ->toArray();
        foreach ($users as $user){
            $teams += $user[$column_points];
        }
        return $teams;
    }
    public static function get_list_passed_route($event_id, $user_id)
    {
        return ResultRouteQualificationClassic::where('event_id', $event_id)->where('user_id', $user_id)->whereIn('attempt', [1,2,3])->pluck('grade');
    }

    public static function get_global_list_passed_route($event_id, $user_id)
    {
        return ResultRouteQualificationClassic::whereIn('event_id', $event_id)->where('user_id', $user_id)->whereIn('attempt', [1,2,3])->pluck('grade');
    }
    public static function find_grade($list_grades, $grade)
    {
        return in_array($grade, $list_grades);
    }

    public static function findMaxKey($array) {
        $maxValue = null;
        $maxKey = null;

        foreach ($array as $key => $value) {
            if ($maxValue === null || $value > $maxValue) {
                $maxValue = $value;
                $maxKey = $key;
            }
        }

        return $maxKey;
    }

    public static function get_category_from_result($event, $the_best_route, $user_id)
    {
        $participant_categories = ParticipantCategory::where('event_id', $event->id)->get()->pluck('category')->toArray();
        $new_convert_options = self::convert_categories($event->options_categories, $participant_categories);

        $next = [];
        foreach ($new_convert_options as $opt){
            $next[] = self::values_in_range(Grades::grades(), $opt['from'], $opt['to']);
        }

        $result = array();
        # Бежим по лучшим трассам (6C, 7A, 7A+)
        foreach ($the_best_route as $route){
            foreach ($new_convert_options as $index => $opt){
                 if(self::find_grade($next[$index], $route)){
                     if (array_key_exists($opt['category'], $result)) {
                         $result[$opt['category']]++;
                     } else {
                         $result[$opt['category']] = 1;
                     }
                 }
            }
        }
         # Это условие нужно для того чтобы подсветить результат который возможно требует внимания
        if(count($result) > 1){
            $res = ResultQualificationClassic::where('user_id', $user_id)->where('event_id', $event->id)->first();
            if($res){
                $res->is_recheck = 1;
                $res->save();
            } else {
                Log::error('Не найден результат в автопереносе при сомнительном результате');
            }

        }
        return self::findMaxKey($result);
    }

    public static function values_in_range($array, $start, $end)
    {
        $startPrinting = false;
        $valuesInRange = [];

        foreach ($array as $value) {
            if ($value == $start) {
                $startPrinting = true;
            }

            if ($startPrinting) {
                $valuesInRange[] = $value;
            }

            if ($value == $end) {
                break;
            }
        }

        if (!in_array($end, $valuesInRange)) {
            // If end value not found in the array, add it to the end
            $valuesInRange[] = $end;
        }

        return $valuesInRange;
    }

    public static function convert_categories($array, $categories)
    {
        $res = [];
        foreach ($array as $el){
            $res[] = array(
                'category' => $categories[$el['Категория участника']],
                'to' => $el['До какой категории сложности определять эту категорию'],
                'from' => $el['От какой категории сложности определять эту категорию']);
        }
        return $res;
    }

    public static function send_main_about_take_part($event, $user, $participant)
    {
        if (Helpers::valid_email($user->email)) {
            $details = array();
            if($event->is_input_set != 1){
                $set = Set::find($participant->number_set_id);
                $dates = Helpers::getDatesByDayOfWeek($event->start_date, $event->end_date);
                $set_date = $dates[$set->day_of_week] ?? '';
                $details['number_set'] = $set->number_set;
                $details['set_date'] = $set_date;
                $details['set_time'] = $set->time;
                $details['set_day_of_week'] = $set->day_of_week;
            }
            $details['title'] = $event->title;
            $details['event_start_date'] = $event->start_date;
            $details['event_url'] = env('APP_URL').$event->new_link ?? $event->link;
            if($event->is_need_pay_for_reg){
                $details['is_need_pay_for_reg'] = true;
                $details['link_payment'] = $event->link_payment;
                if($event->registration_time_expired){
                    $details['pay_time_expired'] = $event->registration_time_expired;
                }
                $details['img_payment'] = $event->img_payment;
                $details['info_payment'] = $event->info_payment;
            } else {
                $details['info_payment'] = $event->info_payment;
            }
            $details['image'] = $event->image;
            if(env('APP_ENV') == 'prod'){
                Mail::to($user->email)->queue(new \App\Mail\TakePart($details));
            }
        }

    }

    public static function send_main_about_group_take_part($event, $user_to_send, $created_users)
    {
        if (Helpers::valid_email($user_to_send->email)) {
            $details = array();
            $details['title'] = $event->title;
            $details['event_start_date'] = $event->start_date;
            $details['event_url'] = env('APP_URL').$event->new_link ?? $event->link;
            if($event->is_need_pay_for_reg){
                $details['is_need_pay_for_reg'] = true;
                $details['link_payment'] = $event->link_payment;
                if($event->registration_time_expired){
                    $details['pay_time_expired'] = $event->registration_time_expired;
                }
                $details['img_payment'] = $event->img_payment;
                $details['info_payment'] = $event->info_payment;
            } else {
                $details['info_payment'] = $event->info_payment;
            }
            $details['image'] = $event->image;
            if(env('APP_ENV') == 'prod'){
                Mail::to($user_to_send->email)->queue(new \App\Mail\GroupTakePart($details, $created_users));
            }
        }

    }
    public static function send_main_about_list_pending($event, $user, $job)
    {
        if (Helpers::valid_email($user->email)) {
            $details = array();
            $details['title'] = $event->title;
            $details['number_sets'] = $job->number_sets;
            $details['event_start_date'] = $event->start_date;
            $details['event_url'] = env('APP_URL').$event->new_link ?? $event->link;
            if(env('APP_ENV') == 'prod'){
                Mail::to($user->email)->queue(new \App\Mail\ListPending($details));
            }

        }

    }

    public static function send_confirm_bill($event, $user)
    {
        if (Helpers::valid_email($user->email)) {
            $details = array();
            $details['title'] = $event->title;
            $details['event_start_date'] = $event->start_date;
            $details['event_url'] = env('APP_URL').$event->new_link ?? $event->link;
//            $details['image'] = $event->image;
            if(env('APP_ENV') == 'prod'){
                Mail::to($user->email)->queue(new \App\Mail\ConfirmBill($details));
            }
        }

    }

    public static function send_message_from_climbing_gym($subject, $message, $user, $climbing_gym_name)
    {
        if (Helpers::valid_email($user['email'])) {
            $details = array();
            $details['subject'] = $subject;
            $details['message'] = $message;
            $details['middlename'] = $user['middlename'];
            $details['climbing_gym_name'] = $climbing_gym_name;
            if(env('APP_ENV') == 'prod'){
                Mail::to($user['email'])->queue(new \App\Mail\MessageParticipants($details));
            }

        }

    }

    public static function calculate_stability_coefficients($user_id) {

        // Получение событий квалификации для выбранного пользователя
        $qualification_classic_events = ResultQualificationClassic::where('user_id', $user_id)
            ->where('is_other_event', 0)
            ->where('active', 1)
            ->latest()
            ->take(30)
            ->pluck('event_id');

        // Инициализация массивов для хранения результатов всех участников
        $scoresByParticipant = [];

        // Подсчет баллов для всех участников в тех же событиях
        foreach ($qualification_classic_events as $event_id) {
            $results = ResultRouteQualificationClassic::where('event_id', $event_id)
                ->get();

            foreach ($results as $result) {
                $participantId = $result->user_id;
                $attempt = $result->attempt;

                if (!isset($scoresByParticipant[$participantId])) {
                    $scoresByParticipant[$participantId] = ['flash' => 0, 'redpoint' => 0, 'count' => 0];
                }

                if ($attempt == 1) {
                    $scoresByParticipant[$participantId]['flash'] += 1.2;
                } else if ($attempt >= 2) {
                    $scoresByParticipant[$participantId]['redpoint'] += 1;
                }
                $scoresByParticipant[$participantId]['count'] += 1;
            }
        }

        // Рассчитываем коэффициенты стабильности для всех участников
        $allScores = [];
        foreach ($scoresByParticipant as $participantId => $data) {
            if ($data['count'] > 0) {
                $sum = $data['flash'] + $data['redpoint'];
                $averageScore = $sum / $data['count'];
                $allScores[$participantId] = $averageScore;
            }
        }

        // Рассчитываем среднее значение и стандартное отклонение для всех участников
        if (count($allScores) > 0) {
            $overallAverage = array_sum($allScores) / count($allScores);

            $variance = 0.0;
            foreach ($allScores as $score) {
                $variance += pow($score - $overallAverage, 2);
            }
            $variance /= count($allScores);
            $standardDeviation = sqrt($variance);

            // Рассчитываем коэффициент стабильности для выбранного участника
            if (isset($scoresByParticipant[$user_id]) && $scoresByParticipant[$user_id]['count'] > 0) {
                $userSum = $scoresByParticipant[$user_id]['flash'] + $scoresByParticipant[$user_id]['redpoint'];
                $userAverageScore = $userSum / $scoresByParticipant[$user_id]['count'];
                $userStabilityCoefficient = ($standardDeviation == 0) ? 0 : $userAverageScore / $standardDeviation;
            } else {
                $userStabilityCoefficient = 0;
            }
        } else {
            $userStabilityCoefficient = 0;
        }
        return $userStabilityCoefficient;

    }
    public static function get_analytics_for_user_data_all($user_id)
    {
        $totalSemifinal = ResultSemiFinalStage::where('user_id', $user_id)->get()->count();
        $totalFinal = ResultFinalStage::where('user_id', $user_id)->get()->count();
        $averageStability = self::calculate_stability_coefficients($user_id);
        $qualification_classic_events = ResultQualificationClassic::where('user_id', $user_id)
            ->where('is_other_event', 0)
            ->where('active', 1)
            ->latest()
            ->take(30)
            ->get();
        if(count($qualification_classic_events) > 0){
            $totalPrizePlaces = self::get_amount_prizes_place($qualification_classic_events, $user_id);
        }
        return [
            'semifinal_rate' => $totalSemifinal ?? 0,
            'final_rate' => $totalFinal ?? 0,
            'averageStability' => $averageStability ?? 0,
            'totalPrizePlaces' => $totalPrizePlaces ?? 0,
        ];
    }
    public static function get_analytics_for_user_data_progress($user_id)
    {
        $qualification_classic_events = ResultQualificationClassic::where('user_id', $user_id)
            ->where('is_other_event', 0)
            ->where('active', 1)
            ->latest()
            ->take(30)
            ->get();

        // Массивы для хранения данных
        $labels = [];
        $flashesData = [];
        $redpointsData = [];

        // Извлекаем данные для каждого события
        foreach ($qualification_classic_events as $res) {
            $event = Event::find($res->event_id);
            if($event){
                if(count($qualification_classic_events) < 4){
                    $label = $event->title;
                } else {
                    $label = strlen($event->title) > 10 ? substr($event->title, 0, 12) . '...' : $event->title;
                }

                $labels[] = $label;

                // Подсчитываем количество флешей и редпоинтов для этого события
                $flashes = ResultRouteQualificationClassic::where('event_id', $event->id)
                    ->where('user_id', $user_id)
                    ->where('attempt', 1)
                    ->count();
                $redpoints = ResultRouteQualificationClassic::where('event_id', $event->id)
                    ->where('user_id', $user_id)
                    ->where('attempt', 2)
                    ->count();

                $flashesData[] = $flashes;
                $redpointsData[] = $redpoints;
            }
        }

    // Подготовка данных для передачи в JavaScript
        return [
            'labels' => $labels,
            'flashes' => $flashesData,
            'redpoints' => $redpointsData
        ];
    }
}
