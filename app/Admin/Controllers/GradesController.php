<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\BatchAddArea;
use App\Admin\Actions\BatchAddPlace;
use App\Admin\Actions\BatchAddPlaceRoutes;
use App\Admin\Actions\BatchAddRoute;
use App\Admin\Actions\BatchHideGrades;
use App\Admin\Actions\BatchUpdateOutdoorRoutes;
use App\Helpers\AllClimbService\Service;
use App\Helpers\Helpers;
use App\Jobs\UpdateGradeInResultAllParticipant;
use App\Models\Area;
use App\Models\Color;
use App\Models\Country;
use App\Models\Event;
use App\Models\Grades;
use App\Http\Controllers\Controller;
use App\Models\GuidRoutesOutdoor;
use App\Models\Place;
use App\Models\PlaceRoute;
use App\Models\ResultFranceSystemQualification;
use App\Models\ResultQualificationClassic;
use App\Models\ResultRouteQualificationClassic;
use App\Models\Route;
use App\Models\RoutesOutdoor;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Grid\Displayers\Actions;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class GradesController extends Controller
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
                    if($event->is_france_system_qualification){
                        $row->column(10, function (Column $column) {
                            $column->row($this->france_system_routes());
                        });
                    } else {
                        $grades = Grades::where('event_id', $event->id)->first();
                        $column_width_color = 2;
                        if($grades && !$event->type_event){
                            $column_width_routes = 8;
                            $enabled_color = true;
                        } else {
                            $column_width_routes = 10;
                            $enabled_color = false;
                        }
                        if($enabled_color){
                            $row->column($column_width_color, function (Column $column) use ($event) {
                                if(!$event->type_event){
                                    $column->row($this->colors());
                                }
                            });
                        }
                        $row->column($column_width_routes, function (Column $column) use ($event) {
//                            $column->row($this->event_routes());
                            if($event->type_event){
                                $column->row($this->ready_outdoor_routes());
                            } else {
                                $column->row($this->ready_routes());
                            }
                        });
                    }
                }
            });
    }
    protected function colors()
    {
        // Определите массив цветов

        $colors = Color::where('owner_id', Admin::user()->id)->pluck('color_name', 'color')->toArray();
        // Сформируйте HTML-код для отображения цветов
        $html = '<table class="table table-striped">';
        $html .= '<thead><tr><th>Цвет</th><th>Название</th></tr></thead>';
        $html .= '<tbody>';
        foreach ($colors as $hex => $name) {
            $html .= '<tr>';
            $html .= '<td><div style="width: 50px; height: 20px; background-color: ' . $hex . ';"></div></td>';
            $html .= '<td>' . $name . '</td>';
            $html .= '</tr>';
        }
        $html .= '</tbody></table>';

        return $html;
    }


    /**
     * Edit interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function edit($id, Content $content, Request $request)
    {
        return $content
            ->header(trans('admin.edit'))
            ->description(trans('admin.description'))
            ->body($this->form('edit')->edit($id));
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

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update($id, Request $request)
    {
        $event = Event::where('owner_id', '=', Admin::user()->id)->where('active', 1)->first();
        if($event->type_event){
            $route = RoutesOutdoor::find($id);
            $route_pk = RoutesOutdoor::find($request->pk);
        } else {
            $route = Route::find($id);
            $route_pk = Route::find($request->pk);
        }
        if($route){
            $grade = Grades::where('event_id', $route->event_id)->first();
        }
        if($request->name == 'route_name'){
            $route_pk->route_name = $request->value;
            $route_pk->save();
        }
        if($request->name == 'grade'){
            $route_pk->grade = $request->value;
            $route_pk->save();
        }
        if($request->name == 'route_id'){
            $route_pk->route_id = $request->value;
            $route_pk->save();
            if (!$event->is_france_system_qualification && $request->value) {
                $participants = ResultQualificationClassic::where('event_id', $event->id)->where('active', 0)->where('is_other_event', 0)->get();
                if($participants){
                    foreach ($participants as $participant){
                        $participant->result_for_edit = ResultQualificationClassic::generate_empty_json_result($event->id);
                        $participant->save();
                    }
                }
            }
        }
        if($request->name == 'value'){
            $route_pk->value = $request->value;
            $route_pk->save();
        }
        if($request->input('color')){
            $color = $request->input('color');
            $route->color = $color;
            $route->color_view = $color;
            $route->save();
        }
        if($request->input('grade')){
            $route->grade = $request->grade;
            $route->save();
            if (!$event->is_france_system_qualification && $route->grade) {
                $results = ResultQualificationClassic::where('event_id', $event->id)->where('active', 1)->first();
                $participants = ResultQualificationClassic::where('event_id', $event->id)->where('active', 0)->where('is_other_event', 0)->get();
                if($results){
                    # Обновляем категорию трассы у всех участников которые добавили свои результаты
                    UpdateGradeInResultAllParticipant::dispatch($event->id, $route, $request->grade);
                }
                # Обновляем номер трасс у всех участников у которых подефолту пустота
                if($participants){
                    foreach ($participants as $participant){
                        $participant->result_for_edit = ResultQualificationClassic::generate_empty_json_result($event->id);
                        $participant->save();
                    }
                }
            }
        }
        if($request->count_routes){
            $grade = Grades::where('event_id', $request->event_id)->first();
            if($event->type_event){
                $route = RoutesOutdoor::where('event_id', $request->event_id)->first();
                if($route){
                    Route::generation_outdoor_route($request->event_id, $request->place_id, $request->area_id, $request->rock_id, $request->grade_and_amount);
                }
            } else {
                $route = Route::where('event_id', $request->event_id)->first();
                if($route){
                    Route::generation_route($request->owner_id, $request->event_id, $request->count_routes, $request->grade_and_amount);
                }
            }


        }

        return $this->form('update')->update($grade->id);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @return mixed
     */
    public function store(Request $request)
    {
        if($request->input('route_id')){
            $route = new Route;
            $route->event_id =  $request->input('event_id');
//            dd($request->input('owner_id'));
            $route->owner_id = $request->input('owner_id');
            $route->route_id = $request->input('route_id');
            $route->color = $request->input('color');
            $route->grade = $request->input('grade');
            $route->save();
            return Helpers::custom_response('Успешно создано', true);
        }
        return $this->form()->store();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     */
    public function destroy($id, Request $request)
    {
        $event = Event::where('owner_id', '=', Admin::user()->id)->where('active', 1)->first();
        $model = explode('-', $id);
        if(isset($model[0]) && isset($model[1])){
            if($model[0] == 'route'){
                if($event->type_event){
                    $route = RoutesOutdoor::find($model[1]);
                } else {
                    $route = Route::find($model[1]);
                }
                $route->delete();
                $grades = Grades::where('event_id', $event->id)->first();
                $get_count = RoutesOutdoor::where('event_id', $event->id)->get()->count();
                if($grades){
                    $grades->count_routes = $get_count;
                    $grades->save();
                }
                $response = [
                    'status'  => true,
                    'message' => "Успешно удалено",
                ];
                return response()->json($response);
            }
        }
        $grades = Grades::find($id);
        if($grades){
            if($event->type_event){
                $route = RoutesOutdoor::where('event_id', $grades->event_id)->get();
            } else {
                $route = Route::where('event_id', $grades->event_id)->get();
            }
            if($route){
                $route->each->delete();
            }
        }
        return $this->form()->destroy($id);
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function event_routes()
    {
        $grid = new Grid(new Grades);
        if (!Admin::user()->isAdministrator()){
            $grid->model()->where('owner_id', '=', Admin::user()->id);
        }
        $grid->model()->where(function ($query) {
            $query->has('event.grades');
        });
        $event = Event::where('owner_id', '=', Admin::user()->id)->where('active', '=', 1)->first();
        $grades = Grades::where('event_id', $event->id)->first();
        if(!$grades){

            $grid->tools(function ($tools) use ($event) {
                if($event->type_event){

                    Admin::script(<<<SCRIPT
            $('body').on('shown.bs.modal', '.modal', function() {
            $(this).find('select').each(function() {
                var dropdownParent = $(document.body);
                if ($(this).parents('.modal.in:first').length !== 0)
                    dropdownParent = $(this).parents('.modal.in:first');
                    $(this).select2({
                        dropdownParent: dropdownParent
                    });
                });
            });
            SCRIPT);
                    $tools->append(new BatchAddPlace);
                    $tools->append(new BatchAddArea);
                    $tools->append(new BatchAddPlaceRoutes);
                    $tools->append(new BatchUpdateOutdoorRoutes);
                }

                Admin::style('
                    .create-date-outdoor {margin-top:8px;}
                     @media screen and (max-width: 767px) {
                            .create-date-outdoor {margin-top:8px;}
                        }
                ');
                $tools->append("<a href='/admin/grades/create' class='create-date-outdoor btn btn-sm btn-success'>Сгенерировать трассы</a>");
            });

        }
        $grid->actions(function ($actions) use ($event) {
            $actions->disableView();
            $actions->disableEdit();
            if(Admin::user()->is_delete_result == 0){
                $actions->disableDelete();
            }
        });
        $grid->disableFilter();
        $grid->disableBatchActions();
        $grid->disableColumnSelector();
        $grid->disableExport();
        $grid->disablePagination();
        $grid->disableCreateButton();
        $grid->column('event_id', 'Настройка трасс для соревнования')->display(function ($event_id) {
            return Event::find($event_id)->title;
        });
        $grid->column('count_routes', 'Кол-во трасс');
        return $grid;
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function france_system_routes()
    {
        $grid = new Grid(new Grades);
        if (!Admin::user()->isAdministrator()){
            $grid->model()->where('owner_id', '=', Admin::user()->id);
        }
        $grid->model()->where(function ($query) {
            $query->has('event.grades');
        });
        $event = Event::where('owner_id', '=', Admin::user()->id)->where('active', '=', 1)->first();
        $grades = Grades::where('event_id', $event->id)->first();
        if(!$grades){
            $grid->tools(function ($tools) {
                $tools->append("<a href='/admin/grades/create' class='btn btn-success'> Настроить кол-во трасс</a>");
            });
        }
        $grid->disableFilter();
        $grid->disableBatchActions();
        $grid->disableColumnSelector();
        $grid->disableExport();
        $grid->disablePagination();
        $grid->disableCreateButton();
        $grid->column('event_id', 'Настройка трасс для соревнования')->display(function ($event_id) {
            return Event::find($event_id)->title;
        });
        $grid->column('count_routes', 'Кол-во трасс');
        return $grid;
    }



    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function ready_routes()
    {
        $grid = new Grid(new Route);
        if (!Admin::user()->isAdministrator()){
            $grid->model()->where('owner_id', '=', Admin::user()->id);
        }
        $grid->model()->where(function ($query) {
            $query->has('event.routes');
        });
        $grid->disableFilter();
//        $grid->disableActions();
        $grid->actions(function ($actions) {
            if(Admin::user()->is_delete_result == 0){
                $actions->disableDelete();
            }
            $actions->disableEdit();
            $actions->disableView();
        });
        $grid->disableBatchActions();
        $grid->disableCreateButton();
        $grid->disableColumnSelector();
        $grid->disableExport();
        $grid->disablePagination();
        $grid->tools(function (Grid\Tools $tools) {
            $tools->append(new BatchHideGrades);
        });
        Admin::script(<<<SCRIPT
                let routeIdColumns = document.querySelectorAll('td.column-route_id');
                routeIdColumns.forEach(function(routeIdColumn) {
                    let parentRow = routeIdColumn.closest('tr');
                    if (parentRow) {
                        let actionsColumn = parentRow.querySelector('td.column-__actions__');
                        if (actionsColumn) {
                            let deleteButton = actionsColumn.querySelector('a.grid-row-delete');
                            if (deleteButton) {
                                let currentId = deleteButton.getAttribute('data-id');
                                deleteButton.setAttribute('data-id', 'route-' + currentId);
                            }
                        }
                    }
                });
        SCRIPT);
        $event = Event::where('owner_id', '=', Admin::user()->id)->where('active', '=', 1)->first();
        if($event->type_event){
            $grid->column('route_name', 'Трасса');
            $grid->column('grade', 'Категория трассы');
            $grid->column('value', 'Ценность трассы');
        } else {

            $results = ResultRouteQualificationClassic::where('event_id', $event->id)->first();
            if($results){
                $grid->column('route_id', 'Номер трассы');
            } else {
                $grid->quickCreate(function (Grid\Tools\QuickCreate $create) use ($event){
                    $create->integer('owner_id')->style('display', 'None')->value($event->owner_id);
                    $create->integer('event_id')->style('display', 'None')->value($event->id);
                    $create->integer('route_id', 'Трасса')->placeholder('номер');
                    $create->select('grade', 'Категория трассы')->options(Grades::getGrades());
                    $colors = Color::where('owner_id', Admin::user()->id)->pluck('color_name', 'color')->toArray();
                    $colors['not_set_color'] = 'Не установлен';
                    $create->select('color', 'Цвет')->options($colors);
                    if($event->mode == 1) {
                        $create->text('value', 'Редпоинт');
                        if ($event->is_flash_value) {
                            $create->integer('flash_value', 'Флеш');
                        }
                        if ($event->is_zone_show) {
                            $create->integer('zone', 'Ценность зоны');
                        }
                    }
                });
                $grid->column('route_id', 'Номер трассы')->editable();
            }
            $colors = Color::where('owner_id', Admin::user()->id)->pluck('color_name', 'color')->toArray();
            $colors['not_set_color'] = 'Не установлен';
            $grid->column('color', __('Цвет трассы'))->select($colors);
            $grid->column('color_view', __('Цвет в представлении'))->display(function ($color) {
                return "<div style='width: 50px; height: 20px; background-color: {$color}; border: 1px solid #ddd;'></div>";
            });

            $grid->column('grade', 'Категория трассы')->select(Grades::getGrades());
            if($event->mode == 1){
                $grid->column('value', 'Редпоинт');
                if($event->is_flash_value){
                    $grid->column('flash_value', 'Флеш');
                }
                if($event->is_zone_show){
                    $grid->column('zone', 'Ценность зоны');
                }
            }
        }

        return $grid;
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function ready_outdoor_routes()
    {
        $grid = new Grid(new RoutesOutdoor);
        if (!Admin::user()->isAdministrator()){
            $grid->model()->where('owner_id', '=', Admin::user()->id);
        }
        $grid->model()->where(function ($query) {
            $query->has('event.routes');
        });
        $event = Event::where('owner_id', '=', Admin::user()->id)->where('active', '=', 1)->first();
        $grid->disableFilter();
        $grid->actions(function ($actions) {
            $actions->disableView();
            $actions->disableEdit();
//            $actions->disableDelete();
        });

        Admin::script(<<<SCRIPT
                let routeIdColumns = document.querySelectorAll('td.column-route_id');
                routeIdColumns.forEach(function(routeIdColumn) {
                    let parentRow = routeIdColumn.closest('tr');
                    if (parentRow) {
                        let actionsColumn = parentRow.querySelector('td.column-__actions__');
                        if (actionsColumn) {
                            let deleteButton = actionsColumn.querySelector('a.grid-row-delete');
                            if (deleteButton) {
                                let currentId = deleteButton.getAttribute('data-id');
                                deleteButton.setAttribute('data-id', 'route-' + currentId);
                            }
                        }
                    }
                });
            $('body').on('shown.bs.modal', '.modal', function() {
            $(this).find('select').each(function() {
                var dropdownParent = $(document.body);
                if ($(this).parents('.modal.in:first').length !== 0)
                    dropdownParent = $(this).parents('.modal.in:first');
                    $(this).select2({
                        dropdownParent: dropdownParent
                    });
                });
            });
SCRIPT);
        $grid->disableBatchActions();
        $grid->disableCreateButton();
        $grid->disableColumnSelector();
        $grid->disableExport();
        $grid->disablePagination();
        $grid->tools(function (Grid\Tools $tools) use ($event)  {
            if($event->type_event && Grades::where('event_id', $event->id)->first()){
                $tools->append(new BatchAddRoute);
                $tools->append(new BatchAddPlace);
                $tools->append(new BatchAddArea);
                $tools->append(new BatchAddPlaceRoutes);
            }
            $tools->append(new BatchHideGrades);
        });
        if($event->type_event){
            $grid->column('route_id', 'Номер')->editable();
            $grid->column('route_name', 'Трасса')->editable();
            $grid->column('place.name', 'Регион');
            $grid->column('area.name', 'Район');
            $grid->column('place_route.name', 'Сектор');
            $grid->column('type', 'Тип');
            $grid->column('image', 'Картинка')->image('', 200, 200);
            $grid->column('grade', 'Категория трассы')->editable('select', Grades::getGrades());
            $grid->column('value', 'Ценность трассы')->editable();
            if($event->is_flash_value){
                $grid->column('flash_value', 'Ценность трассы за флэш')->editable();
            }
            if($event->is_zone_show){
                $grid->column('zone', 'Ценность зоны');
            }
        }
        return $grid;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form($type=null)
    {
        $form = new Form(new Grades);
        $event = Event::where('owner_id', '=', Admin::user()->id)->where('active', '=', 1)->first();


        if($event->is_zone_show && $event->is_flash_value){
            $routes = Grades::getRoutesWithFlashZone();
        } elseif($event->is_zone_show) {
            $routes = Grades::getRoutesWithZone();
        } elseif($event->is_flash_value) {
            $routes = Grades::getRoutesWithFlash();
        } else {
            $routes = Grades::getRoutes();
        }

        $form->disableViewCheck();
        $form->disableEditingCheck();
        $form->disableCreatingCheck();
        $form->disableReset();
        $form->tools(function (Form\Tools $tools) {
            $tools->disableList();
            $tools->disableDelete();
            $tools->disableView();
        });

        $form->hidden('owner_id', '')->value(Admin::user()->id);
        $form->hidden('event_id', '')->value($event->id);
        if($event->type_event){
            $guides = Country::all()->pluck('name', 'id');
            $form->select('country_id', 'Страна')->attribute('id', 'place-outdoor')->options($guides);
            $form->select('place_id', 'Место')->attribute('id', 'area-outdoor');
            $form->select('area_id', 'Район')->attribute('id', 'local-outdoor');
            $form->multipleSelect('rocks_id', 'Камни(Cкалы)')->attribute('id', 'rock-outdoor');
            $script = <<<EOT
        $(document).on("change", '[id=place-outdoor]', function () {
                    $.get("api/get_places",
                            {option: $(this).val()},
                            function (data) {
                                var model = $('[id=area-outdoor]');
                                model.empty();
                                model.append("<option>Select a state</option>");
                                $.each(data, function (index, element) {
                                    model.append("<option value='" + element.id + "'>" + element.text + "</option>");
                                });
                            }
                    );
                });

        $(document).on("change", '[id=area-outdoor]', function () {
                    $.get("api/get_place_routes",
                            {option: $(this).val()},
                            function (data) {
                                var model = $('[id=local-outdoor]');
                                model.empty();
                                model.append("<option>Select a state</option>");
                                $.each(data, function (index, element) {
                                    model.append("<option value='" + element.id + "'>" + element.text + "</option>");
                                });
                            }
                    );
                });
        $(document).on("change", '[id=local-outdoor]', function () {
                    $.get("api/get_rocks",
                            {option: $(this).val()},
                            function (data) {
                                var model = $('[id=rock-outdoor]');
                                model.empty();
                                model.append("<option>Select a state</option>");
                                $.each(data, function (index, element) {
                                    model.append("<option value='" + element.id + "'>" + element.text + "</option>");
                                });
                            }
                    );
                });

        EOT;

            \Encore\Admin\Facades\Admin::script($script);
            Admin::style('
                        .remove {
                          display: none;
                        }
                        .add-amount{
                            display: none;
                        }');

        }

        if(!$event->is_france_system_qualification){
            $form->hidden('count_routes', 'Кол-во трасс');
            Admin::style(".select2-selection__arrow {
                display: None;
            }");

            if($type == 'edit'){
                $form->html('<h4 id="warning-category" style="color: red" >Внимение!! Если вы редактировали категории или номера трасс,
                                    то это сбросится так как генерация трасс происходит с нуля</h4>');
            }
            if($event->type_event) {
                $routes_lead_boulder = Grades::getRoutesOutdoorWithValue();
                Admin::style('
                    select[readonly] {
                      pointer-events: none;
                    }
                ');
                $form->tableroutes('grade_and_amount', '', function ($table) use ($event) {
                    $grades = Grades::getGrades();
                    $table->select('Категория трудность')->attribute('readonly', 'readonly')->options($grades)->readonly();
                    $table->text('Ценность трудность')->attribute('inputmode', 'none')->width('60px');
                    if ($event->mode == 1) {
                        if ($event->is_zone_show) {
                            $table->text('Ценность трудность зоны')->width('60px');
                        }
                    }
                    $table->select('Категория боулдеринг')->attribute('readonly', 'readonly')->options($grades)->readonly();
                    $table->text('Ценность боулдеринг')->attribute('inputmode', 'none')->width('60px');
                    if ($event->mode == 1) {
                        if ($event->is_zone_show) {
                            $table->text('Ценность боулдеринг зоны')->width('60px');
                        }
                    }
                    $table->disableButton();
                })->value($routes_lead_boulder);
            } else {
                $form->tablecustom('grade_and_amount', '', function ($table) use ($event) {
                    $grades = Grades::getGrades();
                    $table->select('Категория')->attribute('inputmode', 'none')->options($grades)->readonly();
                    if (!$event->type_event) {
                        $table->number('Кол-во')->attribute('inputmode', 'none')->width('50px');
                        if ($event->mode == 1) {
                            $table->text('Ценность')->width('60px');
                            if ($event->is_zone_show) {
                                $table->text('Ценность зоны')->width('60px');
                            }
                            if ($event->is_flash_value) {
                                $table->text('Ценность флеша')->width('60px');
                            }
                        }
                    } else {
                        $table->text('Ценность')->width('80px');
                    }
                    $table->disableButton();
                })->value($routes);
            }
//            $form->submitted(function (Form $form) {
//                $form->ignore('choose_type');
//            });
            $form->saving(function (Form $form) use ($event) {
                if($form->grade_and_amount && !$event->type_event){
                    $main_count = 0;
                    foreach ($form->grade_and_amount as $route){
                        for ($count = 1; $count <= $route['Кол-во']; $count++){
                            $main_count++;
                        }
                    }
                    $form->count_routes = $main_count;
                }
                if($event->type_event){
                    if($form->rocks_id) {
                        $amount = 0;
                        foreach ($form->rocks_id as $rock) {
                            if($rock){
                                $place = Place::find($form->place_id);
                                $area = Area::find($form->area_id);
                                $model_rock = PlaceRoute::find($rock);
                                $get_amount_all_route = Service::get_amount_all_routes($place->name, $area->name, $model_rock->name);
                                if(!$get_amount_all_route){
                                    $get_amount_all_route = 0;
                                }
                                $get_amount_all_route_offline = GuidRoutesOutdoor::where('place_id', $form->place_id)->where('area_id', $form->area_id)->where('place_route_id', $rock)->get()->count();
                                if(!$get_amount_all_route_offline){
                                    $get_amount_all_route_offline = 0;
                                }

                                $amount += $get_amount_all_route + $get_amount_all_route_offline;
                            }
                        }
                        $form->count_routes = $amount;
                    }
                }
            });
            $form->saved(function (Form $form) use ($type) {
                if ($type !== 'update') {
                    $owner_id = Admin::user()->id;
                    $event = Event::where('owner_id', '=', $owner_id)->where('active', '=', 1)->first();
                    $exist_routes_list = Route::where('event_id', '=', $event->id)->first();
                    $exist_routes_outdoor_list = RoutesOutdoor::where('event_id', '=', $event->id)->first();
                    if (!$exist_routes_list || !$exist_routes_outdoor_list) {
                        if ($form->count_routes) {
                            if ($event->type_event) {
                                Route::generation_outdoor_route($event->id, $form->place_id, $form->area_id, $form->rocks_id, $form->grade_and_amount);
                            } else {
                                Route::generation_route($owner_id, $event->id, $form->count_routes, $form->grade_and_amount);
                            }
                        }
                    } else {
                        if ($event->type_event) {
                            Route::generation_outdoor_route($event->id, $form->place_id, $form->area_id, $form->rocks_id, $form->grade_and_amount);
                        } else {
                            Route::generation_route($owner_id, $event->id, $form->count_routes, $form->grade_and_amount);
                        }
                    }
                }
            });
        } else {
            $form->number('count_routes', 'Кол-во трасс');
        }
        return $form;
    }
}
