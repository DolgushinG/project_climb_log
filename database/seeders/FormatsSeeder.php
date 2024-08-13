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
            ['format' => 'N лучших'],
            ['format' => 'Все трассы'],
            ['format' => 'Все трассы (по баллам)'],
        );
        DB::table('formats')->insert($formats);
    }
}
