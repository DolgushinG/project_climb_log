<?php

namespace App\Admin\Controllers;

use App\Models\Event;
use App\Models\Map;
use App\Http\Controllers\Controller;
use App\Models\Route;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Show;
use Illuminate\Http\Request;
class MapController extends Controller
{
    use HasResourceActions;

    /**
     * Index interface.
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        return $content
            ->row(function(Row $row) {
                $event = Event::where('owner_id', '=', Admin::user()->id)->where('active', 1)->first();
                if($event){
                    if(!$event->is_france_system_qualification && !$event->type_event){
                        $row->column(10, function (Column $column) use ($event) {
                            $column->row($this->grid());

                        });
                        $row->column(10, function (Column $column) use ($event) {
                            $column->row($this->list_points());
                        });
                    }
                }
            });
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return mixed
     */
    public function store(Request $request)
    {
        $data = $request->only(['route_id', 'author', 'color', 'x', 'y']);

        // Добавляем или изменяем данные
        $data['created_at'] = now(); // Добавляем текущую дату и время
        $data['updated_at'] = now(); // Добавляем текущую дату и время
        $event = Event::where('owner_id', '=', 2)->where('active', 1)->first();
        $route = Route::where('event_id', $event->id)->where('route_id', $data['route_id'])->first();
        // Вы можете добавить дополнительные данные
        // Например, если вам нужно установить ID пользователя:
        $data['grade'] = $route->grade; // Пример добавления ID текущего пользователя
        $data['event_id'] = $event->id; // Пример добавления ID текущего пользователя
        $data['owner_id'] = $event->owner_id; // Пример добавления ID текущего пользователя
        $point = Map::create($data);

        return response()->json([
            'success' => true,
            'point' => $point,
        ]);
    }

    /**
     * Create interface.
     *
     * @param Content $content
     * @return Content
     */
    public function create(Content $content)
    {
        return $content
            ->header(trans('admin.create'))
            ->description(trans('admin.description'))
            ->body($this->form());
    }

    protected function list_points()
    {
        $grid = new Grid(new Map);
        $grid->column('route_id', 'Номер маршрут');
        $grid->column('grade', 'Категория');
        $grid->column('author', 'Автор');
        $grid->column('color', __('Цвет'))->display(function ($color) {
            return "<div style='width: 50px; height: 20px; background-color: {$color}; border: 1px solid #ddd;'></div>";
        });
        return $grid;
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $points = Map::all();
        $event = Event::where('owner_id', '=', 2)->where('active', 1)->first();
        $points_exist = Map::where('event_id', $event->id)->pluck('route_id')->toArray();
        $routes = Route::where('event_id', $event->id)->get();
        $scheme_climbing_gym = '/storage/images/map.jpeg';
        return Admin::component('admin::map', compact(['points', 'event', 'routes', 'points_exist','scheme_climbing_gym']));
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Map);

        $form->hidden('event_id', __('Author'))->rules('required');
        $form->hidden('owner_id', __('Author'))->rules('required');
        $form->text('author', __('Author'))->rules('required');
        $form->text('grade', __('Category'))->rules('required');
        $form->text('route_id', __('Route'))->rules('required');
        $form->color('color', __('Color'))->rules('required');
        $form->hidden('x'); // Скрываем, так как x и y будут установлены через JavaScript
        $form->hidden('y');

        return $form;
    }
}
