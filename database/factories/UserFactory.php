<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{

    protected $model = User::class;
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {

        $firstname = $this->faker->firstName();
        $lastname = $this->faker->lastName();
        $middlename = $firstname.' '.$lastname;
        return [
            'firstname' => $firstname,
            'lastname' => $lastname,
            'middlename' => $middlename,
            'year' => '1920',
            'city' => $this->faker->city(),
            'team' => $this->faker->word(),
            'skill' => null,
            'sport_category' => null,
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
        ];

    }
    public function withGender($gender)
    {
        return $this->state([
            'gender' => $gender,
        ]);
    }
    public function withCategory($id)
    {
        return $this->state([
            'category' => $id,
        ]);
    }
    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function unverified()
    {
        return $this->state(function (array $attributes) {
            return [
                'email_verified_at' => null,
            ];
        });
    }
}
