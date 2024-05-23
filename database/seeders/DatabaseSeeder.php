<?php

namespace Database\Seeders;

use App\Helpers\Generators\Generators;
use App\Models\Event;
use App\Models\ResultQualificationClassic;
use App\Models\ParticipantCategory;
use Illuminate\Database\Seeder;
use function Symfony\Component\Translation\t;

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
            $types = ['classic', 'child', 'custom_1', 'custom_2'];
            Generators::event_create(1, $i, $types[array_rand($types)]);
            $event = Event::where('owner_id', $i)->first();
            $categories = $event->categories;
            foreach ($categories as $category){
                $participant_category = new ParticipantCategory;
                $participant_category->category = $category;
                $participant_category->owner_id = $event->owner_id;
                $participant_category->event_id = $event->id;
                $participant_category->save();
            }
        }
        $genders = ['male', 'female','male', 'female'];
        for($i = 1; $i <= 300; $i++){
            \App\Models\User::factory()->count(1)->withGender($genders[array_rand($genders)])->create();
        }
        $tester = array('firstname' => 'Tester','middlename' => 'tester tester','lastname' => 'Tester','avatar' => NULL,'gender' => 'female','birthday' => '1992-05-29','city' => 'Pamelafort','year' => NULL,'team' => 'laboriosam','is_notify_about_new_event' => NULL,'is_notify_about_where_was_participant_event' => NULL,'telegram_id' => NULL,'vkontakte_id' => NULL,'yandex_id' => NULL,'category' => '3','skill' => NULL,'sport_category' => NULL,'email' => 'tester@tester.ru','email_verified_at' => '2024-04-23 13:31:56','password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','remember_token' => 'hAuohetZkE','created_at' => '2024-04-23 13:31:56','updated_at' => '2024-04-24 13:09:57');
        \Illuminate\Support\Facades\DB::table('users')->insert($tester);
        $this->call([
            AdminTablesSeeder::class,
            AdminRoleAndUsersSeeder::class,
            FormatsSeeder::class,
            SetsSeeder::class,
//            ParticipantSeeder::class,
            GradesSeeder::class,
//            ResultParticipantSeeder::class,
//            ResultRouteSemiFinalStageSeeder::class,
//            ResultRouteFinalSeeder::class,
        ]);
    }

}
