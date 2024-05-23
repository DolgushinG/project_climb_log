<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\ResultQualificationClassic;
use App\Models\ResultSemiFinalStage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ResultRouteFinalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        function prepare_data_result_route_passed_with_owner($owner_id, $event_id)
        {

            #!!! НЕ АКТУАЛЬНО НА ДАННЫЙ МОМЕНТ ПОДУМАТЬ НАД УДАЛЕНИМ
            $event = Event::where('owner_id', $owner_id)->first();
            if($event->is_semifinal){
                $result_female = ResultSemiFinalStage::better_of_participants_semifinal_stage($event->id, 'female', 6)->toArray();
                $result_male = ResultSemiFinalStage::better_of_participants_semifinal_stage($event->id, 'male', 6)->toArray();
            } else {
                $result_female = ResultQualificationClassic::better_participants($event->id, 'female', 6)->toArray();
                $result_male = ResultQualificationClassic::better_participants($event->id, 'male', 6)->toArray();
            }
            $final_users = array_merge($result_female, $result_male);

            $result = array();
            foreach ($final_users as $user) {
                $participant = ResultQualificationClassic::where('event_id', '=', $event->id)->where('user_id', '=', $user['id'])->first();
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
                    $result[] = array('owner_id' => $owner_id, 'event_id' => $event_id,'gender' => $user['gender'], 'user_id' => $user['id'],'category_id' => $participant->category_id, 'final_route_id' => $route, 'amount_try_top' => $amount_try_top, 'amount_try_zone' => $amount_try_zone, 'amount_top' => $amount_top, 'amount_zone' => $amount_zone);
                }
            }
            DB::table('result_route_final_stage')->insert($result);
        }
        for($i = 2; $i <= AdminRoleAndUsersSeeder::COUNT_EVENTS; $i++){
            prepare_data_result_route_passed_with_owner($i, $i);
            Event::refresh_final_points_all_participant_in_final($i, $i);
        }

    }
}
