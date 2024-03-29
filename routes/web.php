<?php

use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;

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
Route::get('/auth/telegram/callback', [App\Http\Controllers\SocialiteController::class, 'callback_telegram']);
Route::get('/auth/vkontakte/callback', [App\Http\Controllers\SocialiteController::class, 'callback_vkontakte']);
Route::get('/auth/yandex/callback', [App\Http\Controllers\SocialiteController::class, 'callback_yandex']);
Route::get('/auth/telegram/redirect', function (){
    return Socialite::driver('telegram')->redirect();
});
Route::get('/auth/vkontakte/redirect', function (){
    return Socialite::driver('vkontakte')->redirect();
});
Route::get('/auth/yandex/redirect', function (){
    return Socialite::driver('yandex')->redirect();
});
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
    Route::post('/editChanges', [App\Http\Controllers\ProfileController::class, 'editChanges'])->name('editChanges');
    Route::post('/change-password', [App\Http\Controllers\ProfileController::class,'changePassword'])->name('changePassword');
    Route::get('/getProfileEvents', [App\Http\Controllers\ProfileController::class, 'getTabContentEvents'])->name('getTabContentEvents');
    Route::post('/takePart', [App\Http\Controllers\EventsController::class, 'store'])->name('takePart');
    Route::post('/changeSet', [App\Http\Controllers\EventsController::class, 'changeSet'])->name('changeSet');
    Route::post('/sendResultParticipant', [App\Http\Controllers\EventsController::class, 'sendResultParticipant'])->name('sendResultParticipant');
    Route::get('/routes/event/{title}/list-routes-event', [App\Http\Controllers\EventsController::class, 'listRoutesEvent'])->name('listRoutesEvent');
});

require __DIR__.'/auth.php';
