<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;


class ResultRouteQualificationClassic extends Model
{
    public $timestamps = true;

//    const FLASH = 1;

    const STATUS_PASSED_FLASH = 1;
    const STATUS_PASSED_REDPOINT = 2;
    const STATUS_NOT_PASSED = 0;
    const STATUS_ZONE = 3;

//    const REDPOINT = 0.9;
//    const FAIL = 0;
//    const FLASH_VALUE = 10;
//    const REDPOINT_VALUE = 0;

//    const POINT_VALUES = array(self::STATUS_NOT_PASSED => self::FAIL, self::STATUS_PASSED_FLASH => self::FLASH_VALUE, self::STATUS_PASSED_REDPOINT => self::REDPOINT_VALUE);

//    var $values = array(self::STATUS_NOT_PASSED => self::FAIL, self::STATUS_PASSED_FLASH => self::FLASH, self::STATUS_PASSED_REDPOINT => self::REDPOINT);


    protected $table = 'result_route_qualification_classic';

    public function category(){
        return $this->belongsTo(ParticipantCategory::class);
    }
    public static function get_flash_value_for_mode_ten_better_route($attempt, $route, $with_flash=true)
    {
        switch ($attempt){
            case self::STATUS_NOT_PASSED:
                return 0;
            case self::STATUS_PASSED_FLASH:
                if($with_flash){
                    return $route->value + $route->flash_value;
                } else {
                    return $route->value;
                }
            case self::STATUS_PASSED_REDPOINT:
                return $route->value;
            case self::STATUS_ZONE:
                return $route->zone;
        }
    }
    public static function get_flash_value_for_mode_all_route($attempt, $event_id)
    {
        $event = Event::find($event_id);
        switch ($attempt){
            case self::STATUS_NOT_PASSED:
                return 0;
            case self::STATUS_PASSED_FLASH:
                return $event->amount_point_flash;
            case self::STATUS_PASSED_REDPOINT:
                return $event->amount_point_redpoint;
        }
    }
    public static function get_flash_value_for_mode_all_outdoor_route($attempt, $route, $with_flash=true)
    {
        switch ($attempt){
            case self::STATUS_NOT_PASSED:
                return 0;
            case self::STATUS_PASSED_FLASH:
                if($with_flash){
                    return $route->value + $route->flash_value;
                } else {
                    return $route->value;
                }
            case self::STATUS_PASSED_REDPOINT:
                return $route->value;
            case self::STATUS_ZONE:
                return $route->zone;
        }
    }
    public static function counting_result($event_id, $route_id, $gender)
    {
        $users = ResultQualificationClassic::where('event_id', '=', $event_id)->where('gender', '=', $gender)->where('active', '=', 1)->where('is_other_event', '=', 0)->pluck('user_id')->toArray();
        return ResultRouteQualificationClassic::whereIn('user_id', $users)
            ->where('event_id', '=', $event_id)
            ->where('gender', $gender)
            ->where('route_id', '=', $route_id)
            ->whereNotIn('attempt', [0])
            ->count();
    }

    public static function get_coefficient($active_participant, $count_route_passed){

        if ($count_route_passed == 0) {
            $count_route_passed = 1;
        }
        return sqrt($active_participant / $count_route_passed);
    }
    public function get_value_route($attempt, $route, $format, $event) {
        switch ($format) {
            # 10 лучших
            case Format::N_ROUTE:
                return self::get_flash_value_for_mode_ten_better_route($attempt, $route, $event->is_flash_value);
            # Все трассы
            case Format::ALL_ROUTE:
                return self::get_flash_value_for_mode_all_route($attempt, $event->id);
            # Все трассы только для феста на скалах type event = 1
            case Format::ALL_ROUTE_WITH_POINTS:
                return self::get_flash_value_for_mode_all_outdoor_route($attempt, $route, $event->is_flash_value);

        }
    }

    public static function participant_with_result($user_id, $event_id) {
        $event = Event::find($event_id);
        if($event->is_france_system_qualification){
            $participant = ResultRouteFranceSystemQualification::where('event_id', '=', $event_id)
                ->where('user_id', '=', $user_id)
                ->first();

        } else {
            $participant = ResultRouteQualificationClassic::where('event_id', '=', $event_id)
                ->where('user_id', '=', $user_id)
                ->first();
        }

        return boolval($participant);
    }

    public static function is_pay_participant($user_id, $event_id)
    {

        $event = Event::find($event_id);
        if($event->is_france_system_qualification){
            $participant = ResultFranceSystemQualification::where('event_id', '=', $event_id)
                ->where('user_id', '=', $user_id)
                ->first();
        } else {
            $participant = ResultQualificationClassic::where('event_id', '=', $event_id)
                ->where('user_id', '=', $user_id)
                ->first();
        }
        if($participant){
            return $participant->is_paid == 1;
        } else {
            Log::error('Не нашелся участник - user_id'.$user_id.'event_id'.$event_id.', причем эта кнопка должна появится только после того как он зарегистрировался');
        }
    }

    public static function is_sended_bill($user_id, $event_id)
    {
        $event = Event::find($event_id);
        if($event->is_france_system_qualification){
            $participant = ResultFranceSystemQualification::where('event_id', '=', $event_id)
                ->where('user_id', '=', $user_id)
                ->first();
        } else {
            $participant = ResultQualificationClassic::where('event_id', '=', $event_id)
                ->where('user_id', '=', $user_id)
                ->first();
        }
        if($participant){
            if($participant->bill){
                return true;
            } else {
                return false;
            }
        } else {
            Log::error('Не нашелся участник - user_id'.$user_id.'event_id'.$event_id.', причем эта кнопка должна появится только после того как он зарегистрировался');
        }
    }
    public static function is_sended_document($user_id, $event_id)
    {
        $event = Event::find($event_id);
        if($event->is_france_system_qualification){
            $participant = ResultFranceSystemQualification::where('event_id', '=', $event_id)
                ->where('user_id', '=', $user_id)
                ->first();
        } else {
            $participant = ResultQualificationClassic::where('event_id', '=', $event_id)
                ->where('user_id', '=', $user_id)
                ->first();
        }
        if($participant){
            if($participant->document){
                return true;
            } else {
                return false;
            }
        } else {
            Log::error('Не нашелся участник - user_id'.$user_id.'event_id'.$event_id.', причем эта кнопка должна появится только после того как он зарегистрировался');
        }
    }

    public static function get_participant_qualification_group($event, $amount, $gender=null)
    {
        $all_group_participants = array();
        foreach ($event->categories as $category){
            $category_id = ParticipantCategory::where('category', $category)->where('event_id', $event->id)->first()->id;
            if($event->is_france_system_qualification) {
                if($gender){
                    $all_group_participants[] = ResultFranceSystemQualification::better_of_participants_france_system_qualification($event->id, $gender, $amount, $category_id)->toArray();
                } else {
                    $all_group_participants[] = ResultFranceSystemQualification::better_of_participants_france_system_qualification($event->id, 'male', $amount, $category_id)->toArray();
                    $all_group_participants[] = ResultFranceSystemQualification::better_of_participants_france_system_qualification($event->id, 'female', $amount, $category_id)->toArray();
                }
            } else {
                if($gender){
                    $all_group_participants[] = ResultQualificationClassic::better_participants($event->id, $gender, $amount, $category_id);
                } else {
                    $all_group_participants[] = ResultQualificationClassic::better_participants($event->id, 'male', $amount, $category_id);
                    $all_group_participants[] = ResultQualificationClassic::better_participants($event->id, 'female', $amount, $category_id);
                }

            }
        }
        $merged_users = collect();
        foreach ($all_group_participants as $participant) {
            foreach ($participant as $a){
                $merged_users[] = $a;
            }
        }

        return $merged_users;
    }

    public static function get_global_participant_qualification_group($event, $amount, $gender=null)
    {
        $all_group_participants = array();
        foreach ($event->categories as $category){
            $category_id = ParticipantCategory::where('category', $category)->where('event_id', $event->id)->first()->id;
            if($gender){
                $all_group_participants[] = ResultQualificationClassic::better_global_participants($event->id, $gender, $amount, $category_id);
            } else {
                $all_group_participants[] = ResultQualificationClassic::better_global_participants($event->id, 'male', $amount, $category_id);
                $all_group_participants[] = ResultQualificationClassic::better_global_participants($event->id, 'female', $amount, $category_id);
            }

        }

        $merged_users = collect();
        foreach ($all_group_participants as $participant) {
            foreach ($participant as $a){
                $merged_users[] = $a;
            }
        }
        return $merged_users;
    }
    public static function get_global_participant_qualification_only_one_group($event, $amount, $group, $gender)
    {
        $all_group_participants = array();
        if($gender){
            $all_group_participants[] = ResultQualificationClassic::better_global_participants($event->id, $gender, $amount, $group->id);
        } else {
            $all_group_participants[] = ResultQualificationClassic::better_global_participants($event->id, 'male', $amount, $group->id);
            $all_group_participants[] = ResultQualificationClassic::better_global_participants($event->id, 'female', $amount, $group->id);
        }

        $merged_users = collect();
        foreach ($all_group_participants as $participant) {
            foreach ($participant as $a){
                $merged_users[] = $a;
            }
        }
        return $merged_users;
    }

    public static function get_participant_qualification_only_one_group($event, $amount, $group, $gender)
    {
        $all_group_participants = array();
        if($event->is_france_system_qualification) {
            if($gender){
                $all_group_participants[] = ResultFranceSystemQualification::better_of_participants_france_system_qualification($event->id, $gender, $amount, $group->id)->toArray();
            } else {
                $all_group_participants[] = ResultFranceSystemQualification::better_of_participants_france_system_qualification($event->id, 'female', $amount, $group->id)->toArray();
                $all_group_participants[] = ResultFranceSystemQualification::better_of_participants_france_system_qualification($event->id, 'male', $amount, $group->id)->toArray();
            }
        } else {
            if($gender){
                $all_group_participants[] = ResultQualificationClassic::better_participants($event->id, $gender, $amount, $group->id);
            } else {
                $all_group_participants[] = ResultQualificationClassic::better_participants($event->id, 'male', $amount, $group->id);
                $all_group_participants[] = ResultQualificationClassic::better_participants($event->id, 'female', $amount, $group->id);
            }
        }
        $merged_users = collect();
        foreach ($all_group_participants as $participant) {
            foreach ($participant as $a){
                $merged_users[] = $a;
            }
        }
        return $merged_users;
    }
    public static function get_participant_qualification_gender($event, $amount, $gender)
    {
        if($event->is_france_system_qualification) {
            if($gender){
                return ResultFranceSystemQualification::better_of_participants_france_system_qualification($event->id, $gender, $amount);
            } else {
                $users_female = ResultFranceSystemQualification::better_of_participants_france_system_qualification($event->id, 'female', $amount);
                $users_male = ResultFranceSystemQualification::better_of_participants_france_system_qualification($event->id, 'male', $amount);
            }
        } else {
            if($gender){
                return ResultQualificationClassic::better_participants($event->id, $gender, $amount);
            } else {
                $users_female = ResultQualificationClassic::better_participants($event->id, 'female', $amount);
                $users_male = ResultQualificationClassic::better_participants($event->id, 'male', $amount);
            }

        }
        return $users_male->merge($users_female);
    }
    public static function get_global_participant_qualification_gender($event, $amount, $gender)
    {
        if($gender) {
            return ResultQualificationClassic::better_global_participants($event->id, $gender, $amount);
        } else {
            $users_female = ResultQualificationClassic::better_global_participants($event->id, 'female', $amount);
            $users_male = ResultQualificationClassic::better_global_participants($event->id, 'male', $amount);
            return $users_male->merge($users_female);
        }
    }
    public function event()
    {
        return $this->belongsTo(Event::class)->where('active', '=', 1);
    }
}
