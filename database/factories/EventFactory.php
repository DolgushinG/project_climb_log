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
            ['Категория' => '5', 'Кол-во' => 4, 'Ценность' => 100],
            ['Категория' => '5+', 'Кол-во' => 4, 'Ценность' => 150],
            ['Категория' => '6A', 'Кол-во' => 4, 'Ценность' => 200],
            ['Категория' => '6A+', 'Кол-во' => 3, 'Ценность' => 250],
            ['Категория' => '6B', 'Кол-во' => 3, 'Ценность' => 300],
            ['Категория' => '6B+', 'Кол-во' => 3, 'Ценность' => 350],
            ['Категория' => '6C', 'Кол-во' => 2, 'Ценность' => 400],
            ['Категория' => '6C+', 'Кол-во' => 1, 'Ценность' => 450],
            ['Категория' => '7A', 'Кол-во' => 1, 'Ценность' => 500],
            ['Категория' => '7A+', 'Кол-во' => 1, 'Ценность' => 550],
            ['Категория' => '7B', 'Кол-во' => 1, 'Ценность' => 600],
            ['Категория' => '7B+', 'Кол-во' => 1, 'Ценность' => 650],
            ['Категория' => '7C', 'Кол-во' => 1, 'Ценность' => 700],
            ['Категория' => '7C+', 'Кол-во' => 1, 'Ценность' => 750],
            ['Категория' => '8A', 'Кол-во' => 0, 'Ценность' => 800],
        ];


        $cities = array(
            "Москва",
            "Санкт-Петербург",
            "Новосибирск",
            "Екатеринбург",
            "Нижний Новгород",
            "Казань",
            "Челябинск",
            "Омск",
            "Самара",
            "Ростов-на-Дону",
            "Уфа",
            "Красноярск",
            "Пермь",
            "Воронеж",
            "Волгоград"
        );


        $climbingGyms = array(
            "АльпикаМайкоп",
            "Большой стон",
            "Красный камень",
            "Скалодром КубГУ",
            "ГрандВолна",
            "Спелеоклуб Гора",
            "Ревуцкие стены",
            "Скалодром Вертикаль",
            "Рисованный Камень",
            "ЛазерМаяк",
            "Скалодром ОККБ-БПЛА",
            "Столбы",
            "Спелеоцентр Сибирь",
            "Точка Кипения",
            "Башни Пскова"
        );




        return [
            'image' => $this->faker->randomElement(['images/20231115_cea82537af86871a32344dcd5c6a23ba.jpeg','images/vT94mFyT9xU.jpg']),
            'active' => true,
            'start_date' => $this->faker->date(),
            'end_date' => $this->faker->date(),
            'start_time' => $this->faker->time(),
            'end_time' => $this->faker->time(),
            'description' => $this->faker->paragraph(20),
            'address' => $this->faker->address(),
            'grade_and_amount' => $routes,
            'city' => $this->faker->randomElement($cities),
            'title' => $this->faker->word(),
            'title_eng' => $this->faker->word(),
            'subtitle' => $this->faker->word(),
            'climbing_gym_name' => $this->faker->randomElement($climbingGyms),
            'climbing_gym_name_eng' => $this->faker->word(),
            'link' => $this->faker->url(),
            'count_routes' => 30,
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
