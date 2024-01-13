<?php

namespace Database\Seeders;

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
        $participants = array(
            ['category' => 'Новички'],
            ['category' => 'Любители'],
            ['category' => 'Спортсмены'],
        );
        DB::table('participant_categories')->insert($participants);
    }
}
