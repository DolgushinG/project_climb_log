<?php

namespace App\Admin\Controllers;

use App\Models\Event;
use App\Models\EventAndCoefficientRoute;
use App\Http\Controllers\Controller;
use App\Models\ResultRouteQualificationClassic;
use App\Models\Route;
use App\Models\RoutesOutdoor;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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
                    if(!$event->is_france_system_qualification){
                        $row->column(5, function (Column $column) use ($event) {
                            $column->row($this->gridWithTitle('Женщины', 'female'));
                        });
                        $row->column(5, function (Column $column) use ($event) {
                            $column->row($this->gridWithTitle('Мужчины', 'male'));
                        });
                        $row->column(10, function (Column $column) use ($event) {
                            $column->row($this->event_analytics());
                        });
                    }
                }
            });
    }
    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public static function grid($gender)
    {
        $event = Event::where('owner_id', '=', Admin::user()->id)->where('active', 1)->first();
        $chartData = Cache::remember('result_'.$gender.'_analytics_cache_event_id_'.$event->id, 60 * 60, function () use ($event, $gender) {
            return self::get_stats_gender($event->id, $gender);
        });
        return view('admin.charts.analytics-result-'.$gender, compact('chartData'));
    }
    protected function gridWithTitle($title, $gender)
    {
        $grid = $this->grid($gender); // Возвращает таблицу данных для указанного пола

        $html = '<div class="panel panel-default">';
        $html .= '<div class="panel-heading"><h3 class="panel-title">' . $title . '</h3></div>';
        $html .= '<div class="panel-body">';
        $html .= $grid;
        $html .= '</div></div>';

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
        $grid->disableFilter();
        $grid->disableBatchActions();
        $grid->disableColumnSelector();
        $grid->disableExport();
        $grid->disableCreateButton();
        $grid->disablePagination();

        $grid->column('title', 'Соревы')->expand(function () use ($event) {
            $headers = ['Трасса','Категория','Флеши','Редпоинты','Всего прохождений(М + Ж)','Коэффициент трассы', 'Пол'];
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
                'orderMulti'=> true,
            ];
            $stats = Cache::remember('result_analytics_cache_event_id_'.$event->id, 60 * 60, function () use ($event) {
                return self::get_stats($event->id);
            });
            return new DataTable($headers, $stats, $style, $options);
        });

        return $grid;
    }
    public static function get_stats($event_id)
    {
        $stats = [];
        $event = Event::find($event_id);
        if($event->type_event){
            $routes = RoutesOutdoor::where('event_id', $event_id)->get();
        } else {
            $routes = Route::where('event_id', $event_id)->get();
        }
        foreach ($routes as $route){
            foreach (['male', 'female'] as $gender) {
                $all_passed = ResultRouteQualificationClassic::where('event_id', $event_id)
                    ->where('grade', $route->grade)
                    ->where('route_id', $route->route_id)
                    ->whereIn('attempt', [
                        ResultRouteQualificationClassic::STATUS_PASSED_FLASH,
                        ResultRouteQualificationClassic::STATUS_PASSED_REDPOINT,
                        ResultRouteQualificationClassic::STATUS_ZONE])
                    ->get()->count();

                $coefficient = EventAndCoefficientRoute::where('event_id', $event_id)
                    ->where('route_id', $route->route_id)
                    ->first();
                if($coefficient){
                    $coefficient = $coefficient->{'coefficient_' . $gender};
                } else {
                    $coefficient = 0;
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
                    'coefficient' => $coefficient,
                    'gender' => $gender == 'male' ? 'Мужчина' : 'Женщина',
                );
            }
        }
        return $stats;
    }

    public static function get_stats_gender($event_id, $gender)
    {
        $stats = [];
        $event = Event::find($event_id);
        if($event->type_event){
            $routes = RoutesOutdoor::where('event_id', $event_id)->get();
        } else {
            $routes = Route::where('event_id', $event_id)->get();
        }
        foreach ($routes as $route){
            $all_passed = ResultRouteQualificationClassic::where('event_id', $event_id)
                ->where('grade', $route->grade)
                ->where('route_id', $route->route_id)
                ->whereIn('attempt', [
                    ResultRouteQualificationClassic::STATUS_PASSED_FLASH,
                    ResultRouteQualificationClassic::STATUS_PASSED_REDPOINT,
                    ResultRouteQualificationClassic::STATUS_ZONE])
                ->get()->count();

            $coefficient = EventAndCoefficientRoute::where('event_id', $event_id)
                ->where('route_id', $route->route_id)
                ->first();
            if($coefficient){
                $coefficient = $coefficient->{'coefficient_' . $gender};
            } else {
                $coefficient = 0;
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
                'coefficient' => $coefficient,
            );
        }
        return $stats;
    }
}
