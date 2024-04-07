<?php

namespace App\Http\Controllers;
use App\Models\Event;
use App\Models\Participant;
use App\Models\ParticipantCategory;
use App\Models\ResultFinalStage;
use App\Models\ResultParticipant;
use App\Models\ResultQualificationLikeFinal;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\Activitylog\Models\Activity;
use function Symfony\Component\String\s;

class ProfileController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['auth','verified']);
    }
    public function index() {
        $user = User::find(Auth()->user()->id);
        $state_user = array();
        $result_flash = ResultParticipant::where('user_id', $user->id)->where('attempt', 1)->get()->count();
        $result_redpoint = ResultParticipant::where('user_id', $user->id)->where('attempt', 2)->get()->count();
        $result_all_route_passed = ResultParticipant::where('user_id', $user->id)->whereIn('attempt', [1,2])->get()->count();
        if($result_all_route_passed > 0){
            $state_user['flash'] =  round(($result_flash / $result_all_route_passed) * 100, 2);
            $state_user['redpoint'] =  round(($result_redpoint / $result_all_route_passed) * 100, 2);
            $state_user['all'] =  $result_all_route_passed;
        } else {
            $state_user['flash'] = 0;
            $state_user['redpoint'] = 0;
            $state_user['all'] = 0;
        }

        $activities = Activity::where('causer_id', '=', $user->id)->orderBy('updated_at')->take(5)->get();
        return view('profile.main', compact(['user', 'activities', 'state_user']));
    }
    public function getTabContentOverview() {
        $user = User::find(Auth()->user()->id);
        if(!$user->password){
            $user['is_alert_needs_show_email_and_password'] = true;
        } else {
            $user['is_alert_needs_show_email_and_password'] = false;
        }
        $activities = Activity::where('causer_id', '=', $user->id)->orderBy('updated_at')->take(5)->get();
        $state_participant = array();
        $all_like_final = ResultQualificationLikeFinal::where('user_id', $user->id)->where('active', 1)->get()->count();
        $all_classic = Participant::where('user_id', $user->id)->where('active', 1)->get()->count();
        $state_participant['amount_event'] = $all_classic + $all_like_final;
        return view('profile.overview', compact(['user', 'activities', 'state_participant']));
    }
    public function getTabContentSetting() {
        $user = User::find(Auth()->user()->id);
        $services = array();
        $user['is_alert_for_needs_set_password'] = true;
        if($user->telegram_id){
            $services[] = array('icon_auth' => ' <i class="fa fa-telegram" aria-hidden="true"></i> ', 'title_auth' => 'Telegram');
            $user['is_alert_for_needs_set_password'] = true;
        }
        if($user->yandex_id){
            $services[] = array('icon_auth' => ' <i class="fa fa-yandex" aria-hidden="true"></i> ', 'title_auth' => 'Yandex');
            $user['is_alert_for_needs_set_password'] = true;
        }
        if($user->vkontakte_id){
            $services[] = array('icon_auth' => ' <i class="fa fa-vk" aria-hidden="true"></i> ', 'title_auth' => 'VK');
            $user['is_alert_for_needs_set_password'] = true;
        }
        if($user->password && $user->email) {
            $services[] = array('icon_auth' => ' <i class="fa fa-key" aria-hidden="true"></i> ', 'title_auth' => 'По email и паролю');
            $user['is_alert_for_needs_set_password'] = false;
        }
        $user['types_auth'] = $services;
        $user['is_show_old_password'] = boolval($user->password);
        return view('profile.setting', compact('user'));
    }
    public function getTabContentEdit() {
        $user = User::find(Auth()->user()->id);
        $sport_categories = User::sport_categories;
        $genders = ['male','female'];
        return view('profile.edit-profile', compact(['sport_categories', 'user', 'genders']));
    }

    public function getTabContentEvents() {
        $user_id = Auth()->user()->id;
        $participant = Participant::where('user_id', '=', $user_id)->pluck('event_id');
        $res_qualification_like_final = ResultQualificationLikeFinal::where('user_id', '=', $user_id)->pluck('event_id');
        $events_ids = $res_qualification_like_final->merge($participant);
        $events = Event::whereIn('id', $events_ids->toArray())->get();
        foreach ($events as $event){
            if($event->is_qualification_counting_like_final){
                $event['amount_participant'] = ResultQualificationLikeFinal::where('event_id', '=', $event->id)->get()->count();
            } else {
                $event['amount_participant'] = Participant::where('event_id', '=', $event->id)->get()->count();
            }
            $participant = ResultFinalStage::where('event_id', '=', $event->id)->where('user_id', '=', $user_id)->first();
            $event['user_place'] = $participant->user_place ?? 'Нет результата';
        }
        return view('profile.events', compact(['events']));
    }
    public function editChanges(Request $request) {
        $messages = array(
            'firstname.string' => 'Поле Имя нужно вводить только текст',
            'lastname.string' => 'Поле Фамилия нужно вводить только текст',
        );
        $validator = Validator::make($request->all(), [
            'firstname' => 'string',
            'lastname' => 'string',
        ],$messages);
        if ($validator->fails())
        {
            return response()->json(['error' => true,'message'=>$validator->errors()->all()],422);
        }
        $user = User::find(Auth()->user()->id);
        $user->firstname = $request->firstname;
        $user->email = $request->email;
        $user->lastname = $request->lastname;
        $user->middlename = $request->firstname.' '.$request->lastname;
        $user->city = $request->city;
        $user->gender = $request->gender;
        $user->sport_category = $request->sport_category;
        $user->team = $request->team;
        $user->birthday = $request->birthday;
        if ($user->save()) {
            return response()->json(['success' => true, 'message' => 'Успешно сохранено'], 200);
        } else {
            return response()->json(['success' => false, 'message' => 'Ошибка сохранения'], 422);
        }
    }

    public function changePassword(Request $request)
    {
        if(!Auth::user()->password){
            $user = User::find(Auth::id());
            $user->password = Hash::make($request->get('new_password'));
            $user->save();
            return response()->json(['success' => true, 'message' => 'Ваш пароль успешно изменен'], 200);
        }
        $messages = array(
            'old_password.required' => 'Поле пароль обязательно для заполнения',
            'new_password.required' => 'Поле новый пароль обязательно для заполнения',
            'new_password.confirmed' => 'Поле новый пароль и подтверждение пароля должны совпадать',
            'new_password.min:8' => 'Минимальная длина пароля 8 сивмолов',
        );
        $validator = Validator::make($request->all(), [
            'old_password' => 'required',
            'new_password' => 'required|min:8',
        ],$messages);
        if ($validator->fails())
        {
            return response()->json(['error' => true,'message'=>$validator->errors()->all()],422);
        }
        $currentPass = Auth::user()->password;
        if (Hash::check($request->get('old_password'), $currentPass)) {
            $user = User::find(Auth::id());
            $user->password = Hash::make($request->get('new_password'));
            $user->save();
            return response()->json(['success' => true, 'message' => 'Ваш пароль успешно изменен'], 200);
        } else {
            return response()->json(['success' => false, 'message' => 'Введенный старый пароль неверный'], 422);
        }
    }

}
