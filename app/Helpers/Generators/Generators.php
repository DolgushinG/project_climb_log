<?php

namespace App\Helpers\Generators;

use App\Models\Event;
use App\Models\Grades;
use App\Models\Participant;
use App\Models\ParticipantCategory;
use App\Models\ResultParticipant;
use App\Models\ResultQualificationLikeFinal;
use App\Models\ResultRouteFinalStage;
use App\Models\ResultRouteQualificationLikeFinal;
use App\Models\ResultRouteSemiFinalStage;
use App\Models\ResultSemiFinalStage;
use App\Models\Set;
use App\Models\User;
use Database\Seeders\ParticipantSeeder;
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
                    ->withSemiFinal(1)
                    ->is_additional_final(0)
                    ->is_qualification_counting_like_final(0)
                    ->amount_point_flash(1)
                    ->amount_point_redpoint(1.2)
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
                    ->is_additional_final(1)
                    ->is_qualification_counting_like_final(1)
                    ->amount_routes_in_qualification_like_final(15)
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
                    ->withSemiFinal(1)
                    ->is_additional_final(1)
                    ->is_qualification_counting_like_final(0)
                    ->mode(1)
                    ->mode_amount_routes(15)
                    ->create();
                break;
            case 'custom_2':
                \App\Models\Event::factory()->count($count)
                    ->withOwnerId($i)
                    ->withCity($i)
                    ->withClimbingGym($i)
                    ->withSemiFinal(1)
                    ->is_additional_final(0)
                    ->is_qualification_counting_like_final(1)
                    ->amount_routes_in_qualification_like_final(20)
                    ->mode(2)
                    ->is_input_birthday(1)
                    ->is_need_sport_category(1)
                    ->create();
        }
    }

    public static function prepare_participant_with_owner($owner_id, $event_id, $users, $category, $start_user_id, $table)
    {
        $participants = array();
        $category_id = ParticipantCategory::where('category', $category)->where('owner_id', $owner_id)->where('event_id', $event_id)->first()->id;
        for ($i = $start_user_id; $i <= $users; $i++) {
            $user = User::find($i);
            $user->category = $category_id;
            $user->save();
            $sets = Set::where('owner_id', $owner_id)->pluck('id','number_set')->toArray();
            $participants[] = array('owner_id' => $owner_id, 'event_id' => $event_id, 'is_paid' => rand(0, 1),'category_id' => $category_id,'gender' => $user->gender, 'user_id' => $i, 'number_set_id' => $sets[array_rand($sets)], 'active' => 1);
        }
        DB::table($table)->insert($participants);
    }

    public static function prepare_result_participant($owner_id, $event_id, $table, $count=30)
    {

        if($table === 'result_participant'){
            ResultParticipant::where('event_id', $event_id)->delete();
            $active_participants = Participant::where('event_id', $event_id)->where('owner_id', $owner_id)->where('active', 1)->get();
            $event = Event::find($event_id);
            foreach ($active_participants as $active_participant){
                $result_participant = array();
                $info_routes = Grades::where('event_id', $event_id)->get();
                $route_id = 1;
                foreach ($info_routes as $route){
                    for($i = 1; $i <= $route->amount;$i++){
                        if($event->mode == 2){
                            (new \App\Models\EventAndCoefficientRoute)->update_coefficitient($event_id, $route_id, $owner_id, $active_participant->gender);
                        }
                        $result_participant[] = array('owner_id' => $owner_id ,'gender' => $active_participant->gender,'user_id' => $active_participant->user_id,'event_id' => $event_id,'route_id' => $route_id,'attempt' => rand(0,2),'grade' => $route->grade);
                        $route_id++;
                    }
                }
                DB::table('result_participant')->insert($result_participant);
            }
        }

        if($table === 'result_route_qualification_like_final') {
            ResultRouteQualificationLikeFinal::where('event_id', $event_id)->delete();
            $event = Event::find($event_id);
            $event_categories = $event->categories;
            $participants = ResultQualificationLikeFinal::where('event_id', $event_id)->where('owner_id', $owner_id)->where('active', 1)->get();
            $result = array();

            foreach($participants as $participant) {
                $category = ParticipantCategory::where('category', '=', $event_categories[array_rand($event_categories)])->where('event_id', $event_id)->first();
                if($category){
                    $category_id = $category->id;
                } else {
                    Log::error('Category has not found '.$category.' category_random'.$event_categories[array_rand($event_categories)].' event_id'.$event_id);
                }
                for ($route = 1; $route <= $event->amount_routes_in_qualification_like_final; $route++) {
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
                    $result[] = array('owner_id' => $owner_id, 'event_id' => $event_id, 'gender' => $participant->gender,'user_id' => $participant->user_id,'category_id' => $category_id, 'route_id' => $route, 'amount_try_top' => $amount_try_top, 'amount_try_zone' => $amount_try_zone, 'amount_top' => $amount_top, 'amount_zone' => $amount_zone);
                }
            }
            DB::table('result_route_qualification_like_final')->insert($result);
        }

        if($table === 'result_route_semifinal_stage') {
            ResultRouteSemiFinalStage::where('event_id', $event_id)->delete();
            $event = Event::find($event_id);
            $amount_the_best_participant = $event->amount_the_best_participant ?? 10;
            if($event->is_qualification_counting_like_final){
                $result_female = ResultQualificationLikeFinal::better_of_participants_qualification_like_final_stage($event_id, 'female', $amount_the_best_participant)->toArray();
                $result_male = ResultQualificationLikeFinal::better_of_participants_qualification_like_final_stage($event_id, 'male', $amount_the_best_participant)->toArray();
            } else {
                $result_female = Participant::better_participants($event_id, 'female', $amount_the_best_participant)->toArray();
                $result_male = Participant::better_participants($event_id, 'male', $amount_the_best_participant)->toArray();
            }
            $users = array_merge($result_female, $result_male);
            $result = array();
            foreach ($users as $user) {
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
                    $result[] = array('owner_id' => $owner_id, 'event_id' => $event_id, 'gender' => $user['gender'],'user_id' => $user['id'],'final_route_id' => $route, 'amount_try_top' => $amount_try_top, 'amount_try_zone' => $amount_try_zone, 'amount_top' => $amount_top, 'amount_zone' => $amount_zone);
                }
            }
            DB::table('result_route_semifinal_stage')->insert($result);
        }

        if($table === 'result_route_final_stage') {
            ResultRouteFinalStage::where('event_id', $event_id)->delete();
            $event = Event::find($event_id);
            $amount_the_best_participant_to_go_final = $event->amount_the_best_participant_to_go_final ?? 10;
            if($event->is_qualification_counting_like_final){
                $participant_from = 'qualification_counting_like_final';
                $result_is_qualification_counting_like_final_female = ResultQualificationLikeFinal::better_of_participants_qualification_like_final_stage($event_id, 'female', $amount_the_best_participant_to_go_final)->toArray();
                $result_is_qualification_counting_like_final_male = ResultQualificationLikeFinal::better_of_participants_qualification_like_final_stage($event_id, 'male', $amount_the_best_participant_to_go_final)->toArray();
            } else {
                if($event->is_semifinal){
                    $result_semifinal_female = ResultSemiFinalStage::better_of_participants_semifinal_stage($event_id, 'female', $amount_the_best_participant_to_go_final)->toArray();
                    $result_semifinal_male = ResultSemiFinalStage::better_of_participants_semifinal_stage($event_id, 'male', $amount_the_best_participant_to_go_final)->toArray();
                    $participant_from = 'qualification';
                } else {
                    $result_female = Participant::better_participants($event_id, 'female', $amount_the_best_participant_to_go_final)->toArray();
                    $result_male = Participant::better_participants($event_id, 'male', $amount_the_best_participant_to_go_final)->toArray();
                    $participant_from = 'qualification';
                }
            }

            $users = array_merge(
                $result_female ?? [],
                        $result_male ?? [],
                        $result_is_qualification_counting_like_final_female ?? [],
                        $result_is_qualification_counting_like_final_male ?? [],
                        $result_semifinal_female ?? [],
                        $result_semifinal_male ?? []
            );
            $result = array();
            foreach ($users as $user) {
                switch ($participant_from){
                    case 'qualification_counting_like_final':
                        $participant = ResultRouteQualificationLikeFinal::where('event_id', '=', $event_id)->where('user_id', '=', $user['id'])->first();
                        break;
                    case 'qualification':
                        $participant = Participant::where('event_id', '=', $event_id)->where('user_id', '=', $user['id'])->first();
                        break;
                    case 'semifinal':
                        $participant = ResultRouteSemiFinalStage::where('event_id', '=', $event_id)->where('user_id', '=', $user['id'])->first();
                }
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
                    $result[] = array('owner_id' => $owner_id, 'event_id' => $event_id, 'gender' => $user['gender'], 'user_id' => $user['id'],'category_id' => $participant->category_id, 'final_route_id' => $route, 'amount_try_top' => $amount_try_top, 'amount_try_zone' => $amount_try_zone, 'amount_top' => $amount_top, 'amount_zone' => $amount_zone);
                }
            }
            DB::table('result_route_final_stage')->insert($result);
            Event::refresh_final_points_all_participant_in_final($event_id, $owner_id);
        }
    }
}
