<?php

namespace Database\Seeders;

use App\Admin\Controllers\ParticipantsController;
use App\Helpers\Generators\Generators;
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
        $events = Event::all();
        foreach ($events as $event){
            $amount_categories = count($event->categories);
            if($amount_categories == 2){
                Generators::prepare_participant_with_owner($event->id, $event->id, 60, 'Новичок', 1);
                Generators::prepare_participant_with_owner($event->id, $event->id, 120, 'Общий зачет', 61);
            } else {
                Generators::prepare_participant_with_owner($event->id, $event->id, 40, 'Новичок', 1);
                Generators::prepare_participant_with_owner($event->id, $event->id, 80, 'Любители', 41);
                Generators::prepare_participant_with_owner($event->id, $event->id, 120, 'Спортсмены', 81);
            }
        }

    }
}
