<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $routes = array(
            array('Категория' => '5', 'Кол-во' => 3, 'Ценность' => 100),
            array('Категория' => '5+', 'Кол-во' => 3, 'Ценность' => 150),
            array('Категория' => '6A', 'Кол-во' => 3, 'Ценность' => 200),
            array('Категория' => '6A+', 'Кол-во' => 3, 'Ценность' => 250),
            array('Категория' => '6B', 'Кол-во' => 3, 'Ценность' => 300),
            array('Категория' => '6B+', 'Кол-во' => 2, 'Ценность' => 350),
            array('Категория' => '6C', 'Кол-во' => 2, 'Ценность' => 400),
            array('Категория' => '6C+', 'Кол-во' => 2, 'Ценность' => 450),
            array('Категория' => '7A', 'Кол-во' => 2, 'Ценность' => 500),
            array('Категория' => '7A+', 'Кол-во' => 2, 'Ценность' => 550),
            array('Категория' => '7B', 'Кол-во' => 2, 'Ценность' => 600),
            array('Категория' => '7B+', 'Кол-во' => 1, 'Ценность' => 650),
            array('Категория' => '7C', 'Кол-во' => 1, 'Ценность' => 700),
            array('Категория' => '7C+', 'Кол-во' => 1, 'Ценность' => 750),
            array('Категория' => '8A', 'Кол-во' => 0, 'Ценность' => 800),
        );
        $routes_json = json_encode($routes);
        return [
            'document' => 'images/0a8453e1f407fec06837f7679235a455.jpg',
            'image' => 'images/0a8453e1f407fec06837f7679235a455.jpg',
            'active' => false,
            'start_date' => $this->faker->date(),
            'end_date' => $this->faker->date(),
            'start_time' => $this->faker->time(),
            'end_time' => $this->faker->time(),
            'description' => $this->faker->paragraph(5),
            'address' => $this->faker->address(),
            'grade_and_amount' => $routes_json,
            'city' => $this->faker->city(),
            'title' => $this->faker->word(),
            'title_eng' => $this->faker->word(),
            'subtitle' => $this->faker->word(),
            'climbing_gym_name' => $this->faker->word(),
            'climbing_gym_name_eng' => $this->faker->word(),
            'link' => $this->faker->url(),
            'count_routes' => 50,
            'mode' => $this->faker->randomElement([1, 2]),
            'owner_id' => $this->faker->randomElement([2, 3]),
        ];
    }
}
