<?php

namespace Database\Seeders;

use App\Models\Event;
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
        $routes = array(
            array('Категория' => '5', 'Кол-во' => 3, 'Ценность' => 100),
            array('Категория' => '5+', 'Кол-во' => 2, 'Ценность' => 150),
            array('Категория' => '6A', 'Кол-во' => 2, 'Ценность' => 200),
            array('Категория' => '6A+', 'Кол-во' => 2, 'Ценность' => 250),
            array('Категория' => '6B', 'Кол-во' => 2, 'Ценность' => 300),
            array('Категория' => '6B+', 'Кол-во' => 2, 'Ценность' => 350),
            array('Категория' => '6C', 'Кол-во' => 2, 'Ценность' => 400),
            array('Категория' => '6C+', 'Кол-во' => 2, 'Ценность' => 450),
            array('Категория' => '7A', 'Кол-во' => 2, 'Ценность' => 500),
            array('Категория' => '7A+', 'Кол-во' => 2, 'Ценность' => 550),
            array('Категория' => '7B', 'Кол-во' => 2, 'Ценность' => 600),
            array('Категория' => '7B+', 'Кол-во' => 1, 'Ценность' => 650),
            array('Категория' => '7C', 'Кол-во' => 1, 'Ценность' => 700),
            array('Категория' => '7C+', 'Кол-во' => 1, 'Ценность' => 750),
        );
        for($i = 1; $i <= AdminRoleAndUsersSeeder::COUNT_EVENTS; $i++){
            Event::generation_route($i, $i, $routes);
        }


    }
}
