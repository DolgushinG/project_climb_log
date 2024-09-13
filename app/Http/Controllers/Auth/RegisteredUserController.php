<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\ListOfPendingParticipant;
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
            'middlename' => $request->firstname.' '.$request->lastname,
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
        $email_person = $request->email_person;
        $messages = [
            'participants.*.firstname.string' => 'Поле Имя нужно вводить только текст',
            'participants.*.lastname.string' => 'Поле Фамилия нужно вводить только текст',
            'participants.*.email.string' => 'Поле email не корректно',
            'participants.*.email.email' => 'Поле email не корректно',
            'participants.*.email.max' => 'Поле email должно содержать не более 255 символов',
            'participants.*.email.unique' => 'Этот email уже зарегистрирован, возможно вы уже имеете аккаунт с этой почтой',
        ];

        $validator = Validator::make($request->all(), [
            'participants.*.firstname' => 'required|string|max:255',
            'participants.*.lastname' => 'required|string|max:255',
            'participants.*.dob' => 'required|date',
            'participants.*.gender' => 'required|in:male,female',
            'participants.*.team' => 'nullable|string|max:255',
            'participants.*.sets' => 'required|integer',
            'participants.*.email' => [
                'required',
                'email',
                'string',
                'max:255',
                Rule::unique('users', 'email'), // Проверяем уникальность email среди пользователей
            ],
        ], $messages);
        if ($validator->fails())
        {
            return response()->json(['error' => true,'message'=> $validator->errors()->all()],422);
        }
        foreach ($new_users as $index => $user){
            User::create([
                'firstname' => $user['firstname'],
                'middlename' => $user['firstname'].' '.$user['lastname'],
                'lastname' => $user['lastname'],
                'gender' => $user['gender'],
                'birthday' => $user['dob'],
                'team' => $user['team'],
                'contact' => $email_person,
                'email' => $user['email'] ?? $index.$email_person,
                'password' => Auth::user()->getAuthPassword(),
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Группа успешно создана и зарегистрирована на соревнование'], 201);
    }
}
