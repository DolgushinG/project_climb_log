<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Participant;
use App\Models\ResultParticipant;
use App\Models\User;
use Encore\Admin\Facades\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ResultRouteSemiFinalStageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        function prepare_with_owner($owner_id, $event_id)
        {
            $event = Event::find($event_id);
            #!!! НЕ АКТУАЛЬНО НА ДАННЫЙ МОМЕНТ ПОДУМАТЬ НАД УДАЛЕНИМ
            if($event->is_semifinal){
                $result_female = Participant::better_participants($event_id, 'female', 10)->toArray();
                $result_male = Participant::better_participants($event_id, 'male', 10)->toArray();
                $final_users = array_merge($result_female, $result_male);
                $result = array();
                foreach ($final_users as $user) {
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
                        $result[] = array('owner_id' => $owner_id, 'gender' => $user['gender'],'event_id' => $event_id, 'user_id' => $user['id'], 'final_route_id' => $route, 'amount_try_top' => $amount_try_top, 'amount_try_zone' => $amount_try_zone, 'amount_top' => $amount_top, 'amount_zone' => $amount_zone);
                    }
                }
                DB::table('result_route_semifinal_stage')->insert($result);
            }

        }
        for($i = 1; $i <= AdminRoleAndUsersSeeder::COUNT_EVENTS; $i++){
            prepare_with_owner($i, $i);
            $event = Event::find($i);
            if($event->is_semifinal){
                Event::refresh_final_points_all_participant_in_semifinal($i, $i);
            }
        }

    }
}
