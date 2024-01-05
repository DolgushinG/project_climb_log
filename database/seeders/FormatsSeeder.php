<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FormatsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $formats = array(
            ['format' => '10 лучших'],
            ['format' => 'Все трассы'],
        );
        DB::table('formats')->insert($formats);
    }
}
