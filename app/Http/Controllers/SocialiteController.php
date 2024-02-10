<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    public function callback_vkontakte(){
        $socialite_user =  Socialite::driver('vkontakte')->user();
        $user = $this->saving_callback($socialite_user, 'vkontakte');
        \Illuminate\Support\Facades\Auth::login($user);
        return redirect('/profile');
    }

    public function callback_telegram(){
        $socialite_user =  Socialite::driver('telegram')->user();
        $user = $this->saving_callback($socialite_user, 'telegram');
        \Illuminate\Support\Facades\Auth::login($user);
        return redirect('/profile');
    }

    public function callback_yandex(){
        $socialite_user =  Socialite::driver('yandex')->user();
        $user = $this->saving_callback($socialite_user, 'yandex');
        \Illuminate\Support\Facades\Auth::login($user);
        return redirect('/profile');
    }
    public function saving_callback($socialite_user, $socialite){
        $user = \App\Models\User::updateOrCreate([
            $socialite.'_id' => $socialite_user->getId()
        ], [
            $socialite.'_id' => $socialite_user->getId(),
            'nickname'.$socialite => $socialite_user->getNickname(),
            'middlename' => $socialite_user->getName(),
            'firstname' => $socialite_user->user['first_name'],
            'lastname' =>$socialite_user->user['last_name'],
            'avatar' => $socialite_user->getAvatar(),
            'email' => $socialite_user->getId().'@'.$socialite.'.com',
            $socialite.'_token' => $socialite_user->token,
            $socialite.'_refresh_token' => $socialite_user->refreshToken,
        ]);
        return $user;
    }
}
