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
        \App\Models\Event::factory()->count(1)->withOwnerId(2)->create();
        \App\Models\Event::factory()->count(1)->withOwnerId(3)->create();
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
        ]);
    }
}
