<?php

namespace App\Models;

use App\Notifications\CustomResetPasswordNotification;
use Encore\Admin\Facades\Admin;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use stdClass;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, LogsActivity;

    const sport_categories = array(
        "б/р",
        "III разряд",
        "II разряд",
        "I разряд",
        "КМС",
        "МС",
        "МСМК",
        "ЗМС"
    );

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->setDescriptionForEvent(fn(string $eventName) => "Успешный вход")
            ->useLogName('login')
            ->logOnly(['middlename']);
    }
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'firstname',
        'middlename',
        'lastname',
        'gender',
        'birthday',
        'year',
        'sport_category',
        'skill',
        'phone',
        'city',
        'team',
        'email',
        'password',
        'avatar',
        'telegram_id',
        'yandex_id',
        'vkontakte_id',
    ];
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public static function user_participant($event_id){
        $event = Event::find($event_id);
        $user_id = Auth()->user()->id;
        if($event->is_france_system_qualification){
            $participant = ResultFranceSystemQualification::where('user_id',  $user_id)->where('event_id', $event_id)->first();
        } else {
            $participant = ResultQualificationClassic::where('user_id', '=', $user_id)
                ->where('event_id', '=', $event_id)->where('is_other_event', 0)->first();
        }
        if($participant){
            return true;
        } else {
            return false;
        }
    }

    public static function gender($id){
        return trans_choice('somewords.'.User::find($id)->gender, 10);
    }

    public function participant()
    {
        return $this->hasOne(ResultQualificationClassic::class);
    }
    public static function category($category_id)
    {   if($category_id){
            return ParticipantCategory::findOrFail($category_id)->category;
        }
    }

    public function event()
    {
        return $this->belongsToMany(Event::class);
    }

    public function result_semi_final_stage()
    {
        return $this->hasOne(ResultRouteSemiFinalStage::class);
    }

    public function result_route_final_stage()
    {
        return $this->hasOne(ResultRouteFinalStage::class);

    }

    /**
     *
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new CustomResetPasswordNotification($token));
    }

    public static function send_new_device($user, $ip, $device)
    {
        if (!str_contains($user->email, 'telegram')) {
            $details = array();
            $details['middlename'] = $user->middlename;
            $details['device'] = $device;
            $details['ip'] = $ip;
            $details['time'] = $user->updated_at;
            Mail::to($user->email)->queue(new \App\Mail\AuthNewDevice($details));
        }
    }
    public static function send_auth_socialize($user, $socialize)
    {
        if (!str_contains($user->email, 'telegram')) {
            $details = array();
            $details['middlename'] = $user->middlename;
            $details['socialize'] = $socialize;
            $details['time'] = $user->updated_at;
            Mail::to($user->email)->queue(new \App\Mail\AuthSocialize($details));
        }

    }
}
