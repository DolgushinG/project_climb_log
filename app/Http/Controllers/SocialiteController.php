<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    public function callback_vkontakte(Request $request){
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
            $user = $this->saving_callback($socialite_user, 'vkontakte', $request);
            \Illuminate\Support\Facades\Auth::login($user);
        }
        return redirect('/profile');
    }

    public function callback_telegram(Request $request){
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
            $user = $this->saving_callback($socialite_user, 'telegram', $request);
            \Illuminate\Support\Facades\Auth::login($user);
        }
        return redirect('/profile');
    }

    public function callback_yandex(Request $request){
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
            $user = $this->saving_callback($socialite_user, 'yandex', $request);
            \Illuminate\Support\Facades\Auth::login($user);
        }
        return redirect('/profile');
    }
    public function saving_callback($socialite_user, $socialite, $request=null){

        $email = $socialite_user->getEmail() ?? null;
        if(!$email){
            $email = $socialite_user->getId().'@'.$socialite.'.com';
        }
        $user = User::where('email', $email)->first();
        if($user){
            User::where('email', $email)->update([$socialite.'_id' => $socialite_user->getId()]);
        } else {
            if($socialite_user->getAvatar()){
                if(strlen($socialite_user->getAvatar()) > 254){
                    $avatar = null;
                } else {
                    $avatar = $socialite_user->getAvatar();
                }
            } else {
                $avatar = null;
            }
            if(isset($socialite_user->user['last_name']) && $socialite_user->user['first_name']){
                $middlename = $socialite_user->user['last_name'].' '. $socialite_user->user['first_name'];
            } else {
                $middlename = $socialite_user->getName();
            }
            $user = \App\Models\User::updateOrCreate([
                $socialite.'_id' => $socialite_user->getId()
            ], [
                $socialite.'_id' => $socialite_user->getId(),
                'nickname'.$socialite => $socialite_user->getNickname(),
                'middlename' => $middlename,
                'firstname' => $socialite_user->user['first_name'] ?? null,
                'lastname' => $socialite_user->user['last_name'] ?? null,
                'avatar' => $avatar,
                'email' => $email,
                $socialite.'_token' => $socialite_user->token,
                $socialite.'_refresh_token' => $socialite_user->refreshToken,
            ]);
            if($socialite == 'yandex'){
                $user->gender = $socialite_user->user['sex'] ?? null;
                $user->email = $socialite_user->user['default_email'] ?? null;
                $user->birthday = $socialite_user->user['birthday'] ?? null;
                $user->save();
            }
        }
        if($user){
            User::send_auth_socialize($user, $socialite);
        }


        return $user;
    }
}
