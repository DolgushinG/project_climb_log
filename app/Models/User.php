<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

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
        'year',
        'sport_category',
        'skill',
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
        $participant = Participant::where('user_id', '=', Auth()->user()->id)
            ->where('event_id', '=', $event_id)->first();
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
        return $this->hasOne(Participant::class);
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

}
