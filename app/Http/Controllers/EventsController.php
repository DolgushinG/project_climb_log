<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRequest;
use App\Jobs\UpdateResultParticipants;
use App\Models\Event;
use App\Models\EventAndCoefficientRoute;
use App\Models\Grades;
use App\Models\Participant;
use App\Models\ParticipantCategory;
use App\Models\ResultParticipant;
use App\Models\Set;
use App\Models\User;
use Encore\Admin\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use stdClass;

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
    public function show(Request $request, $climbing_gym, $title){
        if(\Encore\Admin\Facades\Admin::user()){
            $event = Event::where('title_eng', '=', $title)->where('climbing_gym_name_eng', '=', $climbing_gym)->first();
        } else {
            $event = Event::where('title_eng', '=', $title)->where('climbing_gym_name_eng', '=', $climbing_gym)->where('active', '=', 1)->first();
        }
        if($event){
            $sets = Set::where('owner_id', '=', $event->owner_id)->orderBy('day_of_week')->orderBy('number_set')->get();
            foreach ($sets as $set){
                $participants_event = Participant::where('event_id','=',$event->id)->where('owner_id','=',$event->owner_id)->where('number_set', '=', $set->number_set)->count();
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
            }
            $categories = ParticipantCategory::all();

            return view('welcome', compact('event', 'categories', 'sets'));
        } else {
            return view('404');
        }

    }
    public function get_participants(Request $request,$climbing_gym, $title){
        $event = Event::where('title_eng', '=', $title)->first();
        $participants = array();
        $participant_event = Participant::where('event_id', '=',$event->id)->get();
        $users_id = $participant_event->pluck('user_id')->toArray();
        $users = User::whereIn('id', $users_id)->get()->toArray();
        $users_event = $participant_event->toArray();
        $sets = Set::all();
        $index = 0;
        foreach($users_event as $set => $user) {
            if ($index <= count($users)) {
                $set = $sets->where('number_set', '=', $user['number_set'])->where('owner_id', '=',$event->owner_id)->first();
                $participants[] = array(
                    'firstname' => $users[$index]['firstname'],
                    'lastname' => $users[$index]['lastname'],
                    'city' => $users[$index]['city'],
                    'team' => $users[$index]['team'],
                    'number_set' => $user['number_set'],
                    'time' => $set->time.' '.trans_choice('somewords.'.$set->day_of_week, 10),
                    'gender' => $users[$index]['gender'],
                    'category_id' => $users[$index]['category'],
                    );
            }
            $index++;
        }
        $categories = ParticipantCategory::all();
        return view('event.participants', compact('event', 'participants', 'categories'));
    }

    public function get_final_results(Request $request, $climbing_gym, $title){
        $event = Event::where('title_eng', '=', $title)->first();
        $final_results = Participant::where('event_id', '=', $event->id)->orderBy('points', 'DESC')->get()->toArray();
        $user_ids = Participant::where('event_id', '=', $event->id)->pluck('user_id')->toArray();
        $stats = new stdClass();
        $female_categories = array();
        $male_categories = array();
        $stats->male = User::whereIn('id', $user_ids)->where('gender', '=', 'male')->get()->count();
        $stats->female = User::whereIn('id', $user_ids)->where('gender', '=', 'female')->get()->count();
        $categories = ParticipantCategory::where('event_id', $event->id)->get();

        foreach ($categories as $category){
            $user_female = User::whereIn('id', $user_ids)->where('gender', '=', 'female')->pluck('id');
            $user_male = User::whereIn('id', $user_ids)->where('gender', '=', 'male')->pluck('id');
            $female_categories[$category->id] = Participant::whereIn('user_id', $user_female)->where('category_id', '=', $category->id)->get()->count();
            $male_categories[$category->id] = Participant::whereIn('user_id', $user_male)->where('event_id', '=', $event->id)->where('category_id', '=', $category->id)->get()->count();
        }
        $stats->female_categories = $female_categories;
        $stats->male_categories = $male_categories;
        $result = [];
        foreach ($final_results as $res) {
            $user = User::where('id', '=', $res['user_id'])->first();
            $participant = Participant::where('event_id', '=', $event->id)->where('user_id', '=', $res['user_id'])->first();
            $res['user_name'] = $user->firstname.' '.$user->lastname;
            $res['gender'] = $user->gender;
            $res['city'] = $user->city;
            $res['category_id'] = $participant->category_id;
            $result[] = $res;
        }
        return view('event.final_result', compact('event', 'result',  'categories', 'stats'));
    }

    public function store(StoreRequest $request) {

        $participant = new Participant;
        $participant->event_id = $request->event_id;
        $participant->user_id = $request->user_id;
        $participant->number_set = $request->number_set;
        $participant->category_id = $request->category;
        $participant->owner_id = Event::find($request->event_id)->owner_id;
        $participant->active = 0;
        $participant->save();

        if($request->category){
            $user = User::find($request->user_id);
            $user->category = $request->category;
            $user->save();
        }

        if ($participant->save()) {
            return response()->json(['success' => true, 'message' => 'Успешная регистрация'], 201);
        } else {
            return response()->json(['success' => false, 'message' => 'ошибка регистрации'], 422);
        }
    }

    public function sendResultParticipant(Request $request) {
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
                $data[] = array('grade' => $category, 'points' => 0, 'user_id'=> $user_id, 'event_id'=> $request->event_id, 'owner_id'=> $request->owner_id,'route_id' => $route_id, 'attempt'=> $attempt);
            }
            if (str_contains($result[0], 'redpoint') && $result[1] == "true") {
                $route_id = str_replace("redpoint-","", $result[0]);
                $attempt = 2;
                $data[] = array('grade' => $category, 'points' => 0, 'user_id'=> $user_id, 'event_id'=> $request->event_id, 'owner_id'=> $request->owner_id, 'route_id' => $route_id, 'attempt'=> $attempt);
            }
            if (str_contains($result[0], 'failed') && $result[1] == "true") {
                $route_id = str_replace("failed-","", $result[0]);
                $attempt = 0;
                $data[] = array('grade' => $category, 'points' => 0, 'user_id'=> $user_id, 'event_id'=> $request->event_id, 'owner_id'=> $request->owner_id, 'route_id' => $route_id, 'attempt'=> $attempt);
            }
        };
        $final_data = array();
        $final_data_only_passed_route = array();
        foreach ($data as $route){
            (new \App\Models\EventAndCoefficientRoute)->update_coefficitient($route['event_id'], $route['route_id'], $route['owner_id'], $gender);

            $coefficient = ResultParticipant::get_coefficient($route['event_id'], $route['route_id'], $gender);
            # Варианты форматов подсчета баллов
            $value_category = Grades::where('grade','=',$route['grade'])->where('owner_id','=', $request->owner_id)->first()->value;
            $value_route = (new \App\Models\ResultParticipant)->get_value_route($route['attempt'], $value_category, $format);
            $route['points'] = $coefficient * $value_route;
            # Формат все трассы считаем сразу
            if($format == 2) {
                $this->insert_final_participant_result($route);
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
            $lastElems = array_slice($final_data_only_passed_route, -10, 10);
            foreach ($lastElems as $lastElem) {
                $this->insert_final_participant_result($lastElem);
            }
        }
        foreach ($final_data as $index => $data){
            $final_data[$index] = collect($data)->except('points')->toArray();
        }
        $result = ResultParticipant::insert($final_data);

        UpdateResultParticipants::dispatch($request->event_id);
        if ($result) {
            $event = Event::find($request->event_id);
            return response()->json(['success' => true, 'message' => 'Успешная внесение результатов', 'link' => $event->link], 201);
        } else {
            return response()->json(['success' => false, 'message' => 'ошибка внесение результатов'], 422);
        }
    }

    public function insert_final_participant_result($route){
        $final_participant_result = Participant::where('event_id', '=', $route['event_id'])->where('user_id', '=', $route['user_id'])->first();
//        if ($record === null) {
//            $final_participant_result = new Participant;
//        } else {
//            $final_participant_result = $record;
//        }
        $final_participant_result->points = $final_participant_result->point + $route['points'];
//        $final_participant_result->event_id = $route['event_id'];
//        $final_participant_result->user_id = $route['user_id'];
        $final_participant_result->user_place = Participant::get_places_participant_in_qualification($route['event_id'], $route['user_id'], true);
//        $final_participant_result->owner_id = $route['owner_id'];
        $final_participant_result->save();
    }



    public function listRoutesEvent(Request $request, $title) {
        $event = Event::where('title_eng', '=', $title)->first();
        $grades = Grades::where('owner_id', '=', $event->owner_id)->where('event_id', '=', $event->id)->get();
        $routes = [];
        $main_count = 1;
        foreach ($grades as $route){
            for ($count = 1; $count <= $route->amount; $count++){
                $route_class = new stdClass();
                $route_class->grade = $route->grade;
                $route_class->count = $main_count;
                $routes[$main_count] = $route_class;
                $main_count++;
            }
        }
        return view('result-page', compact('routes', 'event'));
    }
}
