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
