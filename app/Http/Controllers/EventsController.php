<?php

namespace App\Http\Controllers;

use App\Helpers\Helpers;
use App\Http\Requests\StoreRequest;
use App\Models\Event;
use App\Models\Grades;
use App\Models\ListOfPendingParticipant;
use App\Models\ResultQualificationClassic;
use App\Models\ParticipantCategory;
use App\Models\ResultFinalStage;
use App\Models\ResultRouteQualificationClassic;
use App\Models\ResultFranceSystemQualification;
use App\Models\ResultSemiFinalStage;
use App\Models\Route;
use App\Models\Set;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use stdClass;

class EventsController extends Controller
{
    /**
     * @throws \Exception
     */
    public function show(Request $request, $start_date, $climbing_gym, $title){
        $event_public_exist = Event::where('start_date', $start_date)->where('title_eng', '=', $title)->where('climbing_gym_name_eng', '=', $climbing_gym)->where('is_public', 1)->first();
        $event_exist = Event::where('start_date', $start_date)->where('title_eng', '=', $title)->where('climbing_gym_name_eng', '=', $climbing_gym)->first();
        $pre_show = false;
        $user_id = Auth()->user()->id ?? null;
        if($event_public_exist){
            $event = $event_public_exist;
        } else {
            if($request->is('admin/event/*')){
                $pre_show = true;
                $event = $event_exist;
            }
        }
        $is_show_button_list_pending = false;
        if($event_public_exist || $pre_show){
            $sets = Set::where('owner_id', '=', $event->owner_id)->orderBy('number_set')->get();
            foreach ($sets as $set){
                if($event->is_france_system_qualification){
                    $participants_event = ResultFranceSystemQualification::where('event_id','=',$event->id)->where('owner_id','=',$event->owner_id)->where('number_set_id', '=', $set->id)->count();
//                    $participant = ResultFranceSystemQualification::where('event_id','=',$event->id)->where('user_id','=',$user_id)->first();
                } else {
                    $participants_event = ResultQualificationClassic::where('event_id','=',$event->id)->where('owner_id','=',$event->owner_id)->where('number_set_id', '=', $set->id)->count();
//                    $participant = ResultQualificationClassic::where('event_id','=',$event->id)->where('user_id','=',$user_id)->first();
                }
                $set->free = $set->max_participants - $participants_event;
                if($set->free <= 0){
                    $is_show_button_list_pending = true;
                }
                $a = $set->max_participants;
                $b = $set->free;

                if ($a === $b) {
                    $percent = 0;
                } elseif ($a < $b) {
                    $diff = $b - $a;
                    if($b != 0){
                        $percent = $diff / $b * 100;
                    } else {
                        $percent = 0;
                    }
                } else {
                    $diff = $a - $b;
                    if( $a != 0){
                        $percent = $diff / $a * 100;
                    } else {
                        $percent = 0;
                    }
                }
                $set->procent = intval($percent);
                $set->date = Helpers::getDatesByDayOfWeek($event_exist->start_date, $event_exist->end_date);
            }

            $is_show_button_final = boolval(ResultFinalStage::where('event_id', $event->id)->first());

            $is_add_to_list_pending = boolval(ListOfPendingParticipant::where('event_id', $event->id)->where('user_id', $user_id)->first());
            $list_pending = ListOfPendingParticipant::where('event_id', $event->id)->where('user_id', $user_id)->first();
            $is_show_button_semifinal = boolval(ResultSemiFinalStage::where('event_id', $event->id)->first());
            $sport_categories = User::sport_categories;
            return view('welcome', compact(['event','is_show_button_list_pending','list_pending','is_add_to_list_pending', 'sport_categories', 'sets', 'is_show_button_final',  'is_show_button_semifinal']));
        } else {
            return view('404');
        }
    }

    public function event_info_payment(Request $request, $start_date, $climbing_gym, $event_id)
    {
        $event = Event::find($event_id);
        return view('event.tab.payment_without_bill', compact('event'));
    }
    public function event_info_payment_bill(Request $request, $start_date, $climbing_gym, $event_id)
    {
        $event = Event::find($event_id);
        return view('event.tab.payment', compact('event'));
    }

    public function get_participants(Request $request, $start_date, $climbing_gym, $title){
        $event = Event::where('start_date', $start_date)->where('title_eng', '=', $title)->where('climbing_gym_name_eng', '=', $climbing_gym)->where('is_public', 1)->first();
        if($event) {
            if($event->is_france_system_qualification){
                $table = 'result_france_system_qualification';
            } else {
                $table = 'result_qualification_classic';
            }
            $participants = User::query()
                ->leftJoin($table, 'users.id', '=', $table.'.user_id')
                ->where($table.'.event_id', '=', $event->id)
                ->where($table.'.is_other_event', '=', 0)
                ->select(
                    'users.id',
                    'users.middlename',
                    'users.city',
                    'users.team',
                    $table.'.gender',
                    $table.'.number_set_id',
                    $table.'.category_id',
                )->get()->toArray();
            if($event->is_input_set != 1){
                $days = Set::where('owner_id', '=', $event->owner_id)->select('day_of_week')->distinct()->get();
                $sets = Set::where('owner_id', '=', $event->owner_id)->get();
                $number_sets = Set::where('owner_id', '=', $event->owner_id)->pluck('id');
                foreach ($number_sets as $index => $set) {
                    if($event->is_france_system_qualification){
                        $sets[$index]->count_participant = ResultFranceSystemQualification::where('event_id', '=', $event->id)->where('number_set_id', $set)->count();
                    } else {
                        $sets[$index]->count_participant = ResultQualificationClassic::where('event_id', '=', $event->id)->where('is_other_event', 0)->where('number_set_id', $set)->count();
                    }
                    $sets[$index]->date = Helpers::getDatesByDayOfWeek($event->start_date, $event->end_date);
                    if($sets[$index]->count_participant == 0){
                        unset($sets[$index]);
                    }
                }
            } else {
                $days = null;
                $sets = null;
            }
            $index = 0;
            $categories = ParticipantCategory::where('event_id', $event->id)->pluck('category', 'id')->toArray();

            foreach ($participants as $index_user => $user) {
                if ($index <= count($participants)) {
                    if($event->is_input_set == 1){
                        $participants[$index_user]['category'] = $categories[$participants[$index]['category_id']];
                    } else {
                        $set = $sets->where('id', '=', $user['number_set_id'])->where('owner_id', '=', $event->owner_id)->first();
                        $category = $categories[$participants[$index]['category_id']] ?? 'Нет группы';
                        $participants[$index_user]['category'] = $category;
                        $participants[$index_user]['number_set'] = $set->number_set;
                        $participants[$index_user]['time'] = $set->time . ' ' . trans_choice('somewords.' . $set->day_of_week, 10);
                    }
                }
                $index++;
            }
        } else {
            return view('404');
        }
//        dd($days, $participants, $sets);
        return view('event.participants', compact(['days', 'event', 'participants', 'sets']));
    }

    public function get_qualification_classic_results(Request $request, $start_date, $climbing_gym, $title)
    {
        $event = Event::where('start_date', $start_date)->where('title_eng', '=', $title)->where('climbing_gym_name_eng', '=', $climbing_gym)->where('is_public', 1)->first();
//        $final_results = Participant::where('event_id', '=', $event->id)->where('active', '=', 1)->orderBy('points', 'DESC')->get()->toArray();
        if($event){
            if(!$event->is_france_system_qualification){
//                $final_results = Participant::where('event_id', '=', $event->id)->where('active', '=', 1)->orderBy('points', 'DESC')->get()->toArray();
                $user_male_ids = ResultQualificationClassic::where('event_id', '=', $event->id)->where('gender', '=', 'male')->where('active','=', 1)->pluck('user_id')->toArray();
                $user_female_ids = ResultQualificationClassic::where('event_id', '=', $event->id)->where('gender', '=', 'female')->where('active','=', 1)->pluck('user_id')->toArray();
                $stats = new stdClass();
                $female_categories = array();
                $male_categories = array();
                $stats->male = User::whereIn('id', $user_male_ids)->get()->count();
                $stats->female = User::whereIn('id', $user_female_ids)->get()->count();
                $result_male = array();
                $result_female = array();
                $categories = ParticipantCategory::where('event_id', $event->id)->get();
                foreach ($categories as $category) {
//                    $result_male_cache = Cache::remember('result_male_cache_'.$category->category, 60 * 60, function () use ($event, $category) {
//                        return ResultQualificationClassic::get_sorted_group_participant($event->id, 'male', $category->id)->toArray();
//                    });
//                    $result_female_cache = Cache::remember('result_female_cache_'.$category->category, 60 * 60, function () use ($event, $category) {
//                        return ResultQualificationClassic::get_sorted_group_participant($event->id, 'female', $category->id)->toArray();
//                    });
                    $result_male_cache = ResultQualificationClassic::get_sorted_group_participant($event->id, 'male', $category->id)->toArray();
                    $result_female_cache =  ResultQualificationClassic::get_sorted_group_participant($event->id, 'female', $category->id)->toArray();
                    $result_male[] = $result_male_cache;
                    $result_female[] = $result_female_cache;
//                    $result_male[] = Participant::get_sorted_group_participant($event->id, 'male', $category->id)->toArray();
//                    $result_female[] = Participant::get_sorted_group_participant($event->id, 'female', $category->id)->toArray();
                    $user_female = User::whereIn('id', $user_female_ids)->pluck('id');
                    $user_male = User::whereIn('id', $user_male_ids)->pluck('id');
                    $female_categories[$category->id] = ResultQualificationClassic::whereIn('user_id', $user_female)->where('event_id', '=', $event->id)->where('category_id', '=', $category->id)->get()->count();
                    $male_categories[$category->id] = ResultQualificationClassic::whereIn('user_id', $user_male)->where('event_id', '=', $event->id)->where('category_id', '=', $category->id)->get()->count();
                }
                $result_male_final = Helpers::arrayValuesRecursive($result_male);
                $result_female_final = Helpers::arrayValuesRecursive($result_female);
                $result = array_merge($result_male_final, $result_female_final);
                $stats->female_categories = $female_categories;
                $stats->male_categories = $male_categories;
                $categories = $categories->toArray();
//                dd($result);
            }
        } else {
            return view('404');
        }
        return view('event.qualification_classic_results', compact(['event', 'result',  'categories', 'stats']));
    }


    public function get_qualification_france_system_results(Request $request, $start_date, $climbing_gym, $title)
    {
        $event = Event::where('start_date', $start_date)->where('title_eng', '=', $title)->where('climbing_gym_name_eng', '=', $climbing_gym)->where('is_public', 1)->first();
        if($event){
            $categories = ParticipantCategory::where('event_id', $event->id)->get()->toArray();
            $routes_amount = Grades::where('event_id', $event->id)->first()->count_routes;
            $routes = [];
            for($i = 1; $i <= $routes_amount; $i++){
                $routes[] = $i;
            }
            if($event->is_france_system_qualification){
                $result_each_routes = array();
                foreach ($event->categories as $category) {
                    $category = ParticipantCategory::where('category', $category)->where('event_id', $event->id)->first();
                    $users_female2 = Event::get_france_system_result('result_france_system_qualification', $event->id, 'female', $category)->toArray();
                    $users_male2 = Event::get_france_system_result('result_france_system_qualification', $event->id, 'male', $category)->toArray();
                    $result_each_routes['male'][$category->id] = $users_female2;
                    $result_each_routes['female'][$category->id] = $users_male2;
                }
            }
        } else {
            return view('404');
        }
        return view('event.france_system_qualification_results', compact(['event', 'categories', 'result_each_routes', 'routes']));
    }

    public function get_semifinal_france_system_results(Request $request, $start_date, $climbing_gym, $title)
    {
        $event = Event::where('start_date', $start_date)->where('title_eng', '=', $title)->where('climbing_gym_name_eng', '=', $climbing_gym)->where('is_public', 1)->first();
        if($event){
            $categories = ParticipantCategory::where('event_id', $event->id)->get()->toArray();
            $routes = array();
            for ($route = 1; $route <= $event->amount_routes_in_semifinal; $route++) {
                $routes[] = $route;
            }
            $result_each_routes = array();
            if($event->is_sort_group_semifinal){
                foreach ($event->categories as $category) {
                    $category = ParticipantCategory::where('category', $category)->where('event_id', $event->id)->first();
                    $users_female2 = Event::get_france_system_result('result_semifinal_stage', $event->id, 'female', $category)->toArray();
                    $users_male2 = Event::get_france_system_result('result_semifinal_stage', $event->id, 'male', $category)->toArray();
                    $result_each_routes['male'][$category->id] = $users_male2;
                    $result_each_routes['female'][$category->id] = $users_female2;
                }
            } else {
                $users_female2 = Event::get_france_system_result('result_semifinal_stage', $event->id, 'female')->toArray();
                $users_male2 = Event::get_france_system_result('result_semifinal_stage', $event->id, 'male')->toArray();
                $result_each_routes['male'] = $users_male2;
                $result_each_routes['female'] = $users_female2;
            }
        } else {
            return view('404');
        }
        return view('event.france_system_semifinal_results', compact(['event', 'categories', 'result_each_routes', 'routes']));
    }

    public function get_final_france_system_results(Request $request, $start_date, $climbing_gym, $title)
    {
        $event = Event::where('start_date', $start_date)->where('title_eng', '=', $title)->where('climbing_gym_name_eng', '=', $climbing_gym)->where('is_public', 1)->first();
        if($event){
            $categories = ParticipantCategory::where('event_id', $event->id)->get()->toArray();
            $routes = array();
            for ($route = 1; $route <= $event->amount_routes_in_final; $route++) {
                $routes[] = $route;
            }
            $result_each_routes = array();
            if($event->is_sort_group_final) {
                foreach ($event->categories as $category) {
                    $category = ParticipantCategory::where('category', $category)->where('event_id', $event->id)->first();
                    $users_female2 = Event::get_france_system_result('result_final_stage', $event->id, 'female', $category)->toArray();
                    $users_male2 = Event::get_france_system_result('result_final_stage', $event->id, 'male', $category)->toArray();
                    $result_each_routes['male'][$category->id] = $users_male2;
                    $result_each_routes['female'][$category->id] = $users_female2;
                }
            } else {
                $users_female2 = Event::get_france_system_result('result_final_stage', $event->id, 'female')->toArray();
                $users_male2 = Event::get_france_system_result('result_final_stage', $event->id, 'male')->toArray();
                $result_each_routes['male'] = $users_male2;
                $result_each_routes['female'] = $users_female2;
            }
        } else {
            return view('404');
        }
//        dd($result_each_routes, $routes);
        return view('event.france_system_final_results', compact(['event', 'categories', 'result_each_routes', 'routes']));
    }


    public function store(StoreRequest $request) {
        $event = Event::where('id', '=', $request->event_id)->where('is_public', 1)->first();
        $user = User::find($request->user_id);
        if(!$event || !$event->is_registration_state || str_contains($user->email, 'telegram')){
            return response()->json(['success' => false, 'message' => 'ошибка регистрации'], 422);
        }
        $participant_categories = ParticipantCategory::where('event_id', '=', $request->event_id)->where('category', '=', $request->category)->first();
        if($event->is_france_system_qualification){
            $participant = ResultFranceSystemQualification::where('user_id',  $request->user_id)->where('event_id', $request->event_id)->first();
            if($participant){
                return response()->json(['success' => false, 'message' => 'ошибка регистрации'], 422);
            }
            $participant = new ResultFranceSystemQualification;
        } else {
            $participant = ResultQualificationClassic::where('user_id',  $request->user_id)->where('event_id', $request->event_id)->first();
            if($participant){
                return response()->json(['success' => false, 'message' => 'ошибка регистрации'], 422);
            }
            $participant = new ResultQualificationClassic;
        }

        if($event->is_input_set != 1){
            $number_set = $request->number_set;
            $set = Set::where('number_set', $number_set)->where('owner_id', $event->owner_id)->first();
            $participant->number_set_id = $set->id;
        }
        if($event->is_auto_categories){
            $participant->category_id = 0;
        } else {
            $participant->category_id = $participant_categories->id;
        }

        $participant->event_id = $request->event_id;
        if($request->gender){
            $participant->gender = $request->gender;
        } else {
            $participant->gender = $user->gender;
        }
        $participant->user_id = $request->user_id;
        $participant->owner_id = $event->owner_id;
        $participant->active = 0;
        $participant->save();
        if($user){
            if($request->gender){
                $user->gender = $request->gender;
            }
            if($request->sport_category){
                $user->sport_category = $request->sport_category;
            }
            if($request->birthday){
                $user->birthday = $request->birthday;
            }
            $user->save();
        }

        if ($participant->save()) {
            if($user && $event && $participant){
                ResultQualificationClassic::send_main_about_take_part($event, $user, $participant);
            }
            return response()->json(['success' => true, 'message' => 'Успешная регистрация'], 201);
        } else {
            return response()->json(['success' => false, 'message' => 'ошибка регистрации'], 422);
        }
    }

    public function changeSet(Request $request) {
        $event = Event::where('id', '=', $request->event_id)->where('is_public', 1)->first();
        if(!$event || !$event->is_registration_state){
            return response()->json(['success' => false, 'message' => 'ошибка регистрации'], 422);
        }
        $set = Set::where('owner_id',$event->owner_id)->where('number_set', $request->number_set)->first();
        if($event->is_france_system_qualification){
            $participant = ResultFranceSystemQualification::where('user_id',  $request->user_id)->where('event_id', $request->event_id)->first();
            $participants_event = ResultFranceSystemQualification::where('event_id','=',$event->id)->where('owner_id','=',$event->owner_id)->where('number_set_id', '=', $set->id)->count();
        } else {
            $participant = ResultQualificationClassic::where('user_id',  $request->user_id)->where('event_id', $request->event_id)->first();
            $participants_event = ResultQualificationClassic::where('event_id','=', $event->id)->where('owner_id','=',$event->owner_id)->where('number_set_id', '=', $set->id)->count();
        }
        $free = $set->max_participants - $participants_event;
        if($free <= 0){
            return response()->json(['success' => false, 'message' => 'В выбранном сете нет мест'], 422);
        }
        $event = Event::find($request->event_id);
        $number_set = $request->number_set;
        $set = Set::where('number_set', $number_set)->where('owner_id', $event->owner_id)->first();
        $participant->number_set_id = $set->id;
        $participant->save();
        if ($participant->save()) {
            return response()->json(['success' => true, 'message' => 'Успешно сохранено']);
        } else {
            return response()->json(['success' => false, 'message' => 'ошибка регистрации'], 422);
        }
    }

    public function sendResultParticipant(Request $request) {

        $event = Event::where('id', '=', $request->event_id)->where('is_public', 1)->first();
        if(!$event || !$event->is_send_result_state){
            return response()->json(['success' => false, 'message' => 'Регистрация была закрыта'], 422);
        }
        $user_id = $request->user_id;
        # Если не дан доступ из админки к редактированию то запрещать повторную отправку
        if(!$event->is_access_user_edit_result){
            $participant_active = ResultQualificationClassic::where('user_id', '=', $user_id)->where('event_id', '=', $request->event_id)->first();
            if (!$participant_active){
                return response()->json(['success' => false, 'message' => 'Результаты уже были добавлены или отсутствует регистрация'], 422);
            }
        }
        $count_routes = Grades::where('event_id', $request->event_id)->first();
        if (!$count_routes){
            return response()->json(['success' => false, 'message' => 'По данном соревнование не найдены трассы'], 422);
        }
        # Проверяем что есть результат был отмечен, умножение происходит на 2 потому что из 3 результатов failed passed и flash два из них false
        # Не должно быть меньше этого, то есть если не отмечена хотя бы одна трасса она будет больше чем $count_routes * 2
        $amount_false = Event::validate_result($request->result);
        if($amount_false > $count_routes->count_routes * 2){
            return response()->json(['success' => false, 'message' => 'Необходимо отметить все трассы'], 422);
        }
        $gender = User::find($user_id)->gender;
        $format = Event::find($request->event_id)->mode;
        $data = array();
        foreach ($request->result as $result) {
            $category = $result[2];
            if (str_contains($result[0], 'flash') && $result[1] == "true") {
                $route_id = str_replace("flash-","", $result[0]);
                $attempt = 1;
                $data[] = array('grade' => $category, 'gender'=> $gender,'points' => 0, 'user_id'=> $user_id, 'event_id'=> $request->event_id, 'owner_id'=> $request->owner_id,'route_id' => $route_id, 'attempt'=> $attempt);
            }
            if (str_contains($result[0], 'redpoint') && $result[1] == "true") {
                $route_id = str_replace("redpoint-","", $result[0]);
                $attempt = 2;
                $data[] = array('grade' => $category, 'gender'=> $gender,'points' => 0, 'user_id'=> $user_id, 'event_id'=> $request->event_id, 'owner_id'=> $request->owner_id, 'route_id' => $route_id, 'attempt'=> $attempt);
            }
            if (str_contains($result[0], 'failed') && $result[1] == "true") {
                $route_id = str_replace("failed-","", $result[0]);
                $attempt = 0;
                $data[] = array('grade' => $category, 'gender'=> $gender, 'points' => 0, 'user_id'=> $user_id, 'event_id'=> $request->event_id, 'owner_id'=> $request->owner_id, 'route_id' => $route_id, 'attempt'=> $attempt);
            }
        };
        $final_data = array();
        $final_data_only_passed_route = array();
        foreach ($data as $route){
            # Варианты форматов подсчета баллов
            $owner_route = Route::where('grade','=',$route['grade'])->where('owner_id','=', $request->owner_id)->first();
            $value_route = (new \App\Models\ResultRouteQualificationClassic)->get_value_route($route['attempt'], $owner_route, $format, $request->event_id);
            # Формат все трассы считаем сразу
            if($format == 2) {
                (new \App\Models\EventAndCoefficientRoute)->update_coefficitient($route['event_id'], $route['route_id'], $route['owner_id'], $gender);
                $coefficient = ResultRouteQualificationClassic::get_coefficient($route['event_id'], $route['route_id'], $gender);
                $route['points'] = $coefficient * $value_route;
                (new \App\Models\Event)->insert_final_participant_result($route['event_id'], $route['points'], $route['user_id'], $gender);
            } else if($format == 1) {
                $route['points'] = $value_route;
            }
            $final_data[] = $route;
            if ($route['attempt'] != 0){
                $final_data_only_passed_route[] = $route;
            }
        }
        # Формат 10 лучших считаем уже после подсчета, так как ценность трассы еще зависит от коэффициента прохождений
        if($format == 1){
            usort($final_data_only_passed_route, function($a, $b) {
                return $a['points'] <=> $b['points'];
            });
            $points = 0;
            $amount = Event::find($request->event_id)->mode_amount_routes;
            $lastElems = array_slice($final_data_only_passed_route, -$amount, $amount);
            foreach ($lastElems as $lastElem) {
                $points += $lastElem['points'];
            }
            $participant = ResultQualificationClassic::where('user_id', '=', $user_id)->where('event_id', '=', $request->event_id)->first();
            $participant->points = $points;
            $participant->active = 1;
            $participant->save();
        }
//        dd($points);
        foreach ($final_data as $index => $data){
            $final_data[$index] = collect($data)->except('points')->toArray();
        }

        # Добавление json результатов для редактирование в админке
        $participant = ResultQualificationClassic::where('event_id', $request->event_id)->where('user_id', $request->user_id)->first();
        $participant->result_for_edit = $final_data;
        $participant->save();
        $result_classic_for_edit = ResultRouteQualificationClassic::where('event_id', $event->id)->where('user_id', $request->user_id)->first();
        if($event->is_access_user_edit_result && $result_classic_for_edit){
            foreach ($final_data as $data){
                $result_classic_for_edit = ResultRouteQualificationClassic::where('event_id', $event->id)->where('user_id', $data['user_id'])->where('route_id', $data['route_id'])->first();
                $result_classic_for_edit->attempt = $data['attempt'];
                $result_classic_for_edit->save();
            }
        } else {
            $result = ResultRouteQualificationClassic::insert($final_data);
        }



        $participants = User::query()
            ->leftJoin('result_qualification_classic', 'users.id', '=', 'result_qualification_classic.user_id')
            ->where('result_qualification_classic.event_id', '=', $request->event_id)
            ->select(
                'users.id',
                'users.gender',
            )->get();
        foreach ($participants as $participant) {
            Event::update_participant_place($event, $participant->id, $participant->gender);
        }
        Event::refresh_final_points_all_participant($event);
        $categories = ParticipantCategory::where('event_id', $request->event_id)->get();
        foreach ($categories as $category) {
            Cache::forget('result_male_cache_'.$category->category);
            Cache::forget('result_female_cache_'.$category->category);
        }
        if ($result) {
            $event = Event::find($request->event_id);
            return response()->json(['success' => true, 'message' => 'Успешная внесение результатов', 'link' => $event->link], 201);
        } else {
            return response()->json(['success' => false, 'message' => 'ошибка внесение результатов'], 422);
        }
    }

    public function listRoutesEvent(Request $request, $start_date, $climbing_gym, $title) {
        $event = Event::where('start_date', $start_date)->where('title_eng', '=', $title)->where('climbing_gym_name_eng', '=', $climbing_gym)->where('is_public', 1)->first();
        if(!$event){
            return view('404');
        }
        $grades = Route::where('owner_id', '=', $event->owner_id)->where('event_id', '=', $event->id)->get();
        $routes = [];
        foreach ($grades as $route){
            $route_class = new stdClass();
            $route_class->grade = $route->grade;
            $route_class->count = $route->route_id;
            $routes[$route->route_id] = $route_class;
        }
        $user_id = Auth::user()->id;
        $result_route_qualification_classic_participant = ResultRouteQualificationClassic::where('event_id', $event->id)->where('user_id', $user_id)->first();
        if($result_route_qualification_classic_participant){
            $result_qualification_classic_participant = ResultQualificationClassic::where('event_id', $event->id)->where('user_id', $user_id)->first();
            $result_participant = $result_qualification_classic_participant->result_for_edit;
        } else {
            $result_participant = null;
        }
        array_multisort(array_column($routes, 'count'), SORT_ASC, $routes);
        return view('result-page', compact('routes', 'event', 'result_participant'));
    }

    public function sendAllResult(Request $request)
    {
        $event = Event::find($request->event_id);
        try {
            $details = array();
            $details['title'] = $event->title;
            $details['event_start_date'] = $event->start_date;
            $details['event_url'] = env('APP_URL').$event->link;
            $details['event_id'] = $event->id;
            Mail::to($request->email)->queue(new \App\Mail\AllResultExcelFIle($details));
            return response()->json(['success' => true, 'message' => 'Успешная отправка'], 200);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['success' => false, 'message' => 'Произошла ошибка'], 422);
        }
    }

    public function addToListPending(Request $request)
    {
        $event = Event::where('id', '=', $request->event_id)->where('is_public', 1)->first();
        $user = User::find($request->user_id);
        if (!$event || !$event->is_registration_state || str_contains($user->email, 'telegram')) {
            return response()->json(['success' => false, 'message' => 'ошибка внесения в лист ожидания'], 422);
        }
        if (!$request->number_sets) {
            return response()->json(['success' => false, 'message' => 'Вы не выбрали сет'], 422);
        }
        $participant_categories = ParticipantCategory::where('event_id', '=', $request->event_id)->where('category', '=', $request->category)->first();
        if ($event->is_input_set != 1) {
            $list_pending = ListOfPendingParticipant::where('event_id', $request->event_id)->where('user_id', $request->user_id)->first();
            if (!$list_pending) {
                $list_pending = new ListOfPendingParticipant;
            }
            if ($event->is_auto_categories) {
                $list_pending->category_id = 0;
            } else {
                $list_pending->category_id = $participant_categories->id;
            }
            $list_pending->user_id = $request->user_id;
            $list_pending->event_id = $request->event_id;
            $list_pending->number_sets = $request->number_sets;
            $user = User::find($request->user_id);
            if($user){
                if($request->gender){
                    $user->gender = $request->gender;
                }
                if($request->sport_category){
                    $user->sport_category = $request->sport_category;
                }
                if($request->birthday){
                    $user->birthday = $request->birthday;
                }
                $user->save();
            }
            if ($list_pending->save()) {
                if($user && $event && $list_pending){
                    ResultQualificationClassic::send_main_about_list_pending($event, $user, $list_pending);
                }
                return response()->json(['success' => true, 'message' => 'Успешно']);
            }
        }
    }

    public function removeFromListPending(Request $request)
    {
        $list_pending = ListOfPendingParticipant::where('event_id', $request->event_id)->where('user_id', $request->user_id)->first();
        if ($list_pending->delete()) {
            return response()->json(['success' => true, 'message' => 'Успешное удалено']);
        }
    }
}
