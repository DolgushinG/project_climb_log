<?php

namespace App\Admin\Controllers;

use App\Models\Event;
use App\Models\EventAndCoefficientRoute;
use App\Http\Controllers\Controller;
use App\Models\ParticipantCategory;
use App\Models\ResultQualificationClassic;
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
            $headers = ['Трасса','Категория','Флеши','Редпоинты','% флешей','% редпоинтов','оценка','Всего прохождений(М + Ж)','Коэффициент трассы', 'Пол'];
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
            $stats = Cache::remember('result_analytics_cache_event_id_'.$event->id, 24 * 60 * 60, function () use ($event) {
                return self::get_stats($event->id);
            });
            return new DataTable($headers, $stats, $style, $options);
        });

        return $grid;
    }
    public static function get_stats($event_id)
    {
        // Получаем информацию о событии и маршрутах
        $event = Event::find($event_id);
        $routeModel = $event->type_event ? RoutesOutdoor::class : Route::class;
        $routes = $routeModel::where('event_id', $event_id)->get();

        // Предварительно загружаем коэффициенты
        $coefficients = EventAndCoefficientRoute::where('event_id', $event_id)
            ->get()
            ->keyBy('route_id');

        // Подготовка данных для расчета
        $results = ResultRouteQualificationClassic::where('event_id', $event_id)
            ->whereIn('attempt', [
                ResultRouteQualificationClassic::STATUS_PASSED_FLASH,
                ResultRouteQualificationClassic::STATUS_PASSED_REDPOINT,
                ResultRouteQualificationClassic::STATUS_ZONE
            ])
            ->get()
            ->groupBy(function($item) {
                return $item->route_id . '-' . $item->grade . '-' . $item->gender . '-' . $item->attempt;
            });

        $stats = [];

        foreach ($routes as $route) {
            foreach (['male', 'female'] as $gender) {
                $route_key = $route->route_id . '-' . $route->grade . '-' . $gender;

                // Получаем данные из предварительно загруженных результатов
                $all_passed = $results->get($route_key . '-' . ResultRouteQualificationClassic::STATUS_PASSED_FLASH, collect())->count()
                    + $results->get($route_key . '-' . ResultRouteQualificationClassic::STATUS_PASSED_REDPOINT, collect())->count()
                    + $results->get($route_key . '-' . ResultRouteQualificationClassic::STATUS_ZONE, collect())->count();

                $flash = $results->get($route_key . '-' . ResultRouteQualificationClassic::STATUS_PASSED_FLASH, collect())->count();
                $redpoint = $results->get($route_key . '-' . ResultRouteQualificationClassic::STATUS_PASSED_REDPOINT, collect())->count();

                // Получаем коэффициент
                $coefficient = $coefficients->get($route->route_id);
                $coefficient_value = $coefficient ? $coefficient->{'coefficient_' . $gender} : 0;

                // Рассчитываем процентные значения
                $flash_percentage = self::getFlashPercentagesForGender($all_passed, $flash);
                $redpoint_percentage = self::getRedpointPercentagesForGender($all_passed, $redpoint);

                // Анализируем сложность маршрута
                $difficulty = self::analyzeRouteDifficulty($flash, $redpoint, $all_passed, $flash_percentage, $redpoint_percentage);

                $stats[] = [
                    'route_id' => $route->route_id,
                    'grade' => $route->grade,
                    'flash' => $flash,
                    'redpoint' => $redpoint,
                    'flash_percentage' => $flash_percentage,
                    'redpoint_percentage' => $redpoint_percentage,
                    'difficulty' => $difficulty,
                    'all_passed' => $all_passed,
                    'coefficient' => $coefficient_value,
                    'gender' => $gender == 'male' ? 'Мужчина' : 'Женщина',
                ];
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
            $flash_percentage = self::getFlashPercentagesForGender($all_passed, $flash);
            $redpoint_percentage = self::getRedpointPercentagesForGender($all_passed, $redpoint);
            $difficulty = self::analyzeRouteDifficulty($flash, $redpoint, $all_passed, $flash_percentage, $redpoint_percentage);
            $stats[] =  array(
                'route_id' => $route->route_id,
                'grade' => $route->grade,
                'flash' => $flash,
                'redpoint' => $redpoint,
                'flash_percentage' => $flash_percentage,
                'redpoint_percentage' => $redpoint_percentage,
                'difficulty' => $difficulty,
                'all_passed' => $all_passed,
                'coefficient' => $coefficient,
            );
        }
        return $stats;
    }
    public static function getFlashPercentagesForGender($total, $flash_counts)
    {
        if($total > 0){
            $flash_percentage = round(($flash_counts / $total) * 100, 2);
        } else {
            $flash_percentage = 0;
        }
        return $flash_percentage;
    }

    public static function getRedpointPercentagesForGender($total, $redpoint_counts)
    {
        if($total > 0){
            $redpoint_percentage = round(($redpoint_counts / $total) * 100, 2);
        } else {
            $redpoint_percentage = 0;
        }
        return $redpoint_percentage;
    }
    public static function analyzeRouteDifficulty($amount_flash, $amount_redpoint, $total, $flashPercentage, $redpointPercentage) {
        if ($total == 0) {
            return 'Не определено';
        }
        // Определение пороговых значений для различий
        // Пороговые значения теперь более интуитивные
        $flash_thresholds = [
            'Слишком легкая' => 90,  // 90% и выше считается слишком легкой
            'Легкая' => 75,          // 75% - 89% считается легкой
            'Сбалансированная' => 50, // 50% - 74% считается сбалансированной
            'Сложная' => 25,         // 25% - 49% считается сложной
            'Слишком сложная' => 0   // Менее 25% считается слишком сложной
        ];

        $redpoint_thresholds = [
            'Слишком легкая' => 0,   // 0% редпоинтов может указывать на легкость трассы
            'Легкая' => 10,          // 1% - 10% редпоинтов может быть приемлемым для легкой трассы
            'Сбалансированная' => 20, // 11% - 20% редпоинтов может указывать на сбалансированную сложность
            'Сложная' => 30,         // 21% - 30% редпоинтов указывает на сложность
            'Слишком сложная' => 31  // Более 30% редпоинтов считается слишком сложной
        ];

        // Определение сложности трассы на основе разницы
        $difficulty_flash = 'Сбалансированная';
        foreach ($flash_thresholds as $level => $threshold) {
            if ($flashPercentage >= $threshold) {
                $difficulty_flash = $level;
                break;
            }
        }

        $difficulty_redpoint = 'Сбалансированная';
        foreach ($redpoint_thresholds as $level => $threshold) {
            if ($redpointPercentage <= $threshold) {
                $difficulty_redpoint = $level;
                break;
            }
        }

        // Определение общего уровня сложности
        if ($difficulty_flash === 'Слишком легкая' || $difficulty_redpoint === 'Слишком сложная') {
            $difficulty = 'Слишком легкая';
        } elseif ($difficulty_flash === 'Легкая' || $difficulty_redpoint === 'Сложная') {
            $difficulty = 'Легкая';
        } elseif ($difficulty_flash === 'Сложная' || $difficulty_redpoint === 'Легкая') {
            $difficulty = 'Сложная';
        } elseif ($difficulty_flash === 'Слишком сложная' || $difficulty_redpoint === 'Слишком легкая') {
            $difficulty = 'Слишком сложная';
        } else {
            $difficulty = 'Сбалансированная';
        }
        return $difficulty;

    }
}
