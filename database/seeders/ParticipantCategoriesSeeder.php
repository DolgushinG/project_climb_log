<?php

namespace Database\Seeders;

use App\Models\Event;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ParticipantCategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        for($i = 1; $i <= AdminRoleAndUsersSeeder::COUNT_EVENTS; $i++){
            $participants = array(
                ['owner_id' => $i,'event_id' => $i,'category' => 'Новички'],
                ['owner_id' => $i,'event_id' => $i,'category' => 'Любители'],
                ['owner_id' => $i,'event_id' => $i,'category' => 'Спортсмены'],
            );
            DB::table('participant_categories')->insert($participants);
        }

    }
}
