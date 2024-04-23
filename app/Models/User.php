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
        if($event->is_qualification_counting_like_final){
            $participant = ResultQualificationLikeFinal::where('user_id',  $user_id)->where('event_id', $event_id)->first();
        } else {
            $participant = Participant::where('user_id', '=', $user_id)
                ->where('event_id', '=', $event_id)->first();
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

    public static function import_users(){
        $json = file_get_contents('female.json');
// Decode the JSON file
        $json_data = json_decode($json,true);
// Display data
        foreach ($json_data as $index => $data){
            $result_participant = array();
            $user_id = User::where('middlename', '=', $data['middlename'])->first()->id;

            for($i= 1; $i<=20;$i++){
                $grade_route = ResultParticipant::where('event_id', 1)->where('route_id', '=', $i)->first();
                if(!isset($data[$i])){
                    $route_id = $i;
                    $attempt = 0;
                    $result_participant[] = array('grade' => "6B", 'points' => 0 ,'user_id'=> $user_id, 'event_id'=> 2, 'owner_id'=> 1, 'route_id' => $route_id, 'attempt'=> $attempt);
                } else {
                    if (str_contains($data[$i], 'F')) {
                        $route_id = $i;
                        $attempt = 1;
                        $result_participant[] = array('grade' => "6B", 'points' => 0 ,'user_id'=> $user_id, 'event_id'=> 2, 'owner_id'=> 1,'route_id' => $route_id, 'attempt'=> $attempt);
                    }
                    if (str_contains($data[$i], 'A')) {
                        $route_id = $i;
                        $attempt = 2;
                        $result_participant[] = array('grade' => "6B", 'points' => 0 ,'user_id'=> $user_id, 'event_id'=> 2, 'owner_id'=> 1, 'route_id' => $route_id, 'attempt'=> $attempt);
                    }
                    if (str_contains($data[$i], 'X')) {
                        $route_id = $i;
                        $attempt = 0;
                        $result_participant[] = array('grade' => "6B", 'points' => 0 ,'user_id'=> $user_id, 'event_id'=> 2, 'owner_id'=> 1, 'route_id' => $route_id, 'attempt'=> $attempt);
                    }
                }
            }

            $final_data = array();
            $final_data_only_passed_route = array();
            foreach ($result_participant as $route){
                $gender = 'female';
                (new \App\Models\EventAndCoefficientRoute)->update_coefficitient($route['event_id'], $route['route_id'], $route['owner_id'], $gender);
                $coefficient = ResultParticipant::get_coefficient($route['event_id'], $route['route_id'], $gender);
                # Варианты форматов подсчета баллов
                $value_category = Route::where('grade','=',$route['grade'])->where('owner_id','=', 1)->first()->value;
                $value_route = (new \App\Models\ResultParticipant)->get_value_route($route['attempt'], $value_category, 2);
                $route['points'] = $coefficient * $value_route;
                # Формат все трассы считаем сразу
//                dd($route['event_id'], $route['points'], $route['user_id'], $gender);
                (new \App\Models\Event)->insert_final_participant_result($route['event_id'], $route['points'], $route['user_id'], $gender);
                $final_data[] = $route;

                if ($route['attempt'] != 0){
                    $final_data_only_passed_route[] = $route;
                }
            }
            # Формат 10 лучших считаем уже после подсчета, так как ценность трассы еще зависит от коэффициента прохождений

            foreach ($final_data as $index => $data){
                $final_data[$index] = collect($data)->except('points')->toArray();
            }

            ResultParticipant::insert($final_data);

            $participants = User::query()
                ->leftJoin('participants', 'users.id', '=', 'participants.user_id')
                ->where('participants.event_id', '=', 2)
                ->select(
                    'users.id',
                    'users.gender',
                )->get();
            foreach ($participants as $participant) {
                Event::update_participant_place(2, $participant->id, $participant->gender);
            }

        }


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
