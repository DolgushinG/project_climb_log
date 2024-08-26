<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use stdClass;
use function Symfony\Component\String\s;

class Grades extends Model
{
    protected $table = 'grades';
    public $timestamps = true;

    protected $casts = [
        'grade_and_amount' =>'json',
        'grade_and_routes' =>'json',
        'rocks_id' =>'json',
    ];

    const BEGINNER = 'beginner';
    const MIDDLE = 'middle';
    const PRO = 'pro';


    public static function getRoutes()
    {

        return [
            ['Категория' => '4', 'Кол-во' => 3, 'Ценность' => 50],
            ['Категория' => '5', 'Кол-во' => 3, 'Ценность' => 100],
            ['Категория' => '5+', 'Кол-во' => 2, 'Ценность' => 150],
            ['Категория' => '6A', 'Кол-во' => 2, 'Ценность' => 200],
            ['Категория' => '6A+', 'Кол-во' => 2, 'Ценность' => 300],
            ['Категория' => '6B', 'Кол-во' => 3, 'Ценность' => 400],
            ['Категория' => '6B+', 'Кол-во' => 2, 'Ценность' => 600],
            ['Категория' => '6C', 'Кол-во' => 2, 'Ценность' => 800],
            ['Категория' => '6C+', 'Кол-во' => 2, 'Ценность' => 1200],
            ['Категория' => '7A', 'Кол-во' => 2, 'Ценность' => 1600],
            ['Категория' => '7A+', 'Кол-во' => 2, 'Ценность' => 2400],
            ['Категория' => '7B', 'Кол-во' => 2, 'Ценность' => 3200],
            ['Категория' => '7B+', 'Кол-во' => 1, 'Ценность' => 4800],
            ['Категория' => '7C', 'Кол-во' => 1, 'Ценность' => 6400],
            ['Категория' => '7C+', 'Кол-во' => 1, 'Ценность' => 9600],
            ['Категория' => '8A', 'Кол-во' => 0, 'Ценность' => 12800],
        ];
    }
    public static function getRoutesOutdoorWithValue()
    {

        return [
            ['Категория боулдеринг' => '4','Ценность боулдеринг' => 5,'Категория трудность' => '4', 'Ценность трудность' => intval(5 + 5 * 0.5)],
            ['Категория боулдеринг' => '5','Ценность боулдеринг' => 10,'Категория трудность' => '5', 'Ценность трудность' => intval(10 + 10 * 0.5)],
            ['Категория боулдеринг' => '5A','Ценность боулдеринг' => 10,'Категория трудность' => '5A', 'Ценность трудность' => intval(10 + 10 * 0.5)],
            ['Категория боулдеринг' => '5B','Ценность боулдеринг' => 12,'Категория трудность' => '5B', 'Ценность трудность' => intval(12 + 12 * 0.5)],
            ['Категория боулдеринг' => '5C','Ценность боулдеринг' => 15,'Категория трудность' => '5C', 'Ценность трудность' => intval(15 + 15 * 0.5)],
            ['Категория боулдеринг' => '5+','Ценность боулдеринг' => 15,'Категория трудность' => '5+', 'Ценность трудность' => intval(15 + 15 * 0.5)],
            ['Категория боулдеринг' => '6A','Ценность боулдеринг' => 20,'Категория трудность' => '6A', 'Ценность трудность' => intval(20 + 20 * 0.5)],
            ['Категория боулдеринг' => '6A+','Ценность боулдеринг' => 30,'Категория трудность' => '6A+', 'Ценность трудность' => intval(30 + 30 * 0.5)],
            ['Категория боулдеринг' => '6B','Ценность боулдеринг' => 40,'Категория трудность' => '6B', 'Ценность трудность' => intval(40 + 40 * 0.5)],
            ['Категория боулдеринг' => '6B+','Ценность боулдеринг' => 60,'Категория трудность' => '6B+', 'Ценность трудность' => intval(60 + 60 * 0.5)],
            ['Категория боулдеринг' => '6C','Ценность боулдеринг' => 80,'Категория трудность' => '6C', 'Ценность трудность' => intval(80 + 80 * 0.5)],
            ['Категория боулдеринг' => '6C+','Ценность боулдеринг' => 120 ,'Категория трудность' => '6C+', 'Ценность трудность' => intval(120 + 120 * 0.5)],
            ['Категория боулдеринг' => '7A','Ценность боулдеринг' => 160  ,'Категория трудность' => '7A', 'Ценность трудность' => intval(160 + 160 * 0.5)],
            ['Категория боулдеринг' => '7A+','Ценность боулдеринг' => 240 ,'Категория трудность' => '7A+', 'Ценность трудность' => intval(240 + 240 * 0.5)],
            ['Категория боулдеринг' => '7B','Ценность боулдеринг' => 320  ,'Категория трудность' => '7B', 'Ценность трудность' => intval(320 + 320 * 0.5)],
            ['Категория боулдеринг' => '7B+','Ценность боулдеринг' => 480 ,'Категория трудность' => '7B+', 'Ценность трудность' => intval(480 + 480 * 0.5)],
            ['Категория боулдеринг' => '7C','Ценность боулдеринг' => 640  ,'Категория трудность' => '7C', 'Ценность трудность' => intval(640 + 640 * 0.5)],
            ['Категория боулдеринг' => '7C+','Ценность боулдеринг' => 960 ,'Категория трудность' => '7C+', 'Ценность трудность' => intval(960 + 960 * 0.5)],
            ['Категория боулдеринг' => '8A','Ценность боулдеринг' => 1200 ,'Категория трудность' => '8A', 'Ценность трудность' => intval(1200 + 1280 * 0.5)],
            ['Категория боулдеринг' => '8A+','Ценность боулдеринг' => 1600,'Категория трудность' => '8A+', 'Ценность трудность' => intval(1600 + 1600 * 0.5)],
            ['Категория боулдеринг' => '8B','Ценность боулдеринг' => 2240 ,'Категория трудность' => '8B', 'Ценность трудность' => intval(2240 + 2240 * 0.5)],
            ['Категория боулдеринг' => '8B+','Ценность боулдеринг' => 2880,'Категория трудность' => '8B+', 'Ценность трудность' => intval(2880 + 2880 * 0.5)],
            ['Категория боулдеринг' => '8C','Ценность боулдеринг' => 4160 ,'Категория трудность' => '8C', 'Ценность трудность' => intval(4160 + 4160 * 0.5)],
            ['Категория боулдеринг' => '8C+','Ценность боулдеринг' => 5420,'Категория трудность' => '8C+', 'Ценность трудность' => intval(5420 + 5420 * 0.5)],
            ['Категория боулдеринг' => '9A','Ценность боулдеринг' => 7980 ,'Категория трудность' => '9A', 'Ценность трудность' => intval(7980 + 7980 * 0.5)],
            ['Категория боулдеринг' => '9A+','Ценность боулдеринг' => 10540,'Категория трудность' => '9A+', 'Ценность трудность' => intval(10540 + 10540 * 0.5)],
            ['Категория боулдеринг' => '9B+','Ценность боулдеринг' => 15560,'Категория трудность' => '9B+', 'Ценность трудность' => intval(15560 + 15560 * 0.5)],
        ];
    }

    public static function getRoutesWithZone()
    {

        return [
            ['Категория' => '4', 'Кол-во' => 3, 'Ценность' => 50, 'Ценность зоны' => 25],
            ['Категория' => '5', 'Кол-во' => 3, 'Ценность' => 100, 'Ценность зоны' => 50],
            ['Категория' => '5+', 'Кол-во' => 2, 'Ценность' => 150, 'Ценность зоны' => 75],
            ['Категория' => '6A', 'Кол-во' => 2, 'Ценность' => 200, 'Ценность зоны' => 100],
            ['Категория' => '6A+', 'Кол-во' => 2, 'Ценность' => 300, 'Ценность зоны' => 150],
            ['Категория' => '6B', 'Кол-во' => 3, 'Ценность' => 400, 'Ценность зоны' => 200],
            ['Категория' => '6B+', 'Кол-во' => 2, 'Ценность' => 600, 'Ценность зоны' => 300],
            ['Категория' => '6C', 'Кол-во' => 2, 'Ценность' => 800, 'Ценность зоны' => 400],
            ['Категория' => '6C+', 'Кол-во' => 2, 'Ценность' => 1200, 'Ценность зоны' => 600],
            ['Категория' => '7A', 'Кол-во' => 2, 'Ценность' => 1600, 'Ценность зоны' => 800],
            ['Категория' => '7A+', 'Кол-во' => 2, 'Ценность' => 2400, 'Ценность зоны' => 1200],
            ['Категория' => '7B', 'Кол-во' => 2, 'Ценность' => 3200, 'Ценность зоны' => 1600],
            ['Категория' => '7B+', 'Кол-во' => 1, 'Ценность' => 4800, 'Ценность зоны' => 2400],
            ['Категория' => '7C', 'Кол-во' => 1, 'Ценность' => 6400, 'Ценность зоны' => 3200],
            ['Категория' => '7C+', 'Кол-во' => 1, 'Ценность' => 9600, 'Ценность зоны' => 4800],
            ['Категория' => '8A', 'Кол-во' => 0, 'Ценность' => 12800, 'Ценность зоны' => 6400],
        ];
    }
    public static function settings_routes($amount_routes, $routes)
    {
        $res = [];
        $route_id = 1;
        foreach ($routes as $route) {
            if($route['amount'] != 0) {
                for ($i = 1; $i <= $route['amount']; $i++) {
                    if ($route_id <= $amount_routes) {
                        if ($amount_routes <= 20) {
                            if ($route['grade'] != '8A') {
                                if (isset($route['value'])) {
                                    $grades_with_value_flash = Grades::grades_with_value_flash(20);
                                    $grades = Grades::grades();
                                    $index = array_search($route['grade'], $grades);
                                    $flash_value = $grades_with_value_flash[$index];
                                    if(isset($route['zone'])){
                                        $res[] = array('owner_id' => $route['owner_id'], 'event_id' => $route['event_id'], 'route_id' => $route_id,'zone' => $route['zone'], 'grade' => $route['grade'], 'value' => $route['value'], 'flash_value' => $flash_value);
                                    } else {
                                        $res[] = array('owner_id' => $route['owner_id'], 'event_id' => $route['event_id'], 'route_id' => $route_id, 'grade' => $route['grade'], 'value' => $route['value'], 'flash_value' => $flash_value);
                                    }
                                } else {
                                    $res[] = array('owner_id' => $route['owner_id'], 'event_id' => $route['event_id'], 'route_id' => $route_id, 'grade' => $route['grade']);
                                }
                                $route_id++;
                            }
                        } else {
                            if (isset($route['value'])) {
                                $grades_with_value_flash = Grades::grades_with_value_flash(20);
                                $grades = Grades::grades();
                                $index = array_search($route['grade'], $grades);
                                $flash_value = $grades_with_value_flash[$index];
                                if(isset($route['zone'])){
                                    $res[] = array('owner_id' => $route['owner_id'], 'event_id' => $route['event_id'], 'route_id' => $route_id,'zone' => $route['zone'], 'grade' => $route['grade'], 'value' => $route['value'], 'flash_value' => $flash_value);
                                } else {
                                    $res[] = array('owner_id' => $route['owner_id'], 'event_id' => $route['event_id'], 'route_id' => $route_id, 'grade' => $route['grade'], 'value' => $route['value'], 'flash_value' => $flash_value);
                                }
                            } else {
                                $res[] = array('owner_id' => $route['owner_id'], 'event_id' => $route['event_id'], 'route_id' => $route_id, 'grade' => $route['grade']);
                            }
                            $route_id++;
                        }
                    }
                }
            }
        }
        DB::table('routes')->insert($res);
    }

    public static function find_number_for_divider($number)
    {
        $divider = 2; // Начинаем с делителя равного 2
        while ($number / $divider > 16) {
            $divider++;
        }
        return $divider;
    }
    public function event()
    {
        return $this->belongsTo(Event::class)->where('active', 1);
    }

    public static function colors()
    {
        return [
            'not_set_color' => 'Не установлен',
            '#FF0000' => 'Красный',
            '#FF4500' => 'Оранжевый',
            '#FFD700' => 'Золотой',
            '#FFFF00' => 'Жёлтый',
            '#ADFF2F' => 'Зелёный жёлтый',
            '#00FF00' => 'Лаймовый',
            '#32CD32' => 'Лаймовый зелёный',
            '#008000' => 'Зелёный',
            '#006400' => 'Тёмно-зелёный',
            '#00FA9A' => 'Морской зелёный',
            '#00FFFF' => 'Циан',
            '#40E0D0' => 'Бирюзовый',
            '#4682B4' => 'Стальной синий',
            '#0000FF' => 'Синий',
            '#00008B' => 'Тёмно-синий',
            '#8A2BE2' => 'Синевато-фиолетовый',
            '#A020F0' => 'Фиолетовый',
            '#FF00FF' => 'Фуксия',
            '#DDA0DD' => 'Пастельный фиолетовый',
            '#C71585' => 'Малиновый',
            '#C0C0C0' => 'Серебристый',
            '#808080' => 'Серый',
            '#A9A9A9' => 'Тёмно-серый',
            '#800000' => 'Коричневый',
            '#8B4513' => 'Сэд',
            '#D2691E' => 'Шоколадный',
            '#F4A460' => 'Светло-коричневый',
            '#808000' => 'Оливковый',
            '#6B8E23' => 'Оливковый зелёный',
            '#BDB76B' => 'Тёмно-зелёный оливковый',
            '#F0E68C' => 'Хаки',
            '#FF6347' => 'Томатный',
            '#FF7F50' => 'Коралловый',
            '#FF1493' => 'Горячий розовый',
            '#FFC0CB' => 'Розовый',
            '#D3D3D3' => 'Светло-серый',
            '#DCDCDC' => 'Очень светло-серый',
            '#FFFFFF' => 'Белый',
            '#F5F5F5' => 'Светло-серый белый',
            '#000000' => 'Чёрный',
            '#A52A2A' => 'Коричневый',
            '#F5DEB3' => 'Пшеничный',
            '#B0C4DE' => 'Светло-голубой',
            '#E6E6FA' => 'Лаванда'
        ];
    }

    /**
     * @return array[]
     */
    public static function getGrades(): array
    {
        return ['4' => '4','5' => '5','5A' => '5A','5B' => '5B', '5C' => '5C','5+' => '5+','6A' => '6A','6A+' => '6A+', '6B' => '6B', '6B+' => '6B+','6C' => '6C',
            '6C+' => '6C+','7A' => '7A','7A+' => '7A+','7B' => '7B','7B+' => '7B+','7C' => '7C','7C+' => '7C+','8A' => '8A',
            '8A+' => '8A+','8B' => '8B', '8B+' => '8B+', '8C' => '8C','8C+' => '8C+','9A' => '9A','9A+' => '9A+','9B' => '9B','9B+' => '9B+'];
    }

    /**
     * @return array[]
     */
    public static function grades(): array
    {
        return ['4', '5', '5+', '6A','6A+', '6B', '6B+', '6C', '6C+', '7A', '7A+', '7B', '7B+', '7C', '7C+', '8A'];
    }

    /**
     * @return array[]
     */
    public static function outdoor_grades(): array
    {
        return ['4', '5', '5A','5B','5C','5+', '6A','6A+', '6B', '6B+', '6C', '6C+', '7A', '7A+', '7B', '7B+', '7C', '7C+',  '8A', '8A+','8B','8B+','8C','8C+','9A','9A+','9B','9B+','9C'];
    }

    /**
     * @return array[]
     */
    public static function outdoor_grades_with_value_flash($start_low_grade): array
    {

        $step = 10;
        $main_digit = $start_low_grade;
        $res = [];
        $count = 1;
        $len = count(self::outdoor_grades());
        for ($i = 0; $i < $len; $i++) {
            $res[] = $main_digit;
            $main_digit += $step;
            $count++;

            if ($count == 3) {
                $step *= 2;
                $count = 1;
            }
        }

        return $res;
    }

    /**
     * @return array[]
     */
    public static function grades_with_value_flash($start_low_grade): array
    {

        $step = 10;
        $main_digit = $start_low_grade;
        $res = [];
        $count = 1;
        $len = count(self::grades());
        for ($i = 0; $i < $len; $i++) {
            $res[] = $main_digit;
            $main_digit += $step;
            $count++;

            if ($count == 3) {
                $step *= 2;
                $count = 1;
            }
        }

        return $res;
    }


    public static function getAttemptFromGrades($grade, $type_participant)
    {

        switch ($type_participant){
            case self::BEGINNER:
                return self::getGradesPartBeginner($grade);
            case self::MIDDLE:
                return self::getGradesPartMiddle($grade);
            case self::PRO:
                return self::getGradesPartPro($grade);
        }
    }

    public static function getGradesPartBeginner($grade): int
    {
        if(in_array($grade, ['4', '5', '5+', '6A','6A+', '6B', '6B+'])){
            $attempt = rand(0,2);
        }
        if(in_array($grade, ['6C', '6C+', '7A', '7A+', '7B'])){
            $attempt = 0;
        }
        if(in_array($grade, ['7B+', '7C', '7C+', '8A'])){
            $attempt = 0;
        }

        return $attempt;
    }

    public static function getGradesPartMiddle($grade): int
    {
        if(in_array($grade, ['4', '5', '5+', '6A', '6A+', '6B', '6B+'])){
            $attempt = rand(1,2);
        }
        if(in_array($grade, ['6C', '6C+', '7A', '7A+', '7B'])){
            $attempt = rand(0,2);
        }
        if(in_array($grade, ['7B+', '7C', '7C+', '8A'])){
            $attempt = 0;
        }

        return $attempt;
    }
    public static function getGradesPartPro($grade): int
    {
        if(in_array($grade, ['4', '5', '5+', '6A','6A+', '6B', '6B+'])){
            $attempt = rand(1,2);
        }
        if(in_array($grade, ['6C', '6C+', '7A', '7A+', '7B'])){
            $attempt = rand(1,2);
        }
        if(in_array($grade, ['7B+', '7C', '7C+', '8A'])){
            $attempt = rand(0,2);
        }

        return $attempt;
    }

    public static function findMaxIndices($searchArray, $targetArray, $count) {
        $maxIndices = [];
        $indexesFound = 0;

        foreach ($targetArray as $searchItem) {
            $index = array_search($searchItem, $searchArray);
            if ($index !== false) {
                if ($indexesFound < $count) {
                    $maxIndices[] = $index;
                    $indexesFound++;
                } else {
                    sort($maxIndices); // Сортировка массива индексов по возрастанию
                    if ($index > $maxIndices[0]) {
                        $maxIndices[0] = $index;
                    }
                }
            }
        }
        $res = [];
        foreach ($maxIndices as $index){
            $res[] = self::grades()[$index];
        }
        return $res;
    }

}
