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
        for($i = 2;$i <= AdminRoleAndUsersSeeder::COUNT_EVENTS; $i++){
            \App\Models\Event::factory()->count(1)->withOwnerId($i)->create();
        }

        $this->call([
            AdminTablesSeeder::class,
            AdminRoleAndUsersSeeder::class,
            UserSeeder::class,
            SetsSeeder::class,
            ParticipantCategoriesSeeder::class,
            ParticipantSeeder::class,
            GradesSeeder::class,
            ResultParticipantSeeder::class,
            ResultFinalStageSeeder::class,
            ResultRouteAdditionalFinalSeeder::class,
        ]);
    }
}
