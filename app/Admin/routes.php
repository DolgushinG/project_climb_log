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
        $router->resource('result-qualification', ResultQualificationController::class);
        $router->resource('grades', GradesController::class);
        $router->resource('formats', FormatsController::class);
        $router->resource('semifinal-stage', ResultRouteSemiFinalStageController::class);
        $router->resource('final-stage', ResultRouteFinalStageController::class);
        $router->resource('sets', SetsController::class);
        $router->resource('owner-payments', OwnerPaymentsController::class);
        $router->resource('event-and-coefficient-route',  EventAndCoefficientsRoutesController::class);
        $router->get('exports/events/excel/qualification/{id}', 'ResultQualificationController@exportQualificationExcel')->name('exportQualificationExcel');
        $router->get('exports/events/card-france-system/participant/{id}', 'ResultQualificationController@cardParticipantFranceSystemExcel')->name('cardParticipantFranceSystemExcel');
        $router->get('exports/events/card-festival/participant/{id}', 'ResultQualificationController@cardParticipantFestivalExcel')->name('cardParticipantFestivalExcel');
        $router->get('exports/events/list/participant/{id}', 'ResultQualificationController@listParticipantExcel')->name('listParticipantExcel');
        $router->get('exports/events/{event_id}/final/participants', 'ResultRouteFinalStageController@finalParticipantExcel')->name('finalParticipantExcel');
        $router->get('exports/events/{event_id}/semifinal/participants', 'ResultRouteSemiFinalStageController@semifinalParticipantExcel')->name('semifinalParticipantExcel');
        $router->get('exports/events/{event_id}/{stage}/{set_id}/{gender}/{category_id}', 'ResultQualificationClassicController@protocolRouteExcel')->name('protocolRouteExcel');
        $router->get('exports/events/csv/qualification/{id}', 'ResultQualificationController@exportQualificationCsv')->name('exportQualificationCsv');
        $router->get('exports/events/ods/qualification/{id}', 'ResultQualificationController@exportQualificationOds')->name('exportQualificationOds');
        $router->get('exports/events/excel/semifinal/{id}', 'ResultRouteSemiFinalStageController@exportSemiFinalExcel')->name('exportSemiFinalExcel');
        $router->get('exports/events/csv/semifinal/{id}', 'ResultRouteSemiFinalStageController@exportSemiFinalCsv')->name('exportSemiFinalCsv');
        $router->get('exports/events/ods/semifinal/{id}', 'ResultRouteSemiFinalStageController@exportSemiFinalOds')->name('exportSemiFinalOds');
        $router->get('exports/events/excel/final/{id}', 'ResultRouteFinalStageController@exportFinalExcel')->name('exportFinalExcel');
        $router->get('exports/events/excel/qualification-final/{id}', 'ResultQualificationController@exportQualificationLikeFinalExcel')->name('exportQualificationLikeFinalExcel');
        $router->get('exports/events/csv/final/{id}', 'ResultRouteFinalStageController@exportFinalCsv')->name('exportFinalCsv');
        $router->get('exports/events/ods/final/{id}', 'ResultRouteFinalStageController@exportFinalOds')->name('exportFinalOds');
        $router->get('exports/events/excel/all/{id}', 'EventsController@exportAllExcel')->name('exportAllExcel');
        $router->get('exports/events/csv/all/{id}', 'EventsController@exportAllCsv')->name('exportAllCsv');
        $router->get('exports/events/ods/all/{id}', 'EventsController@exportAllOds')->name('exportAllOds');
        $router->get('reject/bill/event/{event_id}/participant/{id}', 'ResultQualificationController@rejectBill')->name('rejectBill');
    });

});
