<?php

namespace App\Admin\Actions;

use App\Models\Area;
use App\Models\Country;
use App\Models\Event;
use App\Models\Grades;
use App\Models\Place;
use App\Models\PlaceRoute;
use App\Models\Route;
use App\Models\RoutesOutdoor;
use Encore\Admin\Actions\Action;
use Illuminate\Http\Request;

class BatchAddRoute extends Action
{
    public $name = 'Добавить Маршрут';

    protected $selector = '.route';

    public function handle(Request $request)
    {
        if($request->input('place_routes')){
            $id = $request->input('place_routes')['id'];
            $place_route = PlaceRoute::find($id);
            $area = Area::find($place_route->area_id);
            $place = Place::find($area->place_id);
            $country = Country::find($place->country_id);
            $route_name = $request->input('route_name');
            $grade = $request->input('grade');
            $owner_id = \Encore\Admin\Facades\Admin::user()->id;
            $event = Event::where('owner_id', '=', $owner_id)->where('active', 1)->first();
            $route_id = RoutesOutdoor::where('event_id', $event->id)->get()->count() + 1;
            $grades_event = Grades::where('event_id', $event->id)->first();
            $model = RoutesOutdoor::where('event_id', $event->id)->where('place_route_id', $id)->where('grade', $grade)->where('route_name', $route_name)->first();
            if(!$model){
                $model = new RoutesOutdoor;
                $model->owner_id = $owner_id;
                $model->event_id = $event->id;
                $model->country_id = $country->id;
                $model->place_id = $place->id;
                $model->area_id = $area->id;
                $model->grade = $grade;
                $model->place_route_id = $id;
                $model->route_name = $route_name;
                $model->route_id = $route_id;
                $grades_with_value_flash = Grades::outdoor_grades_with_value_flash(20);
                $grades = Grades::outdoor_grades();
                $index = array_search(strtoupper($grade), $grades);
                $flash_value = $grades_with_value_flash[$index];
                $value = Route::get_current_value_for_grade($grades_event->grade_and_amount , $grade);
                $model->value = $value;
                $model->flash_value = $flash_value;
                $model->save();


                if($grades_event){
                    $grades_event->count_routes = $route_id;
                    $grades_event->save();
                }
                return $this->response()->success('Готово')->refresh();
            }
        }
    }

    public function form()
    {
        $this->modalSmall();
        $owner_id = \Encore\Admin\Facades\Admin::user()->id;
        $event = Event::where('owner_id', '=', $owner_id)->where('active', 1)->first();
        $route_place = $model = RoutesOutdoor::where('event_id', $event->id)->latest()->first();
        if($route_place){
            $this->select('place_routes.id', 'Сектор')->options(PlaceRoute::all()->pluck('name', 'id'))->value($route_place->place_route_id);
        } else {
            $this->select('place_routes.id', 'Сектор')->options(PlaceRoute::all()->pluck('name', 'id'));
        }

        $this->text('route_name', 'Название');
        $this->image('image', 'Картинка маршрута')->move('images/route');
        $this->select('grade', 'Категория')->options(Grades::getGrades());
    }

    public function html()
    {
        return "<a class='route btn btn-sm btn-primary'><i class='fa fa-arrow-up'></i> {$this->name}</a>
                <style>
                .route {margin-top:8px;}
                 @media screen and (max-width: 767px) {
                        .route {margin-top:8px;}
                    }
                </style>
            ";
    }

}
