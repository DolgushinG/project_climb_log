<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\BatchCreateOutdoorRoutes;
use App\Admin\Actions\BatchHideGrades;
use App\Admin\Actions\BatchUpdateOutdoorRoutes;
use App\Models\Event;
use App\Models\Grades;
use App\Http\Controllers\Controller;
use App\Models\Route;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
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
                        $row->column(4, function (Column $column) {
                            $column->row($this->france_system_routes());
                        });
                    } else {
                        $row->column(4, function (Column $column) {
                            $column->row($this->event_routes());
                            $column->row($this->ready_routes());
                        });
                    }

                }
            });
    }

    /**
     * Edit interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function edit($id, Content $content)
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
        $route = Route::find($id);
        if($route){
            $grade = Grades::where('event_id', $route->event_id)->first();
        }
        if($request->name == 'route_id'){
            $route = Route::find($request->pk);
            $route->route_id = $request->value;
            $route->save();
        }
        if($request->name == 'grade'){
            $route = Route::find($request->pk);
            $route->grade = $request->value;
            $route->save();
        }
        if($request->count_routes){
            $grade = Grades::where('event_id', $request->event_id)->first();
            $route = Route::where('event_id', $request->event_id)->first();
            if($route){
                Route::generation_route($request->owner_id, $request->event_id, $request->count_routes, $request->grade_and_amount);
            }

        }

        return $this->form('update')->update($grade->id);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @return mixed
     */
    public function store()
    {
        return $this->form()->store();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $event_id = Grades::find($id)->event_id;
        $route = Route::where('event_id', $event_id)->first();
        if($route){
            Route::where('event_id', $event_id)->delete();
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
            if($event->type_event){
                $grid->tools(function (Grid\Tools $tools) use ($event) {
                    $tools->append(new BatchCreateOutdoorRoutes);
                    $tools->append(new BatchUpdateOutdoorRoutes);
                });
            } else {
                $grid->tools(function ($tools) {
                    $tools->append("<a href='/admin/grades/create' class='btn btn-success'>Сгенерировать скалодромные трассы</a>");
                });
            }

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
        $grid->disableActions();
        $grid->disableBatchActions();
        $grid->disableCreateButton();
        $grid->disableColumnSelector();
        $grid->disableExport();
        $grid->disablePagination();
        $grid->tools(function (Grid\Tools $tools) {
            $tools->append(new BatchHideGrades);
        });
        $grid->column('route_id', 'Номер трассы')->editable();
        $grid->column('grade', 'Категория трассы')->editable();
        $event = Event::where('owner_id', '=', Admin::user()->id)->where('active', '=', 1)->first();
        if($event->mode == 1){
            $grid->column('value', 'Ценность трассы');
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
    protected function form_routes()
    {
        $form = new Form(new Route);
        $event = Event::where('owner_id', '=', Admin::user()->id)->where('active', '=', 1)->first();
        $form->hidden('owner_id')->value($event->owner_id);
        $form->hidden('event_id')->value($event->id);
        $form->number('route_id');
        $form->text('grade');
        if($event->mode == 1){
            if($event->is_zone_show){
                $form->number('zone');
            }
            $form->number('value');
        }
        $form->disableCreatingCheck();
        $form->disableEditingCheck();


        return $form;
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


        if($event->is_zone_show){
            $routes = Grades::getRoutesWithZone();
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

        if(!$event->is_france_system_qualification){
            $form->hidden('count_routes', 'Кол-во трасс');
            Admin::style(".select2-selection__arrow {
                display: None;
            }");
            if($type == 'edit'){
                $form->html('<h4 id="warning-category" style="color: red" >Внимение!! Если вы редактировали категории или номера трасс,
                                    то это сбросится так как генерация трасс происходит с нуля</h4>');
            }
            $form->tablecustom('grade_and_amount', '', function ($table) use ($event){
                $grades = Grades::getGrades();
                $table->select('Категория')->attribute('inputmode', 'none')->options($grades)->readonly();
                $table->number('Кол-во')->attribute('inputmode', 'none')->width('50px');
                if($event->mode == 1){
                    $table->text('Ценность')->width('60px');
                    if($event->is_zone_show){
                        $table->text('Ценность зоны')->width('60px');
                    }
                }
                $table->disableButton();
            })->value($routes);
            $form->saving(function (Form $form) {
                if($form->grade_and_amount){
                    $main_count = 0;
                    foreach ($form->grade_and_amount as $route){
                        for ($count = 1; $count <= $route['Кол-во']; $count++){
                            $main_count++;
                        }
                    }
                    $form->count_routes = $main_count;
                }
            });
            $form->saved(function (Form $form) use ($type) {
                if($type !== 'update') {
                    $owner_id = Admin::user()->id;
                    $event = Event::where('owner_id', '=', $owner_id)->where('active', '=', 1)->first();
                    $exist_routes_list = Route::where('owner_id', '=', $owner_id)
                        ->where('event_id', '=', $event->id)->first();
                    if (!$exist_routes_list) {
                        if ($form->count_routes) {
                            Route::generation_route($owner_id, $event->id, $form->count_routes, $form->grade_and_amount);
                        }
                    } else {
                        Route::generation_route($owner_id, $event->id, $form->count_routes, $form->grade_and_amount);
                    }
                }
            });
        } else {
            $form->number('count_routes', 'Кол-во трасс');
        }

        return $form;
    }

}
