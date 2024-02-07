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
            $event_categories = Event::find($i)->categories;
            $participants = array();
            foreach ($event_categories as $category){
                $participants[] = ['owner_id' => $i,'event_id' => $i,'category' => $category];
            }
            DB::table('participant_categories')->insert($participants);
        }

    }
}
