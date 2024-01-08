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
            for($i = 3; $i <= $count; $i++) {
                $grades = array(
                    ['owner_id' => $i ,'grade' => '4', 'amount' => 3, 'value' => 100],
                    ['owner_id' => $i ,'grade' => '5', 'amount' => 4, 'value' => 150],
                    ['owner_id' => $i ,'grade' => '5+', 'amount' => 3, 'value' => 200],
                    ['owner_id' => $i ,'grade' => '6A', 'amount' => 5, 'value' => 250],
                    ['owner_id' => $i ,'grade' => '6A+', 'amount' => 5, 'value' => 300],
                    ['owner_id' => $i ,'grade' => '6B', 'amount' => 4, 'value' => 350],
                    ['owner_id' => $i ,'grade' => '6B+', 'amount' => 4, 'value' => 400],
                    ['owner_id' => $i ,'grade' => '6C', 'amount' => 4, 'value' => 450],
                    ['owner_id' => $i ,'grade' => '6C+', 'amount' => 4, 'value' => 500],
                    ['owner_id' => $i ,'grade' => '7A', 'amount' => 4, 'value' => 550],
                    ['owner_id' => $i ,'grade' => '7A+', 'amount' => 3, 'value' => 600],
                    ['owner_id' => $i ,'grade' => '7B', 'amount' => 3, 'value' => 650],
                    ['owner_id' => $i ,'grade' => '7B+', 'amount' => 2, 'value' => 700],
                    ['owner_id' => $i ,'grade' => '7C', 'amount' => 1, 'value' => 750],
                    ['owner_id' => $i ,'grade' => '7C+', 'amount' => 1, 'value' => 800],
                    ['owner_id' => $i ,'grade' => '8A', 'amount' => 0, 'value' => 850],

                );
                DB::table('grades')->insert($grades);
            }
        }
        retry(1);
    }
}
