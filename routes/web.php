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

Route::get('/list-events', [App\Http\Controllers\Controller::class, 'list_events'])->name('list_events');
Route::get('/auth/telegram/callback', [App\Http\Controllers\SocialiteController::class, 'callback_telegram']);
Route::get('/auth/vkontakte/callback', [App\Http\Controllers\SocialiteController::class, 'callback_vkontakte']);
Route::get('/auth/yandex/callback', [App\Http\Controllers\SocialiteController::class, 'callback_yandex']);
Route::get('/auth/telegram/redirect', function (){
    return Socialite::driver('telegram')->stateless()->redirect();
});
Route::get('/auth/vkontakte/redirect', function (){
    return Socialite::driver('vkontakte')->stateless()->redirect();
});
Route::get('/auth/yandex/redirect', function (){
    return Socialite::driver('yandex')->stateless()->redirect();
});
Route::get('/competition', function () {
    return view('welcome');
});
Route::get('/privacyconf', [App\Http\Controllers\Controller::class, 'indexPrivacy'])->name('privacyconf');
Route::get('/privatedata', [App\Http\Controllers\Controller::class, 'indexPrivacyData'])->name('privatedata');
//Route::get('/event/{id}', [App\Http\Controllers\EventsController::class, 'show']);

Route::get('/posts', [App\Http\Controllers\PostsController::class, 'index'])->name('posts');
Route::get('/post/{post}', [App\Http\Controllers\PostsController::class, 'show'])->name('post');
Route::post('/post/likedislike',[App\Http\Controllers\PostsController::class, 'saveLikeDislike'])->name('likeDisLike');
Route::get('/blog', [App\Http\Controllers\Controller::class, 'indexBlog'])->name('blog');

# готово
Route::get('/get-analytics', [App\Http\Controllers\EventsController::class, 'get_analytics']);
# новые урлы
Route::get('/admin/event/{event_id}', [App\Http\Controllers\EventsController::class, 'show'])->name('event.admin_by_id');
Route::get('/event/{event_id}', [App\Http\Controllers\EventsController::class, 'show'])->name('event.by_id');
# СТАРЫЕ (будем избавляться)
Route::get('/admin/event/{start_date}/{climbing_gym}/{title}', [App\Http\Controllers\EventsController::class, 'show'])->name('event.admin_by_url');
Route::get('/event/{start_date}/{climbing_gym}/{title}', [App\Http\Controllers\EventsController::class, 'show'])->name('event.by_url');
Route::get('/event/{event_id}/analytics', [App\Http\Controllers\EventsController::class, 'index_analytics'])->name('index_analytics');
Route::post('/send-all-result', [App\Http\Controllers\EventsController::class, 'sendAllResult'])->middleware('throttle:5,1');
Route::get('/event/{event_id}/participants', [App\Http\Controllers\EventsController::class, 'get_participants'])->name('participants');
Route::get('/event/{event_id}/qualification-classic-results', [App\Http\Controllers\EventsController::class, 'get_qualification_classic_results'])->name('get_qualification_classic_results');
Route::get('/event/{event_id}/qualification-global-classic-results', [App\Http\Controllers\EventsController::class, 'get_qualification_classic_global_results'])->name('get_qualification_classic_global_results');
Route::get('/event/{event_id}/qualification-france-system-results', [App\Http\Controllers\EventsController::class, 'get_qualification_france_system_results'])->name('get_qualification_france_system_results');
Route::get('/event/{event_id}/semifinal-france-system-results', [App\Http\Controllers\EventsController::class, 'get_semifinal_france_system_results'])->name('get_semifinal_france_system_results');
Route::get('/event/{event_id}/final-france-system-results', [App\Http\Controllers\EventsController::class, 'get_final_france_system_results'])->name('get_final_france_system_results');


Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'index'])->name('profile');

    Route::get('/get-profile-overview', [App\Http\Controllers\ProfileController::class, 'getTabContentOverview'])->name('getProfileOverview');
    Route::get('/get-profile-card', [App\Http\Controllers\ProfileController::class, 'getTabContentProfileCard'])->name('getProfileCard');
    Route::get('/get-profile-analytics', [App\Http\Controllers\ProfileController::class, 'getTabContentProfileAnalytics'])->name('getProfileAnalytics');
    Route::get('/get-profile-setting', [App\Http\Controllers\ProfileController::class, 'getTabContentSetting'])->name('getTabContentSetting');
    Route::get('/get-profile-edit', [App\Http\Controllers\ProfileController::class, 'getTabContentEdit'])->name('getTabContentEdit');
    Route::post('/edit-changes', [App\Http\Controllers\ProfileController::class, 'editChanges'])->name('editChanges');
    Route::post('/change-password', [App\Http\Controllers\ProfileController::class,'changePassword'])->name('changePassword');
    Route::get('/get-profile-events', [App\Http\Controllers\ProfileController::class, 'getTabContentEvents'])->name('getTabContentEvents');
    Route::post('/take-part', [App\Http\Controllers\EventsController::class, 'store'])->name('takePart');
    Route::get('/group-register/event/{event_id}', [App\Http\Controllers\Auth\RegisteredUserController::class, 'index_group_registration'])->name('index_group_registration');
    Route::post('/group-register/event/{event_id}', [App\Http\Controllers\Auth\RegisteredUserController::class, 'group_registration'])->name('group_registration');
    Route::get('/get-available-sets', [App\Http\Controllers\EventsController::class, 'getAvailableSets']);
    Route::post('/add-to-list-pending', [App\Http\Controllers\EventsController::class, 'addToListPending'])->name('addToListPending');
    Route::post('/remove-from-list-pending', [App\Http\Controllers\EventsController::class, 'removeFromListPending'])->name('removeFromListPending');
    Route::post('/cancel-take-part-participant', [App\Http\Controllers\EventsController::class, 'cancelTakePartParticipant'])->name('cancelTakePartParticipant');
    Route::post('/change-set', [App\Http\Controllers\EventsController::class, 'changeSet'])->name('changeSet');
    Route::post('/send-products-and-discount', [App\Http\Controllers\EventsController::class, 'sendProductsAndDiscount'])->name('sendProductsAndDiscount');
    Route::post('/send-result-participant', [App\Http\Controllers\EventsController::class, 'sendResultParticipant'])->name('sendResultParticipant');
    Route::post('/crop-image-upload', [App\Http\Controllers\CropImageController::class,'uploadCropImage'])->name('cropimageupload');
    Route::post('/crop-document-upload', [App\Http\Controllers\CropImageController::class,'uploadCropImageDocument'])->name('cropdocumentupload');
    Route::get('/event/{event_id}/routes', [App\Http\Controllers\EventsController::class, 'listRoutesEvent'])->name('listRoutesEvent');
});

require __DIR__.'/auth.php';
