<?php

namespace Database\Factories;

use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventFactory extends Factory
{

    protected $model = Event::class;
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $routes = [
            ['Категория' => '5', 'Кол-во' => 5, 'Ценность' => 100],
            ['Категория' => '5+', 'Кол-во' => 5, 'Ценность' => 150],
            ['Категория' => '6A', 'Кол-во' => 5, 'Ценность' => 200],
            ['Категория' => '6A+', 'Кол-во' => 4, 'Ценность' => 250],
            ['Категория' => '6B', 'Кол-во' => 4, 'Ценность' => 300],
            ['Категория' => '6B+', 'Кол-во' => 4, 'Ценность' => 350],
            ['Категория' => '6C', 'Кол-во' => 3, 'Ценность' => 400],
            ['Категория' => '6C+', 'Кол-во' => 3, 'Ценность' => 450],
            ['Категория' => '7A', 'Кол-во' => 3, 'Ценность' => 500],
            ['Категория' => '7A+', 'Кол-во' => 3, 'Ценность' => 550],
            ['Категория' => '7B', 'Кол-во' => 3, 'Ценность' => 600],
            ['Категория' => '7B+', 'Кол-во' => 3, 'Ценность' => 650],
            ['Категория' => '7C', 'Кол-во' => 3, 'Ценность' => 700],
            ['Категория' => '7C+', 'Кол-во' => 2, 'Ценность' => 750],
            ['Категория' => '8A', 'Кол-во' => 0, 'Ценность' => 800],
        ];
        return [
            'document' => 'images/0a8453e1f407fec06837f7679235a455.jpg',
            'image' => 'images/0a8453e1f407fec06837f7679235a455.jpg',
            'active' => true,
            'start_date' => $this->faker->date(),
            'end_date' => $this->faker->date(),
            'start_time' => $this->faker->time(),
            'end_time' => $this->faker->time(),
            'description' => $this->faker->paragraph(5),
            'address' => $this->faker->address(),
            'grade_and_amount' => $routes,
            'city' => $this->faker->city(),
            'title' => $this->faker->word(),
            'title_eng' => $this->faker->word(),
            'subtitle' => $this->faker->word(),
            'climbing_gym_name' => $this->faker->word(),
            'climbing_gym_name_eng' => $this->faker->word(),
            'link' => $this->faker->url(),
            'count_routes' => 50,
            'mode' => $this->faker->randomElement([1, 2]),
        ];
    }
    public function withOwnerId($owner_id)
    {
        return $this->state([
            'owner_id' => $owner_id,
        ]);
    }
}
