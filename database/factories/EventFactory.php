<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\Grades;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventFactory extends Factory
{

    public $cities = array(
        "Новосибирск",
        "Екатеринбург",
        "Москва",
        "Санкт-Петербург",
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
        "Волгоград",
        "Тула",
        "Екатеринбург"
    );


    public $climbingGyms = array(
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
        "Башни Пскова",
        "Climb lab",
        "Bigwall",
        "Северная стена"
    );

    protected $model = Event::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {

//        $transfer_to_text_category = [
//            ['Категория участника' => '0', 'Кол-во трасс для перевода'=> '2','В какую категорию переводить' => '1', 'От какой категории будет перевод'=> '6C'],
//            ['Категория участника' => '1', 'Кол-во трасс для перевода'=> '2','В какую категорию переводить' => '2', 'От какой категории будет перевод'=> '7B'],
//        ];
        $routes = Grades::getRoutes();
        return [
            'image' => $this->faker->randomElement(['images/20231115_cea82537af86871a32344dcd5c6a23ba.jpeg','images/vT94mFyT9xU.jpg']),
            'active' => true,
            'start_date' => $this->faker->date(),
            'end_date' => $this->faker->date(),
            'start_time' => $this->faker->time(),
            'end_time' => $this->faker->time(),
            'description' => $this->faker->paragraph(20),
            'contact' => '+7(932)-782-22-11',
            'address' => $this->faker->address(),
            'grade_and_amount' => $routes,
            'title' => $this->faker->word(),
            'title_eng' => $this->faker->word(),
            'subtitle' => $this->faker->word(),
            'link' => $this->faker->url(),
            'link_payment' => "https://www.tinkoff.ru/cf/1ZyTiSRkXmZ",
            'img_payment' => "images/qr.png",
            'info_payment' => $this->faker->paragraph(20),
            'amount_routes_in_semifinal' => $this->faker->randomElement([4,5]),
            'amount_routes_in_final' => 4,
            'amount_start_price' => 1800,
            'categories' => $this->faker->randomElement([['Новичок', 'Общий зачет'], ['Новичок', 'Любители', 'Спортсмены']]),
//            'transfer_to_next_category' => $transfer_to_text_category,
            'count_routes' => 30,
            'mode' => $this->faker->randomElement([1, 2]),
            'mode_amount_routes' => 15,
        ];
    }
    public function withOwnerId($owner_id)
    {
        return $this->state([
            'owner_id' => $owner_id,
        ]);
    }
    public function withSemiFinal($is_semifinal)
    {
        return $this->state([
            'is_semifinal' => $is_semifinal,
        ]);
    }
    public function withClimbingGym($index)
    {
        return $this->state([
            'climbing_gym_name' => $this->climbingGyms[$index],
            'climbing_gym_name_eng' => (new \App\Models\Event)->translate_to_eng($this->climbingGyms[$index]),
        ]);
    }

    public function withCity($index)
    {
        return $this->state([
            'city' => $this->cities[$index],
        ]);
    }

}
