<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    public function callback_vkontakte(){
        try {
            $socialite_user =  Socialite::driver('vkontakte')->stateless()->user();
        } catch (\Exception $e) {
            Log::error('Socialize - vkontakte - error - '.$e->getMessage());
            return redirect('/login')->with('error','Что то пошло не так обратитесь к администратору');
        }
        $existingUser = User::where('vkontakte_id', $socialite_user->getId())->first();
        if($existingUser){
            \Illuminate\Support\Facades\Auth::login($existingUser);
        } else {
            $user = $this->saving_callback($socialite_user, 'vkontakte');
            \Illuminate\Support\Facades\Auth::login($user);
        }
        return redirect('/profile');
    }

    public function callback_telegram(){
        try {
            $socialite_user =  Socialite::driver('telegram')->stateless()->user();
        } catch (\Exception $e) {
            Log::error('Socialize -  telegram - error - '.$e->getMessage());
            return redirect('/login')->with('error','Что то пошло не так обратитесь к администратору');
        }
        $existingUser = User::where('telegram_id', $socialite_user->getId())->first();
        if($existingUser){
            \Illuminate\Support\Facades\Auth::login($existingUser);
        } else {
            $user = $this->saving_callback($socialite_user, 'telegram');
            \Illuminate\Support\Facades\Auth::login($user);
        }
        return redirect('/profile');
    }

    public function callback_yandex(){
        try {
            $socialite_user =  Socialite::driver('yandex')->stateless()->user();
        } catch (\Exception $e) {
            Log::error('Socialize - yandex - error - '.$e->getMessage());
            return redirect('/login')->with('error','Что то пошло не так обратитесь к администратору');
        }
        $existingUser = User::where('yandex_id', $socialite_user->getId())->first();
        if($existingUser){
            \Illuminate\Support\Facades\Auth::login($existingUser);
        } else {
            $user = $this->saving_callback($socialite_user, 'yandex');
            \Illuminate\Support\Facades\Auth::login($user);
        }
        return redirect('/profile');
    }
    public function saving_callback($socialite_user, $socialite){

        $email = $socialite_user->user['email'] ?? null;
        if(!$email){
            $email = $socialite_user->getId().'@'.$socialite.'.com';
        }
        $user = \App\Models\User::updateOrCreate([
            $socialite.'_id' => $socialite_user->getId()
        ], [
            $socialite.'_id' => $socialite_user->getId(),
            'nickname'.$socialite => $socialite_user->getNickname(),
            'middlename' => $socialite_user->getName(),
            'firstname' => $socialite_user->user['first_name'] ?? null,
            'lastname' =>$socialite_user->user['last_name'] ?? null,
            'avatar' => $socialite_user->getAvatar(),
            'email' => $email,
            $socialite.'_token' => $socialite_user->token,
            $socialite.'_refresh_token' => $socialite_user->refreshToken,
        ]);
        if($socialite == 'yandex'){
            $user->gender = $socialite_user->user['sex'] ?? null;
            $user->email = $socialite_user->user['default_email'] ?? null;
            $user->year = $socialite_user->user['birthday'] ?? null;
            $user->save();
        }
        return $user;
    }
}
