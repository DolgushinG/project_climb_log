<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SetsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        function prepare_sets($owner_id){
            $sets = array(
                ['owner_id' => $owner_id, 'event_id' => $owner_id,'time' => '10:00-12:00','max_participants' => 135, 'day_of_week' => 'Friday','number_set' => 1],
                ['owner_id' => $owner_id, 'event_id' => $owner_id,'time' => '13:00-15:00','max_participants' => 135, 'day_of_week' => 'Friday','number_set' => 2],
                ['owner_id' => $owner_id, 'event_id' => $owner_id,'time' => '13:00-15:00','max_participants' => 135, 'day_of_week' => 'Saturday','number_set' => 6],
                ['owner_id' => $owner_id, 'event_id' => $owner_id,'time' => '16:00-18:00','max_participants' => 135, 'day_of_week' => 'Friday','number_set' => 3],
                ['owner_id' => $owner_id, 'event_id' => $owner_id,'time' => '16:00-18:00','max_participants' => 135, 'day_of_week' => 'Saturday','number_set' => 7],
                ['owner_id' => $owner_id, 'event_id' => $owner_id,'time' => '20:00-22:00','max_participants' => 135, 'day_of_week' => 'Friday','number_set' => 4],
                ['owner_id' => $owner_id, 'event_id' => $owner_id,'time' => '20:00-22:00','max_participants' => 135, 'day_of_week' => 'Saturday','number_set' => 8],
                ['owner_id' => $owner_id, 'event_id' => $owner_id,'time' => '10:00-12:00','max_participants' => 135, 'day_of_week' => 'Saturday','number_set' => 5],
            );
            DB::table('sets')->insert($sets);
        }
        for($i = 1; $i <= AdminRoleAndUsersSeeder::COUNT_EVENTS; $i++){
            prepare_sets($i);
        }
    }
}
