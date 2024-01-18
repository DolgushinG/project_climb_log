<?php

namespace Database\Seeders;

use App\Admin\Controllers\ParticipantsController;
use App\Models\Event;
use App\Models\Participant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ParticipantSeeder extends Seeder
{

    const USERS = 120;
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
                $participants[] = array('owner_id' => $owner_id, 'event_id' => $event_id, 'user_id' => $i, 'number_set' => rand(1, 6), 'active' => 1);
            }
            DB::table('participants')->insert($participants);
        }
        prepare_participant_with_owner(2, 1, self::USERS);
        prepare_participant_with_owner(3, 2, self::USERS);
    }
}
