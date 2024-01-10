<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GradesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        function retry($count) {
            for($i = 2; $i <= $count; $i++) {
                $grades = array(
                    ['owner_id' => $i, 'value' => 100],
                    ['owner_id' => $i, 'value' => 150],
                    ['owner_id' => $i, 'value' => 200],
                    ['owner_id' => $i, 'value' => 250],
                    ['owner_id' => $i, 'value' => 300],
                    ['owner_id' => $i, 'value' => 350],
                    ['owner_id' => $i, 'value' => 400],
                    ['owner_id' => $i, 'value' => 450],
                    ['owner_id' => $i, 'value' => 500],
                    ['owner_id' => $i, 'value' => 550],
                    ['owner_id' => $i, 'value' => 600],
                    ['owner_id' => $i, 'value' => 650],
                    ['owner_id' => $i, 'value' => 700],
                    ['owner_id' => $i, 'value' => 750],
                    ['owner_id' => $i, 'value' => 800],
                    ['owner_id' => $i, 'value' => 850],

                );
                DB::table('grades')->insert($grades);
            }
        }
        retry(1);
    }
}
