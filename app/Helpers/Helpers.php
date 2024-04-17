<?php

namespace App\Helpers;

use App\Models\Event;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use DateInterval;
use DatePeriod;
use DateTime;
use Illuminate\Support\Facades\Storage;
use Imagick;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
class Helpers
{

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

    public static function save_qr_code($event)
    {
        $link = $event->link.'/routes';
        $image = QrCode::format('png')
            ->size(150)
            ->generate($link);
        $output_file = '/img/qr-code/img-' . time() . '.png';
        Storage::disk('admin')->put($output_file, $image); //storage/app/public/img/qr-code/img-1557309130.png
        return 'storage'.$output_file;
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
}
