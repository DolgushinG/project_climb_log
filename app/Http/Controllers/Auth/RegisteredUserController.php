<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
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

    public function group_registration(Request $request)
    {
        dd($request);
        $new_users = $request->new_users;
        $event_id = $request->event_id;
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
        foreach ($new_users as $user){
            $request->validate([
                'firstname' => ['required', 'string', 'max:255'],
                'lastname' => ['required', 'string', 'max:255'],
                'gender' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email:rfc,dns', 'max:255', 'unique:users'],
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
            ]);
            User::create([
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
        }

        return response()->json(['success' => true, 'message' => 'Группа успешно создана и зарегистрирована на соревнование'], 201);
    }
}
