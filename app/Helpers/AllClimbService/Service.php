<?php

namespace App\Helpers\AllClimbService;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\Http;

class Service
{
    const all_climb_url = "https://allclimb.com";

    public static function curl_start($url)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => self::all_climb_url.$url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_HTTPHEADER => array(
                'Host: allclimb.com',
                'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:128.0) Gecko/20100101 Firefox/128.0',
                'Accept: */*',
                'Accept-Language: en-US,en;q=0.5',
                'X-Requested-With: XMLHttpRequest',
                'Origin: '.self::all_climb_url,
                'Connection: keep-alive',
                'Sec-Fetch-Dest: empty',
                'Sec-Fetch-Mode: cors',
                'Sec-Fetch-Site: same-origin',
                'Cookie: sessionid=vnajr3sr00ceav8i0jraqcgb6oih7f82'
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }

    public static function get_guides(): array
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => self::all_climb_url.'/ru/guides/',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_HTTPHEADER => array(
                'Host: allclimb.com',
                'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:128.0) Gecko/20100101 Firefox/128.0',
                'Accept: */*',
                'Accept-Language: en-US,en;q=0.5',
                'X-Requested-With: XMLHttpRequest',
                'Origin: '.self::all_climb_url,
                'Connection: keep-alive',
                'Sec-Fetch-Dest: empty',
                'Sec-Fetch-Mode: cors',
                'Sec-Fetch-Site: same-origin',
                'Cookie: sessionid=vnajr3sr00ceav8i0jraqcgb6oih7f82'
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        $post = json_decode($response);
        $list_guides = [];
        foreach ($post->result as $guid){
            $list_guides[] = $guid->country;
        }
        return array_values(array_unique($list_guides));
    }
    public static function get_list_guides_country($country): array
    {
        $response = self::curl_start('/ru/guides/');
        $post = json_decode($response);
        $list_country = [];
        foreach ($post->result as $guid){
            if($country == $guid->country){
                $list_country[] = $guid->name;
            }
        }
        return array_values(array_unique($list_country));
    }
    public static function get_guides_in_country($guide): array
    {
        $response = self::curl_start('/ru/guides/'.$guide.'/');
        $post = json_decode($response);
        $list_guides = [];
        foreach ($post->result as $guid){
            $list_guides[] = $guid->name;
        }
        return array_values(array_unique($list_guides));
    }
    public static function get_info_area_in_guide($guide, $area)
    {
        $response = self::curl_start('/ru/guides/'.$guide.'/');
        $post = json_decode($response);
        foreach ($post->result as $guid){
            if($area == $guid->name){
                return $guid->info;
            }
        }
        return null;
    }
}
