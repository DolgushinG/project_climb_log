<?php

namespace App\Helpers\Generators;

use App\Jobs\UpdateRouteCoefficientParticipants;
use App\Models\Event;
use App\Models\Grades;
use App\Models\ResultQualificationClassic;
use App\Models\ParticipantCategory;
use App\Models\ResultFinalStage;
use App\Models\ResultRouteQualificationClassic;
use App\Models\ResultFranceSystemQualification;
use App\Models\ResultRouteFinalStage;
use App\Models\ResultRouteFranceSystemQualification;
use App\Models\ResultRouteSemiFinalStage;
use App\Models\ResultSemiFinalStage;
use App\Models\Route;
use App\Models\Set;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Generators
{
    public static function event_create($count, $i, $type_event)
    {
        switch ($type_event){
            case 'classic':
                \App\Models\Event::factory()->count($count)
                    ->withOwnerId($i)
                    ->withCity($i)
                    ->withClimbingGym($i)
                    ->withSemiFinal(0)
                    ->is_sort_group_final(0)
                    ->is_france_system_qualification(0)
                    ->amount_point_flash(1.2)
                    ->amount_point_redpoint(1)
                    ->mode(2)
                    ->count_routes(30)
                    ->create();
                break;
            case 'child':
                \App\Models\Event::factory()->count($count)
                    ->withOwnerId($i)
                    ->withCity($i)
                    ->withClimbingGym($i)
                    ->withSemiFinal(0)
                    ->is_sort_group_final(1)
                    ->is_france_system_qualification(0)
                    ->mode(2)
                    ->is_input_birthday(1)
                    ->is_need_sport_category(1)
                    ->create();
                break;
            case 'custom_1':
                \App\Models\Event::factory()->count($count)
                    ->withOwnerId($i)
                    ->withCity($i)
                    ->withClimbingGym($i)
                    ->withSemiFinal(0)
                    ->is_sort_group_final(1)
                    ->is_france_system_qualification(0)
                    ->mode(1)
                    ->mode_amount_routes(15)
                    ->create();
                break;
            case 'custom_2':
                \App\Models\Event::factory()->count($count)
                    ->withOwnerId($i)
                    ->withCity($i)
                    ->withClimbingGym($i)
                    ->withSemiFinal(0)
                    ->is_sort_group_final(0)
                    ->is_france_system_qualification(0)
                    ->mode(2)
                    ->is_input_birthday(1)
                    ->is_need_sport_category(1)
                    ->create();
        }
    }

    public static function prepare_participant_with_owner($owner_id, $event_id, $users, $table, $start_user_id=null, $category=null)
    {
        $participants = array();
        $genders = ['male','female'];
        if($category){
            $category_id = ParticipantCategory::where('category', $category)->where('owner_id', $owner_id)->where('event_id', $event_id)->first()->id;
            for ($i = $start_user_id; $i <= $users; $i++) {
                $user = User::find($i);
                $user->category = $category_id;
                $user->save();
                $sets = Set::where('event_id', $event_id)->pluck('id','number_set')->toArray();
                $participants[] = array('owner_id' => $owner_id, 'event_id' => $event_id, 'is_paid' => 0,'category_id' => $category_id,'gender' => $user->gender ?? $genders[array_rand(['male','female'])], 'user_id' => $i, 'number_set_id' => $sets[array_rand($sets)], 'active' => 1, 'created_at' => Carbon::now());
            }
        } else {
            for ($i = 1; $i <= $users ; $i++) {
                $user = User::find($i);
                $sets = Set::where('event_id', $event_id)->pluck('id','number_set')->toArray();
                $participants[] = array('owner_id' => $owner_id, 'event_id' => $event_id, 'is_paid' => 0,'gender' => $user->gender ?? $genders[array_rand(['male','female'])], 'user_id' => $i, 'number_set_id' => $sets[array_rand($sets)], 'active' => 1, 'created_at' => Carbon::now());
            }
        }
        DB::table($table)->insert($participants);
    }

    public static function prepare_result_route_qualification_classic($owner_id, $event_id, $table, $count=30)
    {
        if($table === 'result_route_qualification_classic'){
            ResultRouteQualificationClassic::where('event_id', $event_id)->delete();
            $active_participants = ResultQualificationClassic::where('event_id', $event_id)->where('owner_id', $owner_id)->where('active', 1)->get();
            $event = Event::find($event_id);
            $type_group_routes = ['beginner', 'middle', 'pro'];
            foreach ($active_participants as $active_participant){
                $result_route_qualification_classic = array();
                $info_routes = Route::where('event_id', $event_id)->get();
                $group = $type_group_routes[rand(0,2)];
                foreach ($info_routes as $route){
                    $attempt = Grades::getAttemptFromGrades($route->grade, $group);
                    $result_route_qualification_classic[] = array('owner_id' => $owner_id ,'gender' => $active_participant->gender,'user_id' => $active_participant->user_id,'event_id' => $event_id,'route_id' => $route->route_id,'attempt' => $attempt,'grade' => $route->grade, 'created_at' => Carbon::now());
                }

                $participant = ResultQualificationClassic::where('event_id', $event_id)->where('user_id', $active_participant->user_id)->first();
                $participant->result_for_edit = $result_route_qualification_classic;
                $participant->save();
                DB::table('result_route_qualification_classic')->insert($result_route_qualification_classic);
            }
            if($event->mode == 2){
                UpdateRouteCoefficientParticipants::dispatch($event_id, 'male');
                UpdateRouteCoefficientParticipants::dispatch($event_id, 'female');
            }
        }

        if($table === 'result_route_france_system_qualification') {
            ResultRouteFranceSystemQualification::where('event_id', $event_id)->delete();
            $event = Event::find($event_id);
            $count_routes = Grades::where('event_id', $event_id)->first()->count_routes;
            $event_categories = $event->categories;
            $participants = ResultFranceSystemQualification::where('event_id', $event_id)->where('owner_id', $owner_id)->where('active', 1)->get();
            $result = array();

            foreach($participants as $participant) {
                $category = ParticipantCategory::where('category', '=', $event_categories[array_rand($event_categories)])->where('event_id', $event_id)->first();
                if($category){
                    $category_id = $category->id;
                } else {
                    Log::error('Category has not found '.$category.' category_random'.$event_categories[array_rand($event_categories)].' event_id'.$event_id);
                }
                $result_for_edit = [];
                for ($route = 1; $route <= $count_routes; $route++) {
                    $amount_zone = rand(0, 1);
                    if ($amount_zone) {
                        $amount_try_zone = rand(1, 10);
                    } else {
                        $amount_try_zone = 0;
                    }
                    if ($amount_zone) {
                        $amount_top = rand(0, 1);
                        if ($amount_top) {
                            $amount_try_top = rand(1, 10);
                        } else {
                            $amount_try_top = 0;
                        }
                    } else {
                        $amount_top = 0;
                        $amount_try_top = 0;
                    }
                    $result[] = array('owner_id' => $owner_id, 'event_id' => $event_id, 'gender' => $participant->gender,'user_id' => $participant->user_id,'category_id' => $category_id, 'route_id' => $route, 'amount_try_top' => $amount_try_top, 'amount_try_zone' => $amount_try_zone, 'amount_top' => $amount_top, 'amount_zone' => $amount_zone, 'created_at' => Carbon::now());
                    $result_for_edit[] = array(
                        'Номер маршрута' => $route,
                        'Попытки на топ' => $amount_try_top,
                        'Попытки на зону' => $amount_try_zone
                    );
                }
                $participant = ResultFranceSystemQualification::where('event_id', $event_id)->where('user_id', $participant->user_id)->first();
                $participant->active = 1;
                $participant->result_for_edit_france_system_qualification = $result_for_edit;
                $participant->save();
            }
            DB::table('result_route_france_system_qualification')->insert($result);
        }

        if($table === 'result_route_semifinal_stage') {
            ResultRouteSemiFinalStage::where('event_id', $event_id)->delete();
            $event = Event::find($event_id);
            $amount_the_best_participant = $event->amount_the_best_participant ?? 10;
            if($event->is_open_main_rating){
                $users = ResultSemiFinalStage::get_global_participant_semifinal($event, $amount_the_best_participant, null, true);
            } else {
                $users = ResultSemiFinalStage::get_participant_semifinal($event, $amount_the_best_participant, null, true);
            }
            $result = array();
            foreach ($users as $user) {
                if($event->is_france_system_qualification){
                    $participant = ResultRouteFranceSystemQualification::where('event_id', '=', $event_id)->where('user_id', '=', $user['id'])->first();
                    $category_id = $participant->category_id;

                } else {
                    $participant = ResultQualificationClassic::where('event_id', '=', $event_id)->where('user_id', '=', $user['id'])->first();
                    if($event->is_open_main_rating && $event->is_auto_categories){
                        $category_id = $participant->global_category_id;
                    } else {
                        $category_id = $participant->category_id;
                    }
                }
                $result_for_edit = [];
                for ($route = 1; $route <= $event->amount_routes_in_semifinal; $route++) {
                    $amount_zone = rand(0, 1);
                    if ($amount_zone) {
                        $amount_try_zone = rand(1, 10);
                    } else {
                        $amount_try_zone = 0;
                    }
                    if ($amount_zone) {
                        $amount_top = rand(0, 1);
                        if ($amount_top) {
                            $amount_try_top = rand(1, 10);
                        } else {
                            $amount_try_top = 0;
                        }
                    } else {
                        $amount_top = 0;
                        $amount_try_top = 0;
                    }
                    $result[] = array('owner_id' => $owner_id, 'event_id' => $event_id, 'gender' => $user['gender'],'category_id' => $category_id,'user_id' => $user['id'],'final_route_id' => $route, 'amount_try_top' => $amount_try_top, 'amount_try_zone' => $amount_try_zone, 'amount_top' => $amount_top, 'amount_zone' => $amount_zone, 'created_at' => Carbon::now());
                    $result_for_edit[] = array(
                        'Номер маршрута' => $route,
                        'Попытки на топ' => $amount_try_top,
                        'Попытки на зону' => $amount_try_zone
                    );
                }
                $participant_semifinal = ResultSemiFinalStage::where('event_id', $event_id)->where('user_id', $participant->user_id)->first();
                if(!$participant_semifinal){
                    $participant_semifinal = new ResultSemiFinalStage;
                }
                $participant_semifinal->owner_id = $owner_id;
                $participant_semifinal->event_id = $event_id;
                $participant_semifinal->user_id = $user['id'];
                $participant_semifinal->category_id = $category_id;
                $participant_semifinal->gender = $user['gender'];
                $participant_semifinal->result_for_edit_semifinal = $result_for_edit;
                $participant_semifinal->save();
            }
            DB::table('result_route_semifinal_stage')->insert($result);
            Event::refresh_final_points_all_participant_in_semifinal($event_id);
        }

        if($table === 'result_route_final_stage') {
            ResultRouteFinalStage::where('event_id', $event_id)->delete();

            $event = Event::find($event_id);
            if($event->is_open_main_rating){
                $users = ResultFinalStage::get_final_global_participant($event, null, true);
            } else {
                $users = ResultFinalStage::get_final_participant($event, null, true);
            }

            $result = array();

            foreach ($users as $user) {
                if($event->is_france_system_qualification){
                    $participant = ResultRouteFranceSystemQualification::where('event_id', '=', $event_id)->where('user_id', '=', $user['id'])->first();
                    $category_id = $participant->category_id;
                } else {
                    $participant = ResultQualificationClassic::where('event_id', '=', $event_id)->where('user_id', '=', $user['id'])->first();
                    if($event->is_open_main_rating && $event->is_auto_categories){
                        $category_id = $participant->global_category_id;
                    } else {
                        $category_id = $participant->category_id;
                    }
                }
                $result_for_edit = [];
                for ($route = 1; $route <= $event->amount_routes_in_final; $route++) {
                    $amount_zone = rand(0, 1);
                    if ($amount_zone) {
                        $amount_try_zone = rand(1, 10);
                    } else {
                        $amount_try_zone = 0;
                    }
                    if ($amount_zone) {
                        $amount_top = rand(0, 1);
                        if ($amount_top) {
                            $amount_try_top = rand(1, 10);
                        } else {
                            $amount_try_top = 0;
                        }
                    } else {
                        $amount_top = 0;
                        $amount_try_top = 0;
                    }
                    $result[] = array('owner_id' => $owner_id, 'event_id' => $event_id, 'gender' => $user['gender'], 'user_id' => $user['id'],'category_id' => $category_id, 'final_route_id' => $route, 'amount_try_top' => $amount_try_top, 'amount_try_zone' => $amount_try_zone, 'amount_top' => $amount_top, 'amount_zone' => $amount_zone, 'created_at' => Carbon::now());

                    $result_for_edit[] = array(
                        'Номер маршрута' => $route,
                        'Попытки на топ' => $amount_try_top,
                        'Попытки на зону' => $amount_try_zone
                    );
                }

                $participant_final = ResultFinalStage::where('event_id', $event_id)->where('user_id', $participant->user_id)->first();
                if(!$participant_final){
                    $participant_final = new ResultFinalStage;
                }
                $participant_final->owner_id = $owner_id;
                $participant_final->event_id = $event_id;
                $participant_final->user_id = $user['id'];
                $participant_final->category_id = $category_id;
                $participant_final->gender = $user['gender'];
                $participant_final->result_for_edit_final = $result_for_edit;
                $participant_final->save();
            }
            DB::table('result_route_final_stage')->insert($result);
            Event::refresh_final_points_all_participant_in_final($event_id);
        }
    }
}
