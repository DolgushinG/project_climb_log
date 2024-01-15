<?php

use Encore\Admin\Facades\Admin;
use Illuminate\Routing\Router;

Admin::routes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
    'as'            => config('admin.route.prefix') . '.',
], function (Router $router) {
    $router->resource('/auth/users', UserController::class);

    $router->get('/', 'HomeController@index')->name('home');
    Route::middleware(['owner'])->group(function ($router) {
        $router->resource('events', EventsController::class);
        $router->resource('participants', ParticipantsController::class);
        $router->resource('grades', GradesController::class);
        $router->resource('formats', FormatsController::class);
        $router->resource('result-route-final-stage', ResultRouteFinalStageController::class);
        $router->resource('participants-categories', ParticipantCategoriesController::class);
        $router->resource('sets', SetsController::class);
        $router->resource('event-and-coefficient-route',  EventAndCoefficientsRoutesController::class);
        $router->get('exports/events/excel/qualification/{id}', 'ParticipantsController@exportQualificationExcel')->name('exportQualificationExcel');
        $router->get('exports/events/csv/qualification/{id}', 'ParticipantsController@exportQualificationCsv')->name('exportQualificationCsv');
        $router->get('exports/events/ods/qualification/{id}', 'ParticipantsController@exportQualificationOds')->name('exportQualificationOds');
        $router->get('exports/events/excel/final/{id}', 'ResultRouteFinalStageController@exportFinalExcel')->name('exportFinalExcel');
        $router->get('exports/events/csv/final/{id}', 'ResultRouteFinalStageController@exportFinalCsv')->name('exportFinalCsv');
        $router->get('exports/events/ods/final/{id}', 'ResultRouteFinalStageController@exportFinalOds')->name('exportFinalOds');
    });

});
