<?php

namespace Database\Seeders;

use App\Models\Participant;
use Illuminate\Database\Seeder;

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
            \App\Models\Event::factory()->count(1)->withOwnerId($i)->withCity($i)->withClimbingGym($i)->withSemiFinal(1)->create();
        }
        $this->call([
            AdminTablesSeeder::class,
            AdminRoleAndUsersSeeder::class,
            FormatsSeeder::class,
            UserSeeder::class,
            SetsSeeder::class,
            ParticipantCategoriesSeeder::class,
            ParticipantSeeder::class,
            GradesSeeder::class,
            ResultParticipantSeeder::class,
            ResultRouteSemiFinalStageSeeder::class,
            ResultRouteFinalSeeder::class,
        ]);
    }
}
