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
            ['Категория' => '4', 'Ценность' => 50],
            ['Категория' => '5', 'Ценность' => 100],
            ['Категория' => '5+', 'Ценность' => 150],
            ['Категория' => '6A', 'Ценность' => 200],
            ['Категория' => '6A+', 'Ценность' => 300],
            ['Категория' => '6B', 'Ценность' => 400],
            ['Категория' => '6B+', 'Ценность' => 600],
            ['Категория' => '6C', 'Ценность' => 800],
            ['Категория' => '6C+', 'Ценность' => 1200],
            ['Категория' => '7A', 'Ценность' => 1600],
            ['Категория' => '7A+', 'Ценность' => 2400],
            ['Категория' => '7B', 'Ценность' => 3200],
            ['Категория' => '7B+', 'Ценность' => 4800],
            ['Категория' => '7C', 'Ценность' => 6400],
            ['Категория' => '7C+', 'Ценность' => 9600],
            ['Категория' => '8A', 'Ценность' => 12800],
            ['Категория' => '8A+', 'Ценность' => 16000],
            ['Категория' => '8B', 'Ценность' => 22400],
            ['Категория' => '8B+', 'Ценность' => 28800],
            ['Категория' => '8C', 'Ценность' => 41600],
            ['Категория' => '8C+', 'Ценность' => 54200],
            ['Категория' => '9A', 'Ценность' => 79800],
            ['Категория' => '9A+', 'Ценность' => 105400],
            ['Категория' => '9B+', 'Ценность' => 155600],
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


    /**
     * @return array[]
     */
    public static function getGrades(): array
    {
        return ['4' => '4','5' => '5', '5+' => '5+','6A' => '6A','6A+' => '6A+', '6B' => '6B', '6B+' => '6B+','6C' => '6C',
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
