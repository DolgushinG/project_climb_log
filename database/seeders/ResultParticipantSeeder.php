<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\ResultParticipant;
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
            for ($i = 1; $i <= $count_user; $i++){
                $result_participant = array(
                    array('owner_id' => $owner_id,'user_id' => $i,'event_id' => $event_id,'route_id' => 1,'attempt' => rand(0,2),'grade' => '5'),
                    array('owner_id' => $owner_id,'user_id' => $i,'event_id' => $event_id,'route_id' => 2,'attempt' => rand(0,2),'grade' => '5'),
                    array('owner_id' => $owner_id,'user_id' => $i,'event_id' => $event_id,'route_id' => 3,'attempt' => rand(0,2),'grade' => '5'),
                    array('owner_id' => $owner_id,'user_id' => $i,'event_id' => $event_id,'route_id' => 4,'attempt' => rand(0,2),'grade' => '5+'),
                    array('owner_id' => $owner_id,'user_id' => $i,'event_id' => $event_id,'route_id' => 5,'attempt' => rand(0,2),'grade' => '5+'),
                    array('owner_id' => $owner_id,'user_id' => $i,'event_id' => $event_id,'route_id' => 6,'attempt' => rand(0,2),'grade' => '5+'),
                    array('owner_id' => $owner_id,'user_id' => $i,'event_id' => $event_id,'route_id' => 7,'attempt' => rand(0,2),'grade' => '6A'),
                    array('owner_id' => $owner_id,'user_id' => $i,'event_id' => $event_id,'route_id' => 8,'attempt' => rand(0,2),'grade' => '6A'),
                    array('owner_id' => $owner_id,'user_id' => $i,'event_id' => $event_id,'route_id' => 9,'attempt' => rand(0,2),'grade' => '6A'),
                    array('owner_id' => $owner_id,'user_id' => $i,'event_id' => $event_id,'route_id' => 10,'attempt' => rand(0,2),'grade' => '6A+'),
                    array('owner_id' => $owner_id,'user_id' => $i,'event_id' => $event_id,'route_id' => 11,'attempt' => rand(0,2),'grade' => '6A+'),
                    array('owner_id' => $owner_id,'user_id' => $i,'event_id' => $event_id,'route_id' => 12,'attempt' => rand(0,2),'grade' => '6A+'),
                    array('owner_id' => $owner_id,'user_id' => $i,'event_id' => $event_id,'route_id' => 13,'attempt' => rand(0,2),'grade' => '6B'),
                    array('owner_id' => $owner_id,'user_id' => $i,'event_id' => $event_id,'route_id' => 14,'attempt' => rand(0,2),'grade' => '6B'),
                    array('owner_id' => $owner_id,'user_id' => $i,'event_id' => $event_id,'route_id' => 15,'attempt' => rand(0,2),'grade' => '6B'),
                    array('owner_id' => $owner_id,'user_id' => $i,'event_id' => $event_id,'route_id' => 16,'attempt' => rand(0,2),'grade' => '6B+'),
                    array('owner_id' => $owner_id,'user_id' => $i,'event_id' => $event_id,'route_id' => 17,'attempt' => rand(0,2),'grade' => '6B+'),
                    array('owner_id' => $owner_id,'user_id' => $i,'event_id' => $event_id,'route_id' => 18,'attempt' => rand(0,2),'grade' => '6C'),
                    array('owner_id' => $owner_id,'user_id' => $i,'event_id' => $event_id,'route_id' => 19,'attempt' => rand(0,2),'grade' => '6C'),
                    array('owner_id' => $owner_id,'user_id' => $i,'event_id' => $event_id,'route_id' => 20,'attempt' => rand(0,2),'grade' => '6C+'),
                    array('owner_id' => $owner_id,'user_id' => $i,'event_id' => $event_id,'route_id' => 21,'attempt' => rand(0,2),'grade' => '6C+'),
                    array('owner_id' => $owner_id,'user_id' => $i,'event_id' => $event_id,'route_id' => 22,'attempt' => rand(0,2),'grade' => '7A'),
                    array('owner_id' => $owner_id,'user_id' => $i,'event_id' => $event_id,'route_id' => 23,'attempt' => rand(0,2),'grade' => '7A'),
                    array('owner_id' => $owner_id,'user_id' => $i,'event_id' => $event_id,'route_id' => 24,'attempt' => rand(0,2),'grade' => '7A+'),
                    array('owner_id' => $owner_id,'user_id' => $i,'event_id' => $event_id,'route_id' => 25,'attempt' => rand(0,2),'grade' => '7A+'),
                    array('owner_id' => $owner_id,'user_id' => $i,'event_id' => $event_id,'route_id' => 26,'attempt' => rand(0,2),'grade' => '7B'),
                    array('owner_id' => $owner_id,'user_id' => $i,'event_id' => $event_id,'route_id' => 27,'attempt' => rand(0,2),'grade' => '7B'),
                    array('owner_id' => $owner_id,'user_id' => $i,'event_id' => $event_id,'route_id' => 28,'attempt' => rand(0,2),'grade' => '7B+'),
                    array('owner_id' => $owner_id,'user_id' => $i,'event_id' => $event_id,'route_id' => 29,'attempt' => rand(0,2),'grade' => '7C'),
                    array('owner_id' => $owner_id,'user_id' => $i,'event_id' => $event_id,'route_id' => 30,'attempt' => rand(0,2),'grade' => '7C+'));
                DB::table('result_participant')->insert($result_participant);
            }
            Event::refresh_final_points_all_participant($event_id);
        }
        for($i = 1; $i <= AdminRoleAndUsersSeeder::COUNT_EVENTS; $i++){
            prepare_result_participant($i, $i);
        }
    }
}
