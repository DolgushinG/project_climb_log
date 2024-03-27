<?php

namespace Database\Seeders;

use App\Helpers\Generators\Generators;
use App\Models\Event;
use App\Models\Participant;
use App\Models\ParticipantCategory;
use Illuminate\Database\Seeder;
use function Symfony\Component\Translation\t;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        for($i = 1;$i <= AdminRoleAndUsersSeeder::COUNT_EVENTS; $i++){
            $types = ['classic', 'child', 'custom_1', 'custom_2'];
            Generators::event_create(1, $i, $types[array_rand($types)]);
            $categories = Event::find($i)->categories;
            foreach ($categories as $category){
                $participant_category = new ParticipantCategory;
                $participant_category->category = $category;
                $participant_category->owner_id = $i;
                $participant_category->event_id = $i;
                $participant_category->save();
            }
        }
        $genders = ['male', 'female','male', 'female'];
        for($i = 1; $i <= 120; $i++){
            \App\Models\User::factory()->count(1)->withGender($genders[array_rand($genders)])->create();
        }
        $this->call([
            AdminTablesSeeder::class,
            AdminRoleAndUsersSeeder::class,
            FormatsSeeder::class,
            SetsSeeder::class,
//            ParticipantSeeder::class,
            GradesSeeder::class,
//            ResultParticipantSeeder::class,
//            ResultRouteSemiFinalStageSeeder::class,
//            ResultRouteFinalSeeder::class,
        ]);
    }

}
