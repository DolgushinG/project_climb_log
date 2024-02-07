<?php

namespace Database\Seeders;

use App\Models\Event;
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
        $users = ParticipantSeeder::USERS;
        $category_1_male = intval($users / 8);
        $category_1_female = intval($users / 8); #20
        $category_2_male = intval($users / 8);
        $category_2_female = intval($users / 8); # 20
        $category_3_male = intval($users / 4);
        $category_3_female = intval($users / 4); # 40

        for($i = 1;$i <= AdminRoleAndUsersSeeder::COUNT_EVENTS; $i++){
            \App\Models\Event::factory()->count(1)->withOwnerId($i)->withCity($i)->withClimbingGym($i)->withSemiFinal(0)->create();
            $amount_categories = count(Event::find($i)->categories);
            \App\Models\User::factory()->count($category_1_male)->withCategory(1)->withGender('male')->create();
            \App\Models\User::factory()->count($category_1_female)->withCategory(1)->withGender('female')->create();
            if($amount_categories == 2){
                \App\Models\User::factory()->count(intval($users/4))->withCategory(2)->withGender('male')->create();
                \App\Models\User::factory()->count(intval($users/4))->withCategory(2)->withGender('female')->create();
                \App\Models\User::factory()->count(intval($users/8))->withCategory(2)->withGender('male')->create();
                \App\Models\User::factory()->count(intval($users/8))->withCategory(2)->withGender('female')->create();
            }
            if($amount_categories == 3){
                \App\Models\User::factory()->count($category_2_male)->withCategory(2)->withGender('male')->create();
                \App\Models\User::factory()->count($category_2_female)->withCategory(2)->withGender('female')->create();
                \App\Models\User::factory()->count($category_3_male)->withCategory(3)->withGender('male')->create();
                \App\Models\User::factory()->count($category_3_female)->withCategory(3)->withGender('female')->create();
            }

        }
        $this->call([
            AdminTablesSeeder::class,
            AdminRoleAndUsersSeeder::class,
            FormatsSeeder::class,
//            UserSeeder::class,
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
