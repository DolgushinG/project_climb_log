<?php

namespace App\Models;

use App\Helpers\AllClimbService\Service;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Route extends Model
{

    protected $table = 'routes';

    public function event()
    {
        return $this->belongsTo(Event::class)->where('active', 1);
    }

    public static function generation_route($owner_id, $event_id, $amount_routes, $routes){
        $grades = array();
        foreach ($routes as $route){
            if(isset($route['Ценность'])){
                if(isset($route['Ценность зоны'])){
                    $grades[] = array('owner_id' => $owner_id ,'event_id' => $event_id, 'amount' => $route['Кол-во'], 'grade' => $route['Категория'], 'zone' => $route['Ценность зоны'], 'value' => $route['Ценность']);
                } else {
                    $grades[] = array('owner_id' => $owner_id ,'event_id' => $event_id, 'amount' => $route['Кол-во'], 'grade' => $route['Категория'], 'value' => $route['Ценность']);
                }
            } else {
                $grades[] = array('owner_id' => $owner_id ,'event_id' => $event_id, 'grade' => $route['Категория'], 'amount' => $route['Кол-во']);
            }
        }
        Route::where('event_id', $event_id)->delete();

        Grades::settings_routes($amount_routes, $grades);
    }
    public static function is_exist_name($routes, $name, $grade)
    {
        foreach ($routes as $route) {
            if(in_array($name, $route) && in_array($grade, $route)){
                return true;
            };
        }
        return false;
    }
    public static function generation_outdoor_route($event_id, $place_id, $area_id, $rock_id, $routes){
        $route_id = 1;
        $event = Event::find($event_id);
        $record_outdoor_routes = [];
        foreach ($rock_id as $id){
            if($id){
                $place = Place::find($place_id);
                $area = Area::find($area_id);
                $model_rock = PlaceRoute::find($id);
                $response_routes = Service::get_routes($place->name, $area->name, $model_rock->name);
                if($response_routes){
                    foreach ($response_routes as $route){
                        if($route == 'project'){
                            $value = null;
                            $flash_value = null;
                        } else {
                            $grades_with_value_flash = Grades::outdoor_grades_with_value_flash(20);
                            $grades = Grades::outdoor_grades();
                            $index = array_search(strtoupper($route['grade']), $grades);
                            $flash_value = $grades_with_value_flash[$index];
                            $value = self::get_current_value_for_grade($routes , $route['grade']);
                        }
                        if(!self::is_exist_name($record_outdoor_routes, $route['name'], $route['grade'])){
                            $record_outdoor_routes[] = array(
                                'owner_id' => $event->owner_id,
                                'event_id' => $event_id,
                                'route_id' => $route_id,
                                'country_id' => $place->country_id,
                                'place_id' => $place_id,
                                'area_id' => $area_id,
                                'place_route_id' => $id,
                                'route_name' => $route['name'],
                                'grade' => $route['grade'],
//                            'zone' => $route['Ценность зоны'],
                                'value' => $value,
                                'flash_value' => $flash_value,
                            );
                            $route_id++;
                        }

                    }
                }
            }
        }
        RoutesOutdoor::where('event_id', $event_id)->delete();
        DB::table('routes_outdoors')->insert($record_outdoor_routes);
    }

    public static function get_current_value_for_grade($routes, $grade)
    {
        foreach ($routes as $route){
            if($route['Категория'] == strtoupper($grade)){
                return $route['Ценность'];
            }
        }
        return null;
    }
}
