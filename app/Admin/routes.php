<?php

use Illuminate\Routing\Router;

Admin::routes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
    'as'            => config('admin.route.prefix') . '.',
], function (Router $router) {

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
        $router->get('exports/events/excel/{id}', 'ParticipantsController@exportExcel')->name('exportExcel');
        $router->get('exports/events/csv/{id}', 'ParticipantsController@exportCsv')->name('exportCsv');
        $router->get('exports/events/ods/{id}', 'ParticipantsController@exportCsv')->name('exportOds');
    });

});
