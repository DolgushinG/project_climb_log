<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\ListOfPendingParticipant;
use App\Models\ParticipantCategory;
use App\Models\ResultFranceSystemQualification;
use App\Models\ResultQualificationClassic;
use App\Models\Set;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use DateTime;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $request->validate([
            'firstname' => ['required', 'string', 'max:255'],
            'lastname' => ['required', 'string', 'max:255'],
            'gender' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email:rfc,dns', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);
        $user = User::create([
            'firstname' => $request->firstname,
            'middlename' => $request->lastname.' '.$request->firstname,
            'lastname' => $request->lastname,
            'gender' => $request->gender,
            'city' => $request->city,
            'birthday' => $request->birthday,
            'team' => $request->team,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(RouteServiceProvider::HOME);
    }
    public function index_group_registration(Request $request)
    {
        $event = Event::find($request->event_id);
        $sets = Set::where('event_id', '=', $event->id)->orderBy('number_set')->get();
        foreach ($sets as $set){
            if($event->is_france_system_qualification){
                $participants_event = ResultFranceSystemQualification::where('event_id','=',$event->id)->where('owner_id','=',$event->owner_id)->where('number_set_id', '=', $set->id)->count();
//                    $participant = ResultFranceSystemQualification::where('event_id','=',$event->id)->where('user_id','=',$user_id)->first();
            } else {
                $participants_event = ResultQualificationClassic::where('event_id','=',$event->id)->where('owner_id','=',$event->owner_id)->where('number_set_id', '=', $set->id)->count();
//                    $participant = ResultQualificationClassic::where('event_id','=',$event->id)->where('user_id','=',$user_id)->first();
            }
            $set->free = $set->max_participants - $participants_event;
            if($set->free <= 0){
                $is_show_button_list_pending = true;
            }
            $a = $set->max_participants;
            $b = $set->free;

            if ($a === $b) {
                $percent = 0;
            } elseif ($a < $b) {
                $diff = $b - $a;
                if($b != 0){
                    $percent = $diff / $b * 100;
                } else {
                    $percent = 0;
                }
            } else {
                $diff = $a - $b;
                if( $a != 0){
                    $percent = $diff / $a * 100;
                } else {
                    $percent = 0;
                }
            }
            $set->procent = intval($percent);
            $set->date = Helpers::getDatesByDayOfWeek($event->start_date, $event->end_date);
        }
        $sport_categories = User::sport_categories;
        return view('auth.group-register', compact(['event', 'sets', 'sport_categories']));
    }
    public function group_registration(Request $request)
    {

        $new_users = $request->participants;
        $event_id = $request->event_id;
        $event = Event::find($event_id);
        if(!$event || !$event->is_registration_state){
            return response()->json(['success' => false, 'message' => 'Ошибка регистрации'], 422);
        }
        $person = User::find(Auth::user()->getAuthIdentifier());
        if(!Helpers::valid_email($person->email)){
            return response()->json(['success' => false, 'message' => 'Ошибка регистрации, укажите существующий email в профиле'], 422);
        }
        $messages = [
            'participants.*.firstname.string' => 'Поле Имя нужно вводить только текст',
            'participants.*.lastname.string' => 'Поле Фамилия нужно вводить только текст',
        ];

        $validator = Validator::make($request->all(), [
            'participants.*.firstname' => 'required|string|max:255',
            'participants.*.lastname' => 'required|string|max:255',
            'participants.*.dob' => 'nullable|date',
            'participants.*.gender' => 'in:male,female',
            'participants.*.team' => 'nullable|string|max:255',
            'participants.*.sets' => 'integer',
        ], $messages);
        if ($validator->fails())
        {
            return response()->json(['error' => true,'message'=> $validator->errors()->all()],422);
        }
        $created_users = [];
        foreach ($new_users as $index => $user){
            $new_user = User::create([
                'firstname' => $user['firstname'],
                'middlename' => $user['firstname'].' '.$user['lastname'],
                'lastname' => $user['lastname'],
                'gender' => $user['gender'],
                'category_id' => $user['category_id'] ?? null,
                'sport_category' => $user['sport_category'] ?? null,
                'birthday' => $user['dob'] ?? null,
                'team' => $user['team'] ?? null,
                'contact' => $person->contact,
                'email' => $user['email'] ?? (new \App\Models\Event)->translate_to_eng($user['firstname']).'-group-'.$person->email,
                'password' => Auth::user()->getAuthPassword() ?? Hash::make(Auth::user()->lastname),
            ]);
            $user_id = $new_user->id;
            $created_users[] = $new_user;
            $number_sets = [];
            $participant_categories = ParticipantCategory::where('event_id', '=', $event_id)->where('category', '=', $user['category_id'])->first();
            if($event->is_france_system_qualification){
                $participant = ResultFranceSystemQualification::where('user_id',  $event_id)->where('event_id', $event_id)->first();
                if($participant){
                    return response()->json(['success' => false, 'message' => 'ошибка регистрации'], 422);
                }
                $participant = new ResultFranceSystemQualification;
            } else {
                $participant = ResultQualificationClassic::where('user_id',  $user_id)->where('event_id', $event_id)->first();
                if($participant){
                    return response()->json(['success' => false, 'message' => 'Ошибка регистрации'], 422);
                }
                $participant = new ResultQualificationClassic;
            }
            if($event->is_input_set != 1){
                $number_set_id = $user['sets'];
                $set = Set::where('number_set', $number_set_id)->where('event_id', $event_id)->first();
                $participant->number_set_id = $set->id;
                $number_sets[$new_user->id] = $set->number_set;
            }
            if($event->is_auto_categories){
                $participant->category_id = 0;
            } else {
                $participant->category_id = $participant_categories->id;
            }

            $participant->event_id = $event_id;
            if($user['gender']){
                $participant->gender = $user['gender'];
            } else {
                $participant->gender = $user->gender;
            }
            $participant->user_id = $user_id;
            $participant->owner_id = $event->owner_id;
            if(!$event->type_event && !$event->is_france_system_qualification){
                $participant->result_for_edit = ResultQualificationClassic::generate_empty_json_result($event_id);
            }
            $participant->active = 0;
            $participant->save();
            if($user){
                if($user['gender']){
                    $new_user->gender = $user['gender'];
                }
                if($user['sport_category'] ?? null){
                    $new_user->sport_category = $user['sport_category'];
                }
                if($user['dob'] ?? null){
                    $new_user->birthday = $user['dob'];
                }
                $new_user->save();
            }
        }
        foreach ($created_users as $index => $user){
            $created_users[$index]['number_set'] = $number_sets[$user->id] ?? '-';
        }
        ResultQualificationClassic::send_main_about_group_take_part($event, $person, $created_users);
        return response()->json(['success' => true, 'message' => 'Группа успешно создана и зарегистрирована на соревнование в письме все подробности']);
    }
}
