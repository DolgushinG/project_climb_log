<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Grades;
use App\Models\Route;
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
        for($i = 2; $i <= AdminRoleAndUsersSeeder::COUNT_EVENTS; $i++){
            $event = Event::where('owner_id', $i)->first();
            $event_id = $event->id;
            if(!$event->is_france_system_qualification){
                Route::generation_route($i, $event_id, $event->count_routes, Grades::getRoutes());
            } else {
                $grades = Grades::where('event_id', $event_id)->first();
                if(!$grades){
                    $grades = new Grades;
                }
                $grades->owner_id = $i;
                $grades->event_id = $event_id;
                $grades->count_routes = $event->count_routes;
                $grades->save();
            }
        }


    }
}
