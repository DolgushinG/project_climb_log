<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = ParticipantSeeder::USERS;
        $category_1_male = intval($users / 8);
        $category_1_female = intval($users / 8); #30
        $category_2_male = intval($users / 8);
        $category_2_female = intval($users / 8); # 30
        $category_3_male = intval($users / 4);
        $category_3_female = intval($users / 4); # 60
        \App\Models\User::factory()->count($category_1_male)->withCategory(1)->withGender('male')->create();
        \App\Models\User::factory()->count($category_1_female)->withCategory(1)->withGender('female')->create();
        \App\Models\User::factory()->count($category_2_male)->withCategory(2)->withGender('male')->create();
        \App\Models\User::factory()->count($category_2_female)->withCategory(2)->withGender('female')->create();
        \App\Models\User::factory()->count($category_3_male)->withCategory(3)->withGender('male')->create();
        \App\Models\User::factory()->count($category_3_female)->withCategory(3)->withGender('female')->create();
    }
}
