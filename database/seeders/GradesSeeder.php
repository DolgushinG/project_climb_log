<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Grades;
use Encore\Admin\Facades\Admin;
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
        for($i = 1; $i <= AdminRoleAndUsersSeeder::COUNT_EVENTS; $i++){
            $event = Event::find($i);
            Grades::settings_routes($event->owner_id, $event->id, $event->count_routes);
        }


    }
}
