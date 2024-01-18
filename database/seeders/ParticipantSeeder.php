<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ParticipantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $participants = array(
            ['owner_id' => 2,'event_id' => 1, 'user_id' => 1, 'number_set' => 1, 'active' => 0],
            ['owner_id' => 2,'event_id' => 1, 'user_id' => 2, 'number_set' => 2, 'active' => 0],
            ['owner_id' => 2,'event_id' => 1, 'user_id' => 3, 'number_set' => 3, 'active' => 0],
            ['owner_id' => 3,'event_id' => 1, 'user_id' => 4, 'number_set' => 1, 'active' => 0],
            ['owner_id' => 3,'event_id' => 1, 'user_id' => 5, 'number_set' => 2,  'active' => 0],
            );
        DB::table('participants')->insert($participants);
    }
}
