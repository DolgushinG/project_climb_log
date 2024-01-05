<?php

namespace Database\Seeders;

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
         \App\Models\User::factory(10)->create();
         \App\Models\Event::factory(2)->create();
        $this->call([
            GradesSeeder::class,
            ParticipantSeeder::class,
            AdminTablesSeeder::class,
            ParticipantCategoriesSeeder::class,
            SetsSeeder::class,
        ]);
    }
}
