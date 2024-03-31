<?php

namespace Database\Seeders;

use App\Jobs\UpdateResultParticipants;
use App\Models\Event;
use App\Models\Grades;
use App\Models\Participant;
use App\Models\ResultParticipant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ResultParticipantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     */
    public function run()
    {
        function prepare_result_participant($owner_id, $event_id)
        {
            $count_user = ParticipantSeeder::USERS;
            for ($user_id = 1; $user_id <= $count_user; $user_id++){
                $result_participant = array();
                $info_routes = Grades::where('event_id', $event_id)->get();
                $gender = User::find($user_id)->gender;
                $route_id = 1;
                foreach ($info_routes as $route){
                    for($i = 1; $i <= $route->amount;$i++){
                        (new \App\Models\EventAndCoefficientRoute)->update_coefficitient($event_id, $route_id, $owner_id, $gender);
                        $result_participant[] = array('owner_id' => $owner_id ,'gender' => $gender,'user_id' => $user_id,'event_id' => $event_id,'route_id' => $route_id,'attempt' => rand(0,2),'grade' => $route->grade);
                        $route_id++;
                    }
                }
                DB::table('result_participant')->insert($result_participant);
            }
        }
        for($i = 1; $i <= AdminRoleAndUsersSeeder::COUNT_EVENTS; $i++){
            prepare_result_participant($i, $i);
        }
        Event::refresh_final_points_all_participant($i);

    }
}
