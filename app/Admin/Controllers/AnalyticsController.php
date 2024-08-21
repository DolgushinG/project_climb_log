<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\BatchAddArea;
use App\Admin\Actions\BatchAddPlace;
use App\Admin\Actions\BatchAddPlaceRoutes;
use App\Admin\Actions\BatchAddRoute;
use App\Admin\Actions\BatchHideGrades;
use App\Admin\Actions\BatchUpdateOutdoorRoutes;
use App\Helpers\AllClimbService\Service;
use App\Jobs\UpdateGradeInResultAllParticipant;
use App\Models\Area;
use App\Models\Country;
use App\Models\Event;
use App\Models\EventAndCoefficientRoute;
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
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Illuminate\Http\Request;
use Jxlwqq\DataTable\DataTable;

class AnalyticsController extends Controller
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
                    $row->column(10, function (Column $column) use ($event) {
                        $column->row($this->male());
                        $column->row($this->event_analytics());
                    });

                }
            });
    }
    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public static function male()
    {
        $event = Event::where('owner_id', '=', Admin::user()->id)->where('active', 1)->first();
        $event_id = $event->id;
        $chartData = [];
        $routes = Route::where('event_id', $event_id)->get();
        $gender = 'male';
        foreach ($routes as $route) {
            $all_passed = ResultRouteQualificationClassic::where('event_id', $event_id)
                ->where('grade', $route->grade)
                ->where('route_id', $route->route_id)
                ->whereIn('attempt', [
                    ResultRouteQualificationClassic::STATUS_PASSED_FLASH,
                    ResultRouteQualificationClassic::STATUS_PASSED_REDPOINT,
                    ResultRouteQualificationClassic::STATUS_ZONE])
                ->count();

            $coefficient = EventAndCoefficientRoute::where('event_id', $event_id)
                ->where('route_id', $route->route_id)
                ->first()
                ->{'coefficient_' . $gender};

            $flash = ResultRouteQualificationClassic::where('event_id', $event_id)
                ->where('gender', $gender)
                ->where('grade', $route->grade)
                ->where('route_id', $route->route_id)
                ->where('attempt', ResultRouteQualificationClassic::STATUS_PASSED_FLASH)
                ->count();

            $redpoint = ResultRouteQualificationClassic::where('event_id', $event_id)
                ->where('gender', $gender)
                ->where('grade', $route->grade)
                ->where('route_id', $route->route_id)
                ->where('attempt', ResultRouteQualificationClassic::STATUS_PASSED_REDPOINT)
                ->count();

            $chartData[] = [
                'route_id' => $route->route_id,
                'grade' => $route->grade,
                'flash' => $flash,
                'redpoint' => $redpoint,
                'all_passed' => $all_passed,
                'coefficient' => $coefficient
            ];
        }
        return view('admin.charts.analytics-result', compact('chartData'));
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

    protected function event_analytics()
    {
        $grid = new Grid(new Event);
        if (!Admin::user()->isAdministrator()) {
            $grid->model()->where('owner_id', '=', Admin::user()->id);
        } else {
            $grid->column('owner_id', 'Owner')->editable();
        }
        $grid->model()->where('active', 1);
        $grid->actions(function ($actions){
            $actions->disableEdit();
            $actions->disableView();
            $actions->disableDelete();
        });
        $event = Event::where('owner_id', '=', Admin::user()->id)->where('active', 1)->first();
        // Отключаем ненужные элементы
        $grid->disableFilter();
        $grid->disableBatchActions();
        $grid->disableColumnSelector();
        $grid->disableExport();
        $grid->disableCreateButton();
        $grid->disablePagination();
        $grid->column('title', 'Соревы')->expand(function () use ($event) {
            $headers = ['Трасса','Категория','Флеши','Редпоинты','Всего прохождений(М + Ж)','Коэффициент трассы'];

            $style = ['table-bordered','table-hover', 'table-striped'];

            $options = [
                'responsive' => true,
                'paging' => true,
                'lengthChange' => true,
                'searching' => true,
                'ordering' => true,
                'info' => true,
                'autoWidth' => true,
                'deferRender' => true,
                'processing' => true,
            ];
            $event_id = $event->id;
            $stats = [];
            $routes = Route::where('event_id', $event_id)->get();
            $gender = 'male';
            foreach ($routes as $route){
                $all_passed = ResultRouteQualificationClassic::where('event_id', $event_id)
                    ->where('grade', $route->grade)
                    ->where('route_id', $route->route_id)
                    ->whereIn('attempt', [
                        ResultRouteQualificationClassic::STATUS_PASSED_FLASH,
                        ResultRouteQualificationClassic::STATUS_PASSED_REDPOINT,
                        ResultRouteQualificationClassic::STATUS_ZONE])
                    ->get()->count();
                if($gender == 'male'){
                    $coefficient = EventAndCoefficientRoute::where('event_id', $event_id)
                        ->where('route_id', $route->route_id)
                        ->first()->coefficient_male;
                } else {
                    $coefficient = EventAndCoefficientRoute::where('event_id', $event_id)
                        ->where('route_id', $route->route_id)
                        ->first()->coefficient_female;
                }

                $flash = ResultRouteQualificationClassic::where('event_id', $event_id)
                    ->where('gender', $gender)
                    ->where('grade', $route->grade)
                    ->where('route_id', $route->route_id)
                    ->where('attempt', ResultRouteQualificationClassic::STATUS_PASSED_FLASH)
                    ->get()->count();
                $redpoint = ResultRouteQualificationClassic::where('event_id', $event_id)
                    ->where('gender', $gender)
                    ->where('grade', $route->grade)
                    ->where('route_id', $route->route_id)
                    ->where('attempt', ResultRouteQualificationClassic::STATUS_PASSED_REDPOINT)
                    ->get()->count();

                $stats[] =  array(
                    'route_id' => $route->route_id,
                    'grade' => $route->grade,
                    'flash' => $flash,
                    'redpoint' => $redpoint,
                    'all_passed' => $all_passed,
                    'coefficient' => $coefficient
                );
            }
            return new DataTable($headers, $stats, $style, $options);
        });

        return $grid;
    }


    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function france_system_routes()
    {
        $grid = new Grid(new ResultFranceSystemQualification);
        if (!Admin::user()->isAdministrator()) {
            $grid->model()->where('owner_id', '=', Admin::user()->id);
        }
        $grid->model()->where(function ($query) {
            $query->has('event.result_france_system_qualification');
        });
        $grid->disableFilter();
        $grid->disableBatchActions();
        $grid->disableColumnSelector();
        $grid->disableExport();
        $grid->disablePagination();
        $grid->disableCreateButton();
        $grid->column('text', 'Статус')->default('Пока доступна только для фестивальной системы');
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

        $event = Event::where('owner_id', '=', Admin::user()->id)->where('active', '=', 1)->first();
        if($event->type_event){
            $grid->column('route_name', 'Трасса');
            $grid->column('grade', 'Категория трассы');
            $grid->column('value', 'Ценность трассы');
        } else {
            $grid->column('route_id', 'Номер трассы')->editable();
            $grid->column('grade', 'Категория трассы')->select(Grades::getGrades());
            if($event->mode == 1){
                $grid->column('value', 'Ценность трассы');
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
            $actions->disableDelete();
            $actions->append(<<<EOT
<a href="javascript:void(0);" data-id="route-{$actions->getKey()}" class="grid-row-delete btn btn-xs btn-danger">
    <i class="fa fa-trash"></i> Удалить
</a>
EOT
            );

        });
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
            $grid->column('route_name', 'Трасса');
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
                $form->tableamount('grade_and_amount', '', function ($table) use ($event) {
                    $grades = Grades::getGrades();
                    $table->select('Категория')->attribute('inputmode', 'none')->options($grades)->readonly();
                    if (!$event->type_event) {
                        $table->number('Кол-во')->attribute('inputmode', 'none')->width('50px');
                        if ($event->mode == 1) {
                            $table->text('Ценность')->width('60px');
                            if ($event->is_zone_show) {
                                $table->text('Ценность зоны')->width('60px');
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
                if($type !== 'update') {
                    $owner_id = Admin::user()->id;
                    $event = Event::where('owner_id', '=', $owner_id)->where('active', '=', 1)->first();
                    $exist_routes_list = Route::where('event_id', '=', $event->id)->first();
                    $exist_routes_outdoor_list = RoutesOutdoor::where('event_id', '=', $event->id)->first();
                    if (!$exist_routes_list || !$exist_routes_outdoor_list) {
                        if ($form->count_routes) {
                            if($event->type_event){
                                Route::generation_outdoor_route($event->id, $form->place_id, $form->area_id, $form->rocks_id, $form->grade_and_amount);
                            } else {
                                Route::generation_route($owner_id, $event->id, $form->count_routes, $form->grade_and_amount);
                            }
                        }
                    } else {
                        if($event->type_event) {
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
