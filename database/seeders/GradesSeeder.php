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
        $routes = Grades::getRoutes();
        for($i = 1; $i <= AdminRoleAndUsersSeeder::COUNT_EVENTS; $i++){
            Event::generation_route($i, $i, $routes);
        }


    }
}
