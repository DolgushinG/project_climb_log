<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\BatchAddMap;
use App\Admin\Actions\BatchForceRecoutingSemiFinalResultGender;
use App\Admin\Actions\BatchForceRecoutingSemiFinalResultGroup;
use App\Admin\Actions\BatchGenerateResultSemiFinalParticipant;
use App\Admin\Actions\BatchRemoveMap;
use App\Admin\Actions\ResultRoute\BatchResultCustomRouteUniversal;
use App\Admin\Actions\ResultRoute\BatchResultRouteUniversal;
use App\Admin\Actions\ResultRouteSemiFinalStage\BatchExportProtocolRouteParticipantSemiFinal;
use App\Admin\Actions\ResultRouteSemiFinalStage\BatchExportResultSemiFinal;
use App\Helpers\Helpers;
use App\Models\Color;
use App\Models\Event;
use App\Models\Map;
use App\Http\Controllers\Controller;
use App\Models\Point;
use App\Models\Route;
use App\Models\Staff;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
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
        $event = Event::where('owner_id', '=', Admin::user()->id)->where('active', 1)->first();
        $route = Route::where('event_id', $event->id)->where('route_id', $data['route_id'])->first();
        // Вы можете добавить дополнительные данные
        // Например, если вам нужно установить ID пользователя:
        $data['grade'] = $route->grade; // Пример добавления ID текущего пользователя
//        $data['author'] = 'Иванов'; // Пример добавления ID текущего пользователя
        $data['event_id'] = $event->id; // Пример добавления ID текущего пользователя
        $data['owner_id'] = $event->owner_id; // Пример добавления ID текущего пользователя
        $point = Point::create($data);

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
        $grid = new Grid(new Point);
        $grid->column('route_id', 'Номер маршрут');
        $grid->column('grade', 'Категория');
        $grid->column('author', 'Автор');
        $grid->column('color', __('Цвет'))->display(function ($color) {
            return "<div style='width: 50px; height: 20px; background-color: {$color}; border: 1px solid #ddd;'></div>";
        });
        $grid->tools(function (Grid\Tools $tools) {
            $tools->append(new BatchAddMap);
            $tools->append(new BatchRemoveMap);
        });
        $grid->disableFilter();
//        $grid->disableBatchActions();
        $grid->disableColumnSelector();
        $grid->disableExport();
        $grid->disableCreateButton();
        return $grid;
    }
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $points = Point::all();
        $owner_id = Admin::user()->id;
        $event = Event::where('owner_id', '=', $owner_id)->where('active', 1)->first();
        $points_exist = Point::where('event_id', $event->id)->pluck('route_id')->toArray();
        $routes = Route::where('event_id', $event->id)->get();
        $scheme_climbing_gym = '/storage/'.Admin::user()->map;
        foreach ($points as $index => $point){
            $points[$index]['font_background'] = Helpers::getTextColorAndBorder($point->color ?? '');
        }
        $authors = Staff::where('owner_id', $owner_id)->where('type', 'routesetter')->pluck('middlename','id')->toArray();
        $colors = Color::where('owner_id', $owner_id)->get()->sortByDesc('color_name');
        return Admin::component('admin::map', compact(['colors','authors','points', 'event', 'routes', 'points_exist','scheme_climbing_gym']));
    }

    /**
     * Edit interface.
     *
     * @param Content $content
     * @return Content
     */
    public function edit($id, Content $content)
    {
        return $content
            ->header(trans('admin.edit'))
            ->description(trans('admin.description'))
            ->body($this->form()->edit($id));
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Point);

        $form->hidden('event_id', __('Author'))->rules('required');
        $form->hidden('owner_id', __('Author'))->rules('required');
        $form->text('author', __('Автор'))->rules('required');
        $form->text('grade', __('Категория'))->rules('required');
        $form->text('route_id', __('Маршрут'))->rules('required');
        $form->color('color', __('Цвет'))->rules('required');
        $form->hidden('x'); // Скрываем, так как x и y будут установлены через JavaScript
        $form->hidden('y');

        return $form;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param int $id
     *
     */
    public function update($id, Request $request)
    {
        if($id){
           $point = Point::find($id);
           if($point){
               $point->author = $request->input('author');
               $point->route_id = $request->input('route_id');
               $point->grade = $request->input('grade');
               $point->color = $request->input('color');
               $point->save();
               return response()->json(['success' => true, 'message' => 'Точка успешно обновлена!']);
           }
        }
        return response()->json(['success' => false, 'message' => 'Ошибка при обновлении точки.']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     */
    public function destroy($id, Request $request)
    {
        if($id){
            $point = Point::find($id);
            if($point){
                $point->delete();
                return response()->json(['success' => true, 'message' => 'Точка успешно удалена!!']);
            }
        }
        return response()->json(['success' => false, 'message' => 'Ошибка при удалении точки.']);
    }
}
