<?php

namespace App\Helpers;

use App\Models\Event;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
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
        $link = $event->link.'/list-routes-event';
        $image = QrCode::format('png')
            ->size(150)
            ->generate($link);
        $output_file = '/img/qr-code/img-' . time() . '.png';
        Storage::disk('admin')->put($output_file, $image); //storage/app/public/img/qr-code/img-1557309130.png
        return 'storage'.$output_file;
    }

}
