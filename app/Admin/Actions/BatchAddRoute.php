<?php

namespace App\Admin\Actions;

use App\Models\Area;
use App\Models\Country;
use App\Models\Event;
use App\Models\Grades;
use App\Models\GuidRoutesOutdoor;
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
            $type = $request->input('type');
            if(request()->image){
                $imageName = time().'.'.request()->image->getClientOriginalExtension();
                $path = '/images/route/'.$id.'/'.$imageName;
                request()->image->move(public_path('storage/images/route/'.$id), $imageName);
            } else{
                $path = null;
            }
            $grade = $request->input('grade');
            $owner_id = \Encore\Admin\Facades\Admin::user()->id;
            $event = Event::where('owner_id', '=', $owner_id)->where('active', 1)->first();
            $route_id = RoutesOutdoor::where('event_id', $event->id)->get()->count() + 1;
            $grades_event = Grades::where('event_id', $event->id)->first();
            $model = RoutesOutdoor::where('event_id', $event->id)->where('place_route_id', $id)->where('grade', $grade)->where('route_name', $route_name)->first();
            $model_for_guid = GuidRoutesOutdoor::where('place_id', $area->place_id)->where('area_id', $place_route->area_id)->where('place_route_id', $id)->where('grade', $grade)->where('route_name', $route_name)->first();
            if(!$model_for_guid){
                $model_for_guid = new GuidRoutesOutdoor;
                $model_for_guid->country_id = $country->id;
                $model_for_guid->place_id = $place->id;
                $model_for_guid->area_id = $area->id;
                $model_for_guid->grade = $grade;
                $model_for_guid->type = $type;
                $model_for_guid->place_route_id = $id;
                $model_for_guid->route_name = $route_name;
                $model_for_guid->image = $path;
                $model_for_guid->save();
            }
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
                $model->type = $type;
                $model->image = $path;
                $model->route_id = $route_id;
                $grades_with_value_flash = Grades::outdoor_grades_with_value_flash(20);
                $grades = Grades::outdoor_grades();
                $index = array_search(strtoupper($grade), $grades);
                $flash_value = $grades_with_value_flash[$index];
                $value = Route::get_current_value_for_grade($grades_event->grade_and_amount , $grade, $type);
                $model->value = $value;
                $model->flash_value = $flash_value;
                $model->save();
                if($grades_event){
                    $grades_event->count_routes = $route_id;
                    $grades_event->save();
                }
            }
            return $this->response()->success('Готово')->refresh();
        }
    }

    public function form()
    {
        $this->modalSmall();
        $owner_id = \Encore\Admin\Facades\Admin::user()->id;
        $event = Event::where('owner_id', '=', $owner_id)->where('active', 1)->first();
        $route_place = RoutesOutdoor::where('event_id', $event->id)->latest()->first();
        if($route_place){
            $this->select('place_routes.id', 'Сектор')->options(PlaceRoute::all()->pluck('name', 'id'))->value($route_place->place_route_id)->required();
        } else {
            $this->select('place_routes.id', 'Сектор')->options(PlaceRoute::all()->pluck('name', 'id'))->required();
        }

        $this->text('route_name', 'Название')->required();
        $this->select('type', 'Тип')->options(['трудность' => 'Трудность', 'боулдеринг' => 'Боулдер', 'мультипитч' => 'Мультипитч'])->required();
        $this->image('image', 'Картинка маршрута')->move('images/route')->required();
        $this->select('grade', 'Категория')->options(Grades::getGrades())->required();
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
