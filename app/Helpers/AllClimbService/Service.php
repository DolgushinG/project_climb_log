<?php

namespace App\Helpers\AllClimbService;

use App\Models\Area;
use App\Models\Country;
use App\Models\Place;
use App\Models\PlaceRoute;

class Service
{
    const all_climb_url = "https://allclimb.com";

    public static function curl_start($url)
    {
        $curl = curl_init();

        $link = str_replace ( ' ', '%20', $url);
        curl_setopt_array($curl, array(
            CURLOPT_URL => self::all_climb_url.$link,
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
            CURLOPT_URL => self::all_climb_url,
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

    public static function get_countries(): array
    {
        $response = self::curl_start('/ru/guides/');
        $post = json_decode($response);
        $list_country = [];
        foreach ($post->result as $guid){
            $list_country[] = $guid->country;
        }
        return array_values(array_unique($list_country));
    }
    public static function get_places($country): array
    {
        $response = self::curl_start('/ru/guides/');
        $post = json_decode($response);
        $list = [];
        foreach ($post->result as $guid){
            if($country == $guid->country){
                $list[] = array('place_name' => $guid->name, 'web_guide_link' => $guid->web_guide_link);
            }
        }
        return $list;
    }
    public static function get_areas($place): array
    {
        $response = self::curl_start('/ru/guides/'.$place.'/');
        $post = json_decode($response);
        $list = [];
        foreach ($post->result as $guid){
            $list[] = array('area_name' => $guid->name, 'web_guide_link' => $guid->web_guide_link);
        }
        return $list;
    }

    public static function get_place_routes($place, $area): array
    {
        $response = self::curl_start('/ru/guides/'.$place.'/'.$area.'/');
        $post = json_decode($response);
        $list = [];
        if(isset($post->result)){
            foreach ($post->result as $guid){
                if(isset($guid->numroutes)){
                    $num = 'Всего - '.preg_replace('/<b>(.*?)<\/b>/', '$1', $guid->numroutes);
                } else {
                    $num = '';
                }
                $list[] = array(
                    'place_route_name' => $guid->name,
                    'web_guide_link' => $guid->web_guide_link,
                    'description' => $num,
                );
            }
        }

        return $list;
    }

    public static function get_routes($place, $area, $rock): array
    {
        $route_id = 1;
        $response = self::curl_start('/ru/guides/'.$place.'/'.$area.'/'.$rock.'/');
        $post = json_decode($response);
        $list_guides = [];
        if(isset($post->images)){
            foreach ($post->images as $guid){

                if(isset($guid->Routes)){
                    foreach ($guid->Routes as $route) {
                        $type = null;
                        if(str_contains($route->type , 'Боулдер')){
                            $type = 'боулдеринг';
                        }
                        if(str_contains($route->type , 'Спорт')){
                            $type = 'трудность';
                        }
                        if(str_contains($route->grade , 'project') || str_contains($route->grade , 'проект')){
                            $list_guides[] = array('name' => $route->name, 'grade' => 'project');
                        } else {
                            preg_match('/\d+[a-zA-Z]+/', $route->grade, $matches);
                            if(preg_match('/["\']|([^\d\.,])/', $route->name)){
                                if(strlen($route->name) < 2 && str_contains($route->name, "'")){
                                    $list_guides[] = array(
                                        'route_id' => $route_id,
                                        'name' => 'Без названия',
                                        'type' => $type,
                                        'grade' => $matches[0],
                                        'web_link' => $route->web_guide_link,
                                    );
                                } else {
                                    $list_guides[] = array(
                                        'route_id' => $route_id,
                                        'name' => $route->name,
                                        'type' => $type,
                                        'grade' => $matches[0],
                                        'web_link' => $route->web_guide_link,
                                    );
                                }
                            } else {
                                $list_guides[] = array(
                                    'route_id' => $route_id,
                                    'name' => 'Без названия',
                                    'type' => $type,
                                    'grade' => $matches[0],
                                    'web_link' => $route->web_guide_link,
                                );
                            }
                        }
                        $route_id++;
                    }

                }

            }
        }
        return $list_guides;
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
    public static function get_amount_all_routes($guide, $area, $rock)
    {
        $response = self::curl_start('/ru/guides/' . $guide . '/' . $area . '/');
        $post = json_decode($response);
        $amount = 0;
        if (isset($post->result)) {
            foreach ($post->result as $guid) {
                if (isset($guid->numroutes)) {
                    if ($guid->name == $rock) {
                        preg_match('/\d+/', $guid->numroutes, $matches);
                        $amount += intval($matches[0]);
                    }

                }
            }
            return $amount;
        } else {
            return null;
        }
    }

    public static function update_countries()
    {
        $countries = Service::get_countries();
        foreach ($countries as $country_name){
            $country = Country::where('name', $country_name)->first();
            if(!$country){
                $country = new Country;
                $country->name = $country_name;
                $country->save();
            }
        }
    }
    public static function update_places()
    {
        $countries = Country::all();
        foreach ($countries as $country){
            $places = Service::get_places($country->name);
            foreach ($places as $el){
                $place = Place::where('name', $el['place_name'])->where('country_id', $country->id)->first();
                if(!$place){
                    $place = new Place;
                    $place->name = $el['place_name'];
                    $place->web_link = $el['web_guide_link'];
                    $place->country_id = $country->id;
                    $place->save();
                }
            }
        }
    }

    public static function update_areas()
    {
        $countries = Country::all();
        foreach ($countries as $country){
            $places = Place::where('country_id', $country->id)->get();
            foreach ($places as $place){
                $areas = Service::get_areas($place->name);
                foreach ($areas as $el){
                    $area = Area::where('name', $el['area_name'])->where('place_id', $place->id)->first();
                    if(!$area){
                        $area = new Area;
                        $area->name = $el['area_name'];
                        $area->web_link = $el['web_guide_link'];
                        $area->place_id = $place->id;
                        $area->save();
                    }
                }
            }
        }
    }
    public static function update_place_routes()
    {
        $countries = Country::all();
        foreach ($countries as $country){
            $places = Place::where('country_id', $country->id)->get();
            foreach ($places as $place){
                $areas = Area::where('place_id', $place->id)->get();
                foreach ($areas as $area){
                    $response_place_route_name = Service::get_place_routes($place->name, $area->name);
                    foreach ($response_place_route_name as $el){
                        $place_routes_model = PlaceRoute::where('name', $el['place_route_name'])->where('area_id', $area->id)->first();
                        if(!$place_routes_model){
                            $place_routes_model = new PlaceRoute;
                            $place_routes_model->area_id = $area->id;
                            $place_routes_model->name = $el['place_route_name'];
                            $place_routes_model->web_link = $el['web_guide_link'];
                            $place_routes_model->description = $el['description'];
                            $place_routes_model->save();
                        }
                    }
                }
            }
        }
    }
}
