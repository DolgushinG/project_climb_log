<?php

namespace Database\Seeders;

use App\Admin\Controllers\ParticipantsController;
use App\Models\Event;
use App\Models\Participant;
use App\Models\ParticipantCategory;
use App\Models\User;
use Database\Factories\EventFactory;
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
        function prepare_participant_with_owner($owner_id, $event_id, $users, $category, $start_user_id)
        {
            $participants = array();
            $category_id = ParticipantCategory::where('category', $category)->where('owner_id', $owner_id)->where('event_id', $event_id)->first()->id;
            for ($i = $start_user_id; $i <= $users; $i++) {
                $user = User::find($i);
                $user->category = $category_id;
                $user->save();
               $participants[] = array('owner_id' => $owner_id, 'event_id' => $event_id, 'is_paid' => rand(0, 1),'category_id' => $category_id, 'user_id' => $i, 'number_set' => rand(1, 6), 'active' => 1);
            }
            DB::table('participants')->insert($participants);
        }
        $events = Event::all();
        foreach ($events as $event){
            $amount_categories = count($event->categories);
            if($amount_categories == 2){
                prepare_participant_with_owner($event->id, $event->id, 60, 'Новичок', 1);
                prepare_participant_with_owner($event->id, $event->id, 120, 'Общий зачет', 61);
            } else {
                prepare_participant_with_owner($event->id, $event->id, 40, 'Новичок', 1);
                prepare_participant_with_owner($event->id, $event->id, 80, 'Любители', 41);
                prepare_participant_with_owner($event->id, $event->id, 120, 'Спортсмены', 81);
            }
        }


    }
}
