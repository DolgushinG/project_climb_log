<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [App\Http\Controllers\Controller::class, 'main'])->name('main');
Route::get('/competition', function () {
    return view('welcome');
});

//Route::get('/event/{id}', [App\Http\Controllers\EventsController::class, 'show']);
Route::get('/event/{climbing_gym}/{title}', [App\Http\Controllers\EventsController::class, 'show']);
Route::get('/event/{climbing_gym}/{title}/participants', [App\Http\Controllers\EventsController::class, 'get_participants'])->name('participants');
Route::get('/event/{climbing_gym}/{title}/final/results', [App\Http\Controllers\EventsController::class, 'get_final_results'])->name('final_results');
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'index'])->name('profile');
    Route::get('/getProfileOverview', [App\Http\Controllers\ProfileController::class, 'getTabContentOverview'])->name('getProfileOverview');
    Route::get('/getProfileSetting', [App\Http\Controllers\ProfileController::class, 'getTabContentSetting'])->name('getTabContentSetting');
    Route::get('/getProfileEdit', [App\Http\Controllers\ProfileController::class, 'getTabContentEdit'])->name('getTabContentEdit');
    Route::get('/getProfileEvents', [App\Http\Controllers\ProfileController::class, 'getTabContentEvents'])->name('getTabContentEvents');
    Route::post('/takePart', [App\Http\Controllers\EventsController::class, 'store'])->name('takePart');
    Route::post('/sendResultParticipant', [App\Http\Controllers\EventsController::class, 'sendResultParticipant'])->name('sendResultParticipant');
    Route::get('/routes/event/{title}', [App\Http\Controllers\EventsController::class, 'listRoutesEvent'])->name('listRoutesEvent');
});

require __DIR__.'/auth.php';
