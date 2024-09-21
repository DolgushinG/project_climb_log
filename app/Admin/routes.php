<?php

use App\Models\Grades;
use App\Models\ResultFranceSystemQualification;
use App\Models\ResultRouteFranceSystemQualification;
use App\Models\User;
use Encore\Admin\Facades\Admin;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Response;


Admin::routes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
    'as'            => config('admin.route.prefix') . '.',
], function (Router $router) {
    $router->resource('/auth/users', UserController::class);
    $router->get('/grades/api/get_places', function(Request $request) {
        $country_id = $request->get('option');
        $country = \App\Models\Country::find($country_id);
        return \App\Models\Place::where('country_id', $country->id)->get(['id', DB::raw('name as text')]);
    });
    $router->get('/grades/api/get_place_routes', function(Request $request) {
        $place_id = $request->get('option');
        $place = \App\Models\Place::find($place_id);
        return \App\Models\Area::where('place_id', $place->id)->get(['id', DB::raw('name as text')]);
    });
    $router->get('/grades/api/get_rocks', function(Request $request) {
        $area_id = $request->get('option');
        $area = \App\Models\Area::find($area_id);
        return \App\Models\PlaceRoute::where('area_id', $area->id)->get(['id', DB::raw('name as text')]);
    });

    $router->middleware(['throttle:get_users'])->get('/api/get_users', function(Request $request) {
        $eventId = $request->get('eventId');
        $participant_users_id = ResultFranceSystemQualification::where('event_id', $eventId)->pluck('user_id')->toArray();
        $result = User::whereIn('id', $participant_users_id)->pluck('middlename','id');
        $amount_routes = Grades::where('event_id', $eventId)->first();
        if($amount_routes){
            $amount_routes = $amount_routes->count_routes;
        } else {
            $amount_routes = 0;
        }
        // Преобразуем формат и сортируем по алфавиту
        $sortedUsers = $result->mapWithKeys(function ($middlename, $id) use($eventId, $amount_routes) {
            $result_user = ResultRouteFranceSystemQualification::where('event_id', $eventId)->where('user_id', $id);
            $routes = $result_user->get()->sortBy('route_id')->pluck('route_id')->toArray();
            $string_version = '';
            foreach ($routes as $value) {
                $string_version .= $value . ', ';
            }
            if($result_user->get()->count() == $amount_routes){
                $str = ' [Добавлены все трассы]';
            } else {
                $str =  ' [Трассы: '.$string_version.']';
            }
            return [$id => $middlename. $str ];
        })->toArray();

        return response()->json($sortedUsers ?? []);
    });
    $router->middleware(['throttle:get_attempts'])->get('/api/get_attempts', function(Request $request) {
        $routeId = $request->get('route_id');
        $userId = $request->get('user_id');
        $eventId = $request->get('event_id');
        $result = \App\Models\ResultRouteFranceSystemQualification::where('event_id', $eventId)->where('route_id', $routeId)->where('user_id', $userId)->first();
        if($result){
            $data = [
                'all_attempts' => $result->all_attempts,
                'amount_try_top' => $result->amount_try_top,
                'amount_try_zone' => $result->amount_try_zone,
            ];
        } else {
            $data = [];
        }
        return response()->json($data);
    });
    $router->middleware(['throttle:set_attempts'])->get('/api/set_attempts', function(Request $request) {
        $routeId = $request->get('route_id');
        $userId = $request->get('user_id');
        $eventId = $request->get('event_id');
        $attempt = $request->get('attempt');
        $amount_try_top = intval($request->get('amount_try_top'));
        $amount_try_zone = intval($request->get('amount_try_zone'));
        if($amount_try_top > 0){
            $amount_top  = 1;
        } else {
            $amount_top  = 0;
        }
        if($amount_try_zone > 0){
            $amount_zone  = 1;
        } else {
            $amount_zone  = 0;
        }
        $result_reg = ResultFranceSystemQualification::where('event_id', $eventId)->where('user_id', $userId)->first();
        ResultFranceSystemQualification::update_france_route_results(
            owner_id: $result_reg->owner_id,
            event_id: $eventId,
            category_id: $result_reg->category_id ?? null,
            route_id: $routeId,
            user_id: $userId,
            amount_try_top: $amount_try_top,
            amount_try_zone: $amount_try_zone,
            amount_top: $amount_top,
            amount_zone: $amount_zone,
            gender: $result_reg->gender,
            all_attempts: $attempt,
            number_set_id: $result_reg->number_set_id ?? null
        );
        $result = \App\Models\ResultRouteFranceSystemQualification::where('event_id', $eventId)->where('route_id', $routeId)->where('user_id', $userId)->first();
        $data = [
            'all_attempts' => $result->all_attempts,
            'amount_try_top' => $result->amount_try_top,
            'amount_try_zone' => $result->amount_try_zone,
        ];
        return response()->json($data);
    });
    $router->middleware(['throttle:get_attempts'])->get('/api/final/get_attempts', function(Request $request) {
        $routeId = $request->get('route_id');
        $userId = $request->get('user_id');
        $eventId = $request->get('event_id');
        $result = \App\Models\ResultRouteFinalStage::where('event_id', $eventId)->where('final_route_id', $routeId)->where('user_id', $userId)->first();
        if($result){
            $data = [
                'all_attempts' => $result->all_attempts,
                'amount_try_top' => $result->amount_try_top,
                'amount_try_zone' => $result->amount_try_zone,
            ];
        } else {
            $data = [];
        }
        return response()->json($data);
    });
    $router->middleware(['throttle:get_attempts'])->get('/api/semifinal/get_attempts', function(Request $request) {
        $routeId = $request->get('route_id');
        $userId = $request->get('user_id');
        $eventId = $request->get('event_id');
        $result = \App\Models\ResultRouteSemiFinalStage::where('event_id', $eventId)->where('final_route_id', $routeId)->where('user_id', $userId)->first();
        if($result){
            $data = [
                'all_attempts' => $result->all_attempts,
                'amount_try_top' => $result->amount_try_top,
                'amount_try_zone' => $result->amount_try_zone,
            ];
        } else {
            $data = [];
        }
        return response()->json($data);
    });
    $router->get('/', 'HomeController@index')->name('home');
    Route::middleware(['owner'])->group(function ($router) {
        $router->resource('events', EventsController::class);
        $router->resource('/posts', PostsController::class);
        $router->get('events/clone/{id}', 'EventsController@cloneEvent')->name('cloneEvent');
        $router->resource('result-qualification', ResultQualificationController::class);
        $router->resource('map', MapController::class);
        $router->resource('analytics', AnalyticsController::class);
        $router->resource('grades', GradesController::class);
        $router->resource('formats', FormatsController::class);
        $router->resource('colors', ColorController::class);
        $router->resource('semifinal-stage', ResultRouteSemiFinalStageController::class);
        $router->resource('final-stage', ResultRouteFinalStageController::class);
        $router->resource('sets', SetsController::class);
        $router->resource('owner-payments', OwnerPaymentsController::class);
        $router->resource('event-and-coefficient-route',  EventAndCoefficientsRoutesController::class);
        $router->get('exports/events/excel/qualification/{id}', 'ResultQualificationController@exportQualificationExcel')->name('exportQualificationExcel');
        $router->get('exports/events/card-france-system/participant/{id}', 'ResultQualificationController@cardParticipantFranceSystemExcel')->name('cardParticipantFranceSystemExcel');
        $router->get('exports/events/card-festival/participant/{id}', 'ResultQualificationController@cardParticipantFestivalExcel')->name('cardParticipantFestivalExcel');
        $router->get('exports/events/list/participant/{id}', 'ResultQualificationController@listParticipantExcel')->name('listParticipantExcel');
        $router->get('exports/start-protocol/events/{event_id}/participant/{category_id}', 'ResultQualificationController@startProtocolParticipantExcel')->name('startProtocolParticipantExcel');
        $router->get('exports/events/{event_id}/final/participants', 'ResultRouteFinalStageController@finalParticipantExcel')->name('finalParticipantExcel');
        $router->get('exports/events/{event_id}/semifinal/participants', 'ResultRouteSemiFinalStageController@semifinalParticipantExcel')->name('semifinalParticipantExcel');
        $router->get('exports/events/{event_id}/{stage}/{set_id}/{gender}/{category_id}', 'ResultQualificationController@protocolRouteExcel')->name('protocolRouteExcel');
        $router->get('exports/events/csv/qualification/{id}', 'ResultQualificationController@exportQualificationCsv')->name('exportQualificationCsv');
        $router->get('exports/events/ods/qualification/{id}', 'ResultQualificationController@exportQualificationOds')->name('exportQualificationOds');
        $router->get('exports/events/excel/semifinal/{id}', 'ResultRouteSemiFinalStageController@exportSemiFinalExcel')->name('exportSemiFinalExcel');
        $router->get('exports/events/csv/semifinal/{id}', 'ResultRouteSemiFinalStageController@exportSemiFinalCsv')->name('exportSemiFinalCsv');
        $router->get('exports/events/ods/semifinal/{id}', 'ResultRouteSemiFinalStageController@exportSemiFinalOds')->name('exportSemiFinalOds');
        $router->get('exports/events/excel/final/{id}', 'ResultRouteFinalStageController@exportFinalExcel')->name('exportFinalExcel');
        $router->get('exports/events/excel/qualification-final/{id}', 'ResultQualificationController@exportFranceSystemQualificationExcel')->name('exportFranceSystemQualificationExcel');
        $router->get('exports/events/csv/final/{id}', 'ResultRouteFinalStageController@exportFinalCsv')->name('exportFinalCsv');
        $router->get('exports/events/ods/final/{id}', 'ResultRouteFinalStageController@exportFinalOds')->name('exportFinalOds');
        $router->get('exports/events/excel/all/{id}', 'EventsController@exportAllExcel')->name('exportAllExcel');
        $router->get('exports/events/csv/all/{id}', 'EventsController@exportAllCsv')->name('exportAllCsv');
        $router->get('exports/events/ods/all/{id}', 'EventsController@exportAllOds')->name('exportAllOds');
        $router->get('reject/bill/event/{event_id}/participant/{id}', 'ResultQualificationController@rejectBill')->name('rejectBill');
    });

});
