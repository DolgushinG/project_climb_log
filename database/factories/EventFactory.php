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
        $date = $this->faker->date();
        return [
            'image' => $this->faker->randomElement(['images/20231115_cea82537af86871a32344dcd5c6a23ba.jpeg','images/vT94mFyT9xU.jpg']),
            'active' => true,
            'is_registration_state' => true,
            'is_send_result_state' => true,
            'start_date' => $date,
            'end_date' => $this->faker->date(),
            'description' => $this->faker->paragraph(20),
            'contact' => '+7(932)-782-22-11',
            'address' => $this->faker->address(),
            'title' => 'competition',
            'title_eng' => 'competition',
            'subtitle' => $this->faker->word(),
            'link' => '/event/'.$date.'/admin/competition',
            'admin_link' => '/admin/event/'.$date.'/admin/competition',
            'link_payment' => "https://www.tinkoff.ru/cf/1ZyTiSRkXmZ",
            'img_payment' => "images/qr.png",
            'info_payment' => $this->faker->paragraph(20),
            'amount_routes_in_semifinal' => $this->faker->randomElement([4,5]),
            'amount_routes_in_final' => 4,
            'amount_start_price' => 1800,
            'amount_the_best_participant' => $this->faker->randomElement([6,8,10,20]),
            'categories' => $this->faker->randomElement([['Новичок', 'Общий зачет'], ['Новичок', 'Любители', 'Спортсмены']]),
            'count_routes' => 30,
            'is_public' => true,
            'is_need_pay_for_reg' => false,
        ];
    }
    public function withOwnerId($value)
    {
        return $this->state([
            'owner_id' => $value,
        ]);
    }
    public function withSemiFinal($value)
    {
        return $this->state([
            'is_semifinal' => $value,
        ]);
    }

    public function is_additional_final($value)
    {
        return $this->state([
            'is_additional_final' => $value,
        ]);
    }
    public function is_qualification_counting_like_final($value)
    {
        return $this->state([
            'is_qualification_counting_like_final' => $value,
        ]);
    }
    public function amount_point_flash($value)
    {
        return $this->state([
            'amount_point_flash' => $value,
        ]);
    }
    public function amount_point_redpoint($value)
    {
        return $this->state([
            'amount_point_redpoint' => $value,
        ]);
    }
    public function mode($value)
    {
        return $this->state([
            'mode' => $value,
        ]);
    }
    public function mode_amount_routes($value)
    {
        return $this->state([
            'mode_amount_routes' => $value,
        ]);
    }
    public function count_routes($value)
    {
        return $this->state([
            'count_routes' => $value,
        ]);
    }
    public function is_input_birthday($value)
    {
        return $this->state([
            'is_input_birthday' => $value,
        ]);
    }
    public function is_need_sport_category($value)
    {
        return $this->state([
            'is_need_sport_category' => $value,
        ]);
    }
    public function withClimbingGym($value)
    {
        return $this->state([
            # рандомные названия
//            'climbing_gym_name' => $this->climbingGyms[$value],
//            'climbing_gym_name_eng' => (new \App\Models\Event)->translate_to_eng($this->climbingGyms[$value]),
        # фикс админ
            'climbing_gym_name' => 'admin',
            'climbing_gym_name_eng' => 'admin',
        ]);
    }

    public function withCity($value)
    {
        return $this->state([
            'city' => $this->cities[$value],
        ]);
    }

}
