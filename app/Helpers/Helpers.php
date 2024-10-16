<?php

namespace App\Helpers;

use App\Models\Event;
use App\Models\ParticipantCategory;
use App\Models\Set;
use Carbon\Carbon;
use DateInterval;
use DatePeriod;
use DateTime;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use function Symfony\Component\String\s;

class Helpers
{


    public static function clear_cache(Event $event)
    {
        $categories = ParticipantCategory::where('event_id', $event->id)->get();
        foreach ($categories as $category) {
            Cache::forget('result_male_cache_' . $category->category.'_event_id_'.$event->id);
            if($event->is_open_main_rating){
                Cache::forget('global_result_male_cache_' . $category->category.'_event_id_'.$event->id);
                Cache::forget('global_result_female_cache_' . $category->category.'_event_id_'.$event->id);
            }
            Cache::forget('result_female_cache_' . $category->category.'_event_id_'.$event->id);
        }
        Cache::forget('result_analytics_cache_event_id_'.$event->id);
        Cache::forget('result_male_analytics_cache_event_id_'.$event->id);
        Cache::forget('result_female_analytics_cache_event_id_'.$event->id);
    }
    public static function getContrastColor($hexColor)
    {
        // hexColor RGB
        $R1 = hexdec(substr($hexColor, 1, 2));
        $G1 = hexdec(substr($hexColor, 3, 2));
        $B1 = hexdec(substr($hexColor, 5, 2));

        // Black RGB
        $blackColor = "#000000";
        $R2BlackColor = hexdec(substr($blackColor, 1, 2));
        $G2BlackColor = hexdec(substr($blackColor, 3, 2));
        $B2BlackColor = hexdec(substr($blackColor, 5, 2));

        // Calc contrast ratio
        $L1 = 0.2126 * pow($R1 / 255, 2.2) +
            0.7152 * pow($G1 / 255, 2.2) +
            0.0722 * pow($B1 / 255, 2.2);

        $L2 = 0.2126 * pow($R2BlackColor / 255, 2.2) +
            0.7152 * pow($G2BlackColor / 255, 2.2) +
            0.0722 * pow($B2BlackColor / 255, 2.2);

        $contrastRatio = 0;
        if ($L1 > $L2) {
            $contrastRatio = (int)(($L1 + 0.05) / ($L2 + 0.05));
        } else {
            $contrastRatio = (int)(($L2 + 0.05) / ($L1 + 0.05));
        }

        // If contrast is more than 5, return black color
        if ($contrastRatio > 5) {
            return '#000000';
        } else {
            // if not, return white color.
            return '#FFFFFF';
        }
    }

    public static function is_valid_year_for_event($event_id, $number_set, $in_year)
    {
        $set = Set::where('event_id', $event_id)->where('number_set', $number_set)->first();
        $event = Event::find($event_id);
        if($set && $event->is_input_birthday){

            $allow_years = $set->allow_years;
            $birthYear = (int)date('Y', strtotime($in_year));
            // Проверяем, есть ли год в массиве
            if($allow_years && $birthYear){
                return in_array($birthYear, $allow_years);
            } else {
                return true;
            }
        }
        return true;
    }

    public static function valid_email($email)
    {
        if(str_contains($email, 'telegram')){
            return false;
        }
        if(str_contains($email, 'vkontakte')){
            return false;
        }
        if(str_contains($email, 'group')){
            return false;
        }
        return true;
    }

    public static function remove_key($array, $key)
    {
        foreach (array_keys($array) as $index)
        {
            unset($array[$index][$key]);
        }
        return $array;
    }

    public static function get_year($year)
    {
        if(!$year){
            return '';
        }
        $data_time_year = new DateTime($year);
        return $data_time_year->format('Y');


    }
    public static function calculate_age($birthdate_str) {
        // Преобразуем строку в объект даты
        if(!$birthdate_str){
            return '';
        }
        $birthdate = new DateTime($birthdate_str);

        // Текущая дата
        $today = new DateTime('today');

        // Вычисляем разницу в годах
        $age = $today->diff($birthdate)->y;
        return $age.' '.self::get_age_suffix($age);
    }
    public static function get_age_suffix($age) {
        // Определяем правильное склонение
        if ($age % 10 == 1 && $age % 100 != 11) {
            return "год";
        } elseif ($age % 10 >= 2 && $age % 10 <= 4 && ($age % 100 < 10 || $age % 100 >= 20)) {
            return "года";
        } else {
            return "лет";
        }
    }
    public static function formating_string($string)
    {
        return preg_replace('/[^ \w-]/', '', $string);
    }
    public static function get_procent($max, $current)
    {
        $a = $max;
        $b = $current;
        if ($a === $b) {
            $percent = 0;
        } elseif ($a < $b) {
            $diff = $b - $a;
            $percent = $diff / $b * 100;
        } else {
            $diff = $a - $b;
            $percent = $diff / $a * 100;
        }
        return intval($percent);
    }
    public static function find_max_attempts($amount_try_top, $amount_try_zone)
    {
        if($amount_try_top){
            $result_for_compare = $amount_try_top;
        } else if ($amount_try_zone){
            $result_for_compare = $amount_try_zone;
        } else {
            return 0;
        }
        return $result_for_compare;
    }

    public static function validate_amount_sum_top_and_zone_and_attempts($all_attempts, $amount_try_top, $amount_try_zone)
    {
        if($amount_try_top == 0 && $amount_try_zone >= 0){
            return false;
        }

        return intval($all_attempts) != self::find_max_attempts($amount_try_top, $amount_try_zone);
    }
    public static function validate_amount_top_and_zone($amount_top, $amount_zone)
    {
        return $amount_top == 1 && $amount_zone == 0;
    }
    public static function validate_amount_try_top_and_zone($amount_try_top, $amount_try_zone)
    {
        # Если нет пролаза на топ, то правило не работает
        if(intval($amount_try_top) == 0 && intval($amount_try_zone) > 0){
            return false;
        }
        return intval($amount_try_zone) > intval($amount_try_top);
    }
    public static function save_qr_code($event)
    {
        $link = route('listRoutesEvent', [$event->id]);
        $image = QrCode::format('png')
            ->size(150)
            ->generate($link);
        $output_file = '/img/qr-code/img-' . time() . '.png';
        Storage::disk('admin')->put($output_file, $image); //storage/app/public/img/qr-code/img-1557309130.png
        return 'storage'.$output_file;
    }

    public static function isRussianOnly($string, $event) {

        if(!$string){
            return false;
        }
        if($event->is_need_to_russian_names){
            // Регулярное выражение для проверки на русские символы и пробелы
            return preg_match('/^[а-яА-ЯёЁ\s]+$/u', $string);
        } else {
            return true;
        }
    }

    /**
     * @throws \Exception
     */
    public static function getDatesByDayOfWeek($start_date, $end_date) {
        // Преобразуем входные строки в объекты DateTime
        $start = new DateTime($start_date);
        $end = new DateTime($end_date);
        // Добавляем 1 день к конечной дате, чтобы включить последний день
        $end->modify('+1 day');

        // Инициализируем пустой массив для хранения дат по дням недели
        $dates_by_day_of_week = array(
            'Monday' => null,
            'Tuesday' => null,
            'Wednesday' => null,
            'Thursday' => null,
            'Friday' => null,
            'Saturday' => null,
            'Sunday' => null
        );

        // Перебираем каждый день между начальной и конечной датами
        $interval = new DateInterval('P1D');
        $period = new DatePeriod($start, $interval, $end);

        foreach ($period as $date) {
            // Получаем название дня недели на английском
            $day_of_week = $date->format('l');
            // Добавляем текущую дату в массив соответствующего дня недели
            $dates_by_day_of_week[$day_of_week] = $date->format('d-m-Y');
        }

        // Удаляем пустые дни недели
        $dates_by_day_of_week = array_filter($dates_by_day_of_week);
        return $dates_by_day_of_week;
    }


    public static function arrayValuesRecursive($arr)
    {
        $result = [];
        foreach ($arr as $res){
            foreach ($res as $r){
                $result[] = $r;
            }
        }
        return $result;
    }

    public static function echo_days($days) {
        if($days % 10 == 1 && ($days % 100 > 19 || $days < 11 )) {
            return "день";
        } else if ($days % 10 > 1 && $days % 10 < 5 && ($days % 100 >19 || $days < 11 )) {
            return "дня";
        } else {
            return "дней";
        }
    }

    public static function custom_response($message, $status=false)
    {
        $response = [
            'status'  => $status,
            'message' => $message,
        ];
        return response()->json($response);
    }

    public static function is_categories_events_same($events)
    {
        $list_for_compare = [];
        foreach ($events as $event_id){
            if($event_id){
                $event = Event::find($event_id);
                $list_for_compare[] = array('event_id' => $event_id, 'count' => count($event->categories), 'categories' => $event->categories);
            }
        }
        $equal = true;
        foreach ($list_for_compare as &$innerArray1) {
            foreach ($list_for_compare as &$innerArray2) {
                if ($innerArray1['count'] !== $innerArray2['count']) {
                    $equal = false;
                    break;
                }
                $categoriesDiff = array_diff($innerArray1['categories'], $innerArray2['categories']);
                if (!empty($categoriesDiff)) {
                    $equal = false;
                    break;
                }
            }
        }

        if ($equal) {
            return true;
        } else {
            return false;
        }

    }

    public static function hexToRgb($hex) {
        if(!$hex){
            return '';
        }
        $hex = str_replace("#", "", $hex);

        if(strlen($hex) == 3) {
            $r = hexdec(str_repeat(substr($hex, 0, 1), 2));
            $g = hexdec(str_repeat(substr($hex, 1, 1), 2));
            $b = hexdec(str_repeat(substr($hex, 2, 1), 2));
        } else {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
        }

        return array($r, $g, $b);
    }

    public static function getBrightness($r, $g, $b) {
        return (0.299 * $r + 0.587 * $g + 0.114 * $b);
    }
    public static function getTextColorAndBorder($backgroundColor) {
        if(!$backgroundColor){
            return '';
        }
        // Преобразуем HEX цвет в RGB
        list($r, $g, $b) = self::hexToRgb($backgroundColor);

        // Вычисляем яркость
        $brightness = self::getBrightness($r, $g, $b);

        // Устанавливаем цвет текста и границу на основе яркости
        if ($brightness > 186) {
            $textColor = "black";  // Светлый фон, делаем текст черным
            $border = "1px solid black"; // Добавляем черную границу
        } else {
            $textColor = "white";  // Темный фон, делаем текст белым
            $border = "none"; // Убираем границу
        }

        return array($textColor, $border);
    }

    public static function is_valid_date_for_delete($startDateString)
    {
        // Преобразуйте строку в объект Carbon
        $startDate = Carbon::createFromFormat('Y-m-d', $startDateString);

        // Получите текущую дату
        $currentDate = Carbon::now()->startOfDay(); // Убедитесь, что сравнение происходит на уровне дней

        // Сравните текущую дату с датой старта
        dd($currentDate->greaterThanOrEqualTo($startDate));
        if ($currentDate->greaterThanOrEqualTo($startDate)) {
            return false;
        }

        return true;
    }
}
