<?php

namespace Database\Seeders;

use App\Admin\Controllers\ParticipantsController;
use App\Models\Event;
use App\Models\Participant;
use App\Models\User;
use Database\Factories\EventFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ParticipantSeeder extends Seeder
{

    const USERS = 80;
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        function prepare_participant_with_owner($owner_id, $event_id, $users)
        {
            $participants = array();
            for ($i = 1; $i <= $users; $i++) {
                $category_id = User::find($i)->category;
                $participants[] = array('owner_id' => $owner_id, 'event_id' => $event_id, 'category_id' => $category_id, 'user_id' => $i, 'number_set' => rand(1, 6), 'active' => 1);
            }
            DB::table('participants')->insert($participants);
        }
        for($i = 1; $i <= AdminRoleAndUsersSeeder::COUNT_EVENTS; $i++){
            prepare_participant_with_owner($i, $i, self::USERS);
        }
    }
}
