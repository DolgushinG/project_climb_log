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
        $sets = array(
            ['owner_id' => 2, 'time' => '10:00-12:00','max_participants' => 35, 'day_of_week' => 'friday','number_set' => 1],
            ['owner_id' => 2, 'time' => '13:00-15:00','max_participants' => 35, 'day_of_week' => 'friday','number_set' => 2],
            ['owner_id' => 2, 'time' => '13:00-15:00','max_participants' => 35, 'day_of_week' => 'saturday','number_set' => 6],
            ['owner_id' => 2, 'time' => '16:00-18:00','max_participants' => 35, 'day_of_week' => 'friday','number_set' => 3],
            ['owner_id' => 2, 'time' => '16:00-18:00','max_participants' => 35, 'day_of_week' => 'saturday','number_set' => 7],
            ['owner_id' => 2, 'time' => '20:00-22:00','max_participants' => 35, 'day_of_week' => 'friday','number_set' => 4],
            ['owner_id' => 2, 'time' => '20:00-22:00','max_participants' => 35, 'day_of_week' => 'saturday','number_set' => 8],
            ['owner_id' => 2, 'time' => '10:00-12:00','max_participants' => 35, 'day_of_week' => 'saturday','number_set' => 5],

            ['owner_id' => 3, 'time' => '10:00-12:00','max_participants' => 35, 'day_of_week' => 'friday','number_set' => 1],
            ['owner_id' => 3, 'time' => '13:00-15:00','max_participants' => 35, 'day_of_week' => 'friday','number_set' => 2],
            ['owner_id' => 3, 'time' => '13:00-15:00','max_participants' => 35, 'day_of_week' => 'saturday','number_set' => 6],
            ['owner_id' => 3, 'time' => '16:00-18:00','max_participants' => 35, 'day_of_week' => 'friday','number_set' => 3],
            ['owner_id' => 3, 'time' => '16:00-18:00','max_participants' => 35, 'day_of_week' => 'saturday','number_set' => 7],
            ['owner_id' => 3, 'time' => '20:00-22:00','max_participants' => 35, 'day_of_week' => 'friday','number_set' => 4],
            ['owner_id' => 3, 'time' => '20:00-22:00','max_participants' => 35, 'day_of_week' => 'saturday','number_set' => 8],
            ['owner_id' => 3, 'time' => '10:00-12:00','max_participants' => 35, 'day_of_week' => 'saturday','number_set' => 5],
        );
        DB::table('sets')->insert($sets);
    }
}
