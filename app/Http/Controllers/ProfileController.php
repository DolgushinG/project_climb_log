<?php

namespace App\Http\Controllers;
use App\Models\Event;
use App\Models\ResultQualificationClassic;
use App\Models\ResultFinalStage;
use App\Models\ResultRouteQualificationClassic;
use App\Models\ResultFranceSystemQualification;
use App\Models\ResultSemiFinalStage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Validation\Rule;


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
        $result_flash = ResultRouteQualificationClassic::where('user_id', $user->id)->where('attempt', 1)->get()->count();
        $result_redpoint = ResultRouteQualificationClassic::where('user_id', $user->id)->where('attempt', 2)->get()->count();
        $result_all_route_passed = ResultRouteQualificationClassic::where('user_id', $user->id)->whereIn('attempt', [1,2])->get()->count();
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
    public function getTabContentProfileCard()
    {
        $user = User::find(Auth()->user()->id);
        $state_user = array();
        $result_flash = ResultRouteQualificationClassic::where('user_id', $user->id)->where('attempt', 1)->get()->count();
        $result_redpoint = ResultRouteQualificationClassic::where('user_id', $user->id)->where('attempt', 2)->get()->count();
        $result_all_route_passed = ResultRouteQualificationClassic::where('user_id', $user->id)->whereIn('attempt', [1,2])->get()->count();
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
        return view('profile.card', compact(['user', 'activities', 'state_user']));
    }

    public static function getTabContentProfileAnalytics()
    {
        $user = User::find(476);
        $user_id = $user->id;
        $qualification_classic_events = ResultQualificationClassic::where('user_id', $user_id)->where('is_other_event', 0)->where('active',1)->get('event_id');
        dd($user_id, $qualification_classic_events);
        $bestTrackCount = 3;

        // Получаем коэффициенты стабильности для обоих форматов
        $stabilityCoefficients = ResultQualificationClassic::calculate_stability_coefficients($results, $bestTrackCount);

//        echo "Коэффициент стабильности для всех трасс (первый формат): " . $stabilityCoefficients['all_tracks'] . "\n";
//        echo "Коэффициент стабильности для лучших трасс (второй формат): " . $stabilityCoefficients['best_tracks'] . "\n";


//        return view('profile.card', compact());
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
        $all_results = ResultFranceSystemQualification::where('user_id', $user->id)->where('active', 1)->get()->count();
        $all_classic = ResultQualificationClassic::where('user_id', $user->id)->where('active', 1)->get()->count();
        $state_participant['amount_event'] = $all_classic + $all_results;
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
        $participant = ResultQualificationClassic::where('user_id', '=', $user_id)->where('is_other_event', 0)->pluck('event_id');
        $res_france_system_qualification = ResultFranceSystemQualification::where('user_id', '=', $user_id)->pluck('event_id');
        $events_ids = $res_france_system_qualification->merge($participant);
        $events = Event::whereIn('id', $events_ids->toArray())->get();
        foreach ($events as $event){
            if($event->is_france_system_qualification){
                $event['amount_participant'] = ResultFranceSystemQualification::where('event_id', '=', $event->id)->get()->count();
                $res_qualification = ResultFranceSystemQualification::where('event_id', '=', $event->id)->where('user_id', '=', $user_id)->first();
                $res_qualification_place = $res_qualification->place ?? '';
            } else {
                $event['amount_participant'] = ResultQualificationClassic::where('event_id', '=', $event->id)->where('is_other_event', 0)->get()->count();
                $res_qualification = ResultQualificationClassic::where('event_id', '=', $event->id)->where('is_other_event', 0)->where('user_id', '=', $user_id)->first();
                $res_qualification_place = $res_qualification->user_place ?? '';
            }
            $res_final = ResultFinalStage::where('event_id', '=', $event->id)->where('user_id', '=', $user_id)->first();
            $res_semifinal = ResultSemiFinalStage::where('event_id', '=', $event->id)->where('user_id', '=', $user_id)->first();
            $event['user_final_place'] = $res_final->place ?? '';
            $event['user_semifinal_place'] = $res_semifinal->place ?? '';
            $event['user_qualification_place'] = $res_qualification_place;
        }
        return view('profile.events', compact(['events']));
    }
    public function editChanges(Request $request) {
        if (!$request->firstname) {
            return response()->json(['error' => true,'message'=> ['Поле Имя не может быть пустым']],422);
        }
        if (!$request->lastname) {
            return response()->json(['error' => true,'message'=> ['Поле Фамилия не может быть пустым']],422);
        }
        $messages = array(
            'firstname.string' => 'Поле Имя нужно вводить только текст',
            'lastname.string' => 'Поле Фамилия нужно вводить только текст',
            'email.string' => 'Поле email не корректно',
            'email.email' => 'Поле email не корректно',
            'email.max:255' => 'Поле email не корректно',
            'email.unique' => 'Этот email уже зарегистрирован, возможно вы уже имеете аккаунт с этой почтой',
        );
        $validator = Validator::make($request->all(), [
                'firstname' => 'string',
                'lastname' => 'string',
                'email' => [
                    'email',
                    'string',
                    // Исключаем текущего пользователя из проверки уникальности email
                    Rule::unique('users')->ignore(Auth()->user()->id),
                ],
            ], $messages);
        if ($validator->fails())
        {
            return response()->json(['error' => true,'message'=> $validator->errors()->all()],422);
        }
        if (!filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
            return response()->json(['error' => true,'message'=> ['Поле email не корректно']],422);
        }

        $user = User::find(Auth()->user()->id);
        $user->firstname = $request->firstname;
        $user->email = $request->email;
        $user->lastname = $request->lastname;
        $user->middlename = $request->firstname.' '.$request->lastname;
        $user->city = $request->city;
        $user->gender = $request->gender;
        $user->contact = $request->contact;
        $user->sport_category = $request->sport_category;
        $user->team = $request->team;
        $user->birthday = $request->birthday;
        if ($user->save()) {
            return response()->json(['success' => true, 'message' => 'Успешно сохранено']);
        } else {
            return response()->json(['success' => false, 'message' => 'Ошибка сохранения'], 422);
        }
    }

    public function changePassword(Request $request)
    {
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
        if(!Auth::user()->password){
            $user = User::find(Auth::id());
            $user->password = Hash::make($request->get('new_password'));
            $user->save();
            return response()->json(['success' => true, 'message' => 'Ваш пароль успешно изменен']);
        }
        $currentPass = Auth::user()->password;
        if (Hash::check($request->get('old_password'), $currentPass)) {
            $user = User::find(Auth::id());
            $user->password = Hash::make($request->get('new_password'));
            $user->save();
            return response()->json(['success' => true, 'message' => 'Ваш пароль успешно изменен']);
        } else {
            return response()->json(['success' => false, 'message' => 'Введенный старый пароль неверный'], 422);
        }
    }

}
