<?php

namespace App\Http\Controllers;

use App\Helpers\Helpers;
use App\Http\Requests\StoreRequest;
use App\Jobs\UpdateResultParticipants;
use App\Models\Event;
use App\Models\EventAndCoefficientRoute;
use App\Models\Grades;
use App\Models\Participant;
use App\Models\ParticipantCategory;
use App\Models\ResultParticipant;
use App\Models\ResultQualificationLikeFinal;
use App\Models\Route;
use App\Models\Set;
use App\Models\User;
use Encore\Admin\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use stdClass;
use function Symfony\Component\String\s;

class EventsController extends Controller
{
    public function counting($method, $value) {
        if ("ceil" == $method) {
            return ceil($value);
        }
        if ("round" == $method) {
            return round($value);
        }
        if ("int" == $method) {
            return intval($value);
        }
        if ("floor" == $method) {
            return floor($value);
        }
    }

    /**
     * @throws \Exception
     */
    public function show(Request $request, $climbing_gym, $title){
        $event_public_exist = Event::where('title_eng', '=', $title)->where('climbing_gym_name_eng', '=', $climbing_gym)->where('is_public', 1)->first();
        $event_exist = Event::where('title_eng', '=', $title)->where('climbing_gym_name_eng', '=', $climbing_gym)->first();
        $pre_show = false;
        if($event_public_exist){
            $event = $event_public_exist;
        } else {
            if($request->is('admin/event/*')){
                $pre_show = true;
                $event = $event_exist;
            }
        }
        if($event_public_exist || $pre_show){
            $sets = Set::where('owner_id', '=', $event->owner_id)->orderBy('number_set')->get();
            foreach ($sets as $set){
                $participants_event = Participant::where('event_id','=',$event->id)->where('owner_id','=',$event->owner_id)->where('number_set_id', '=', $set->id)->count();
                $set->free = $set->max_participants - $participants_event;
                $a = $set->max_participants;
                $b = $set->free;

                if ($a === $b) {
                    $percent = 0;
                } elseif ($a < $b) {
                    $diff = $b - $a;
                    $percent = $diff / $b * 100;
                } else {
                    $diff = $a - $b;
                    $percent = $diff / $a * 100;
                }
                $set->procent = intval($percent);
                $set->date = Helpers::getDatesByDayOfWeek($event_exist->start_date, $event_exist->end_date);
            }
            $sport_categories = User::sport_categories;
            return view('welcome', compact(['event', 'sport_categories', 'sets']));
        } else {
            return view('404');
        }
    }
    public function get_participants(Request $request, $climbing_gym, $title){
        $event = Event::where('title_eng', '=', $title)->where('is_public', 1)->first();
        if($event) {
            $participants = array();
            if($event->is_qualification_counting_like_final){
                $participant_event = ResultQualificationLikeFinal::where('event_id', '=', $event->id)->get();
            } else {
                $participant_event = Participant::where('event_id', '=', $event->id)->get();
            }
            $users_id = $participant_event->pluck('user_id')->toArray();
            $users = User::whereIn('id', $users_id)->get()->toArray();
            $users_event = $participant_event->toArray();
            if($event->is_input_set != 1){
                $days = Set::where('owner_id', '=', $event->owner_id)->select('day_of_week')->distinct()->get();
                $sets = Set::where('owner_id', '=', $event->owner_id)->get();
                $number_sets = Set::where('owner_id', '=', $event->owner_id)->pluck('id');
                foreach ($number_sets as $index => $set) {
                    if($event->is_qualification_counting_like_final){
                        $sets[$index]->count_participant = ResultQualificationLikeFinal::where('event_id', '=', $event->id)->where('number_set_id', $set)->count();
                    } else {
                        $sets[$index]->count_participant = Participant::where('event_id', '=', $event->id)->where('number_set_id', $set)->count();
                    }
                }
            } else {
                $days = null;
                $sets = null;
            }
            $index = 0;
            foreach ($users_event as $set => $user) {
                if ($index <= count($users)) {
                    if($event->is_input_set == 1){
                        $array_for_set = array(
                            'middlename' => $users[$index]['middlename'],
                            'city' => $users[$index]['city'],
                            'team' => $users[$index]['team'],
                            'gender' => $users[$index]['gender'],
                        );
                    } else {
                        $set = $sets->where('id', '=', $user['number_set_id'])->where('owner_id', '=', $event->owner_id)->first();
                        $array_for_set = array(
                            'middlename' => $users[$index]['middlename'],
                            'city' => $users[$index]['city'],
                            'team' => $users[$index]['team'],
                            'number_set' => $set->number_set,
                            'time' => $set->time . ' ' . trans_choice('somewords.' . $set->day_of_week, 10),
                            'gender' => $users[$index]['gender'],
                        );
                    }

                    $participants[] = $array_for_set;
                }
                $index++;
            }
        } else {
            return view('404');
        }
        return view('event.participants', compact(['days', 'event', 'participants', 'sets']));
    }

    public function get_final_results(Request $request, $climbing_gym, $title)
    {
        $event = Event::where('title_eng', '=', $title)->where('is_public', 1)->first();
        $final_results = Participant::where('event_id', '=', $event->id)->where('active', '=', 1)->orderBy('points', 'DESC')->get()->toArray();
        if($event){
            if(!$event->is_qualification_counting_like_final){
//                $final_results = Participant::where('event_id', '=', $event->id)->where('active', '=', 1)->orderBy('points', 'DESC')->get()->toArray();
                $user_ids = Participant::where('event_id', '=', $event->id)->pluck('user_id')->toArray();
                $stats = new stdClass();
                $female_categories = array();
                $male_categories = array();
                $stats->male = User::whereIn('id', $user_ids)->where('gender', '=', 'male')->get()->count();
                $stats->female = User::whereIn('id', $user_ids)->where('gender', '=', 'female')->get()->count();
                $result_male = array();
                $result_female = array();
                $categories = ParticipantCategory::where('event_id', $event->id)->get();
                foreach ($categories as $category) {
                    $result_male[] = Participant::get_sorted_group_participant($event->id, 'male', $category->id)->toArray();
                    $result_female[] = Participant::get_sorted_group_participant($event->id, 'female', $category->id)->toArray();
                    $user_female = User::whereIn('id', $user_ids)->where('gender', '=', 'female')->pluck('id');
                    $user_male = User::whereIn('id', $user_ids)->where('gender', '=', 'male')->pluck('id');
                    $female_categories[$category->id] = Participant::whereIn('user_id', $user_female)->where('event_id', '=', $event->id)->where('category_id', '=', $category->id)->get()->count();
                    $male_categories[$category->id] = Participant::whereIn('user_id', $user_male)->where('event_id', '=', $event->id)->where('category_id', '=', $category->id)->get()->count();
                }
                $result_male_final = Helpers::arrayValuesRecursive($result_male);
                $result_female_final = Helpers::arrayValuesRecursive($result_female);
                $result = array_merge($result_male_final, $result_female_final);
                $stats->female_categories = $female_categories;
                $stats->male_categories = $male_categories;
                $categories = $categories->toArray();
            }
        } else {
            return view('404');
        }
        return view('event.final_result', compact(['event', 'result',  'categories', 'stats']));
    }




    public function store(StoreRequest $request) {
        $event = Event::where('id', '=', $request->event_id)->where('is_public', 1)->first();
        if(!$event || !$event->is_registration_state ){
            return response()->json(['success' => false, 'message' => 'ошибка регистрации'], 422);
        }
        $participant_categories = ParticipantCategory::where('event_id', '=', $request->event_id)->where('category', '=', $request->category)->first();
        if($event->is_qualification_counting_like_final){
            $participant = ResultQualificationLikeFinal::where('user_id',  $request->user_id)->where('event_id', $request->event_id)->first();
            if($participant){
                return response()->json(['success' => false, 'message' => 'ошибка регистрации'], 422);
            }
            $participant = new ResultQualificationLikeFinal;
        } else {
            $participant = Participant::where('user_id',  $request->user_id)->where('event_id', $request->event_id)->first();
            if($participant){
                return response()->json(['success' => false, 'message' => 'ошибка регистрации'], 422);
            }
            $participant = new Participant;
        }
        $event = Event::find($request->event_id);
        if($event->is_input_set != 1){
            $number_set = $request->number_set;
            $set = Set::where('number_set', $number_set)->where('owner_id', $event->owner_id)->first();
            $participant->number_set_id = $set->id;
        }

        $participant->event_id = $request->event_id;
        $participant->gender = $request->gender;
        $participant->user_id = $request->user_id;
        $participant->category_id = $participant_categories->id;
        $participant->owner_id = $event->owner_id;
        $participant->active = 0;
        $participant->save();

        if($request->user_id){
            $user = User::find($request->user_id);
            $user->gender = $request->gender;
            $user->sport_category = $request->sport_category;
            $user->birthday = $request->birthday;
            $user->save();
        }

        if ($participant->save()) {
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
        if($event->is_qualification_counting_like_final){
            $participant = ResultQualificationLikeFinal::where('user_id',  $request->user_id)->where('event_id', $request->event_id)->first();
        } else {
            $participant = Participant::where('user_id',  $request->user_id)->where('event_id', $request->event_id)->first();
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
            return response()->json(['success' => false, 'message' => 'ошибка регистрации'], 422);
        }
        $user_id = $request->user_id;
        $participant_active = Participant::where('user_id', '=', $user_id)->where('event_id', '=', $request->event_id)->first();
        if (!$participant_active){
            return response()->json(['success' => false, 'message' => 'ошибка внесение результатов'], 422);
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
            $value_category = Route::where('grade','=',$route['grade'])->where('owner_id','=', $request->owner_id)->first()->value;
            $value_route = (new \App\Models\ResultParticipant)->get_value_route($route['attempt'], $value_category, $format);
            # Формат все трассы считаем сразу
            if($format == 2) {
                (new \App\Models\EventAndCoefficientRoute)->update_coefficitient($route['event_id'], $route['route_id'], $route['owner_id'], $gender);
                $coefficient = ResultParticipant::get_coefficient($route['event_id'], $route['route_id'], $gender);
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
            $participant = Participant::where('user_id', '=', $user_id)->where('event_id', '=', $request->event_id)->first();
            $participant->points = $points;
            $participant->active = 1;
            $participant->save();
        }
//        dd($points);
        foreach ($final_data as $index => $data){
            $final_data[$index] = collect($data)->except('points')->toArray();
        }
        $result = ResultParticipant::insert($final_data);

        $participants = User::query()
            ->leftJoin('participants', 'users.id', '=', 'participants.user_id')
            ->where('participants.event_id', '=', $request->event_id)
            ->select(
                'users.id',
                'users.gender',
            )->get();
        foreach ($participants as $participant) {
            Event::update_participant_place($request->event_id, $participant->id, $participant->gender);
        }
        Event::refresh_final_points_all_participant($event);
        if ($result) {
            $event = Event::find($request->event_id);
            return response()->json(['success' => true, 'message' => 'Успешная внесение результатов', 'link' => $event->link], 201);
        } else {
            return response()->json(['success' => false, 'message' => 'ошибка внесение результатов'], 422);
        }
    }





    public function listRoutesEvent(Request $request, $title) {
        $event = Event::where('title_eng', '=', $title)->where('is_public', 1)->first();
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
        array_multisort(array_column($routes, 'count'), SORT_ASC, $routes);
        return view('result-page', compact('routes', 'event'));
    }

}
