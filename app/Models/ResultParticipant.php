<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;


class ResultParticipant extends Model
{
    public $timestamps = true;

//    const FLASH = 1;

    const STATUS_PASSED_FLASH = "1";
    const STATUS_PASSED_REDPOINT = "2";
    const STATUS_NOT_PASSED = "0";

//    const REDPOINT = 0.9;
//    const FAIL = 0;
//    const FLASH_VALUE = 10;
//    const REDPOINT_VALUE = 0;

//    const POINT_VALUES = array(self::STATUS_NOT_PASSED => self::FAIL, self::STATUS_PASSED_FLASH => self::FLASH_VALUE, self::STATUS_PASSED_REDPOINT => self::REDPOINT_VALUE);

//    var $values = array(self::STATUS_NOT_PASSED => self::FAIL, self::STATUS_PASSED_FLASH => self::FLASH, self::STATUS_PASSED_REDPOINT => self::REDPOINT);


    protected $table = 'result_participant';

    public static function get_flash_value_for_mode_ten_better_route($attempt, $event_id, $route)
    {
        $event_route =  Route::where('event_id', $event_id)->where('grade', $route->grade)->first();
        switch ($attempt){
            case self::STATUS_NOT_PASSED:
                return 0;
            case self::STATUS_PASSED_FLASH:
                return $route->value + $event_route->flash_value;
            case self::STATUS_PASSED_REDPOINT:
                return $route->value;
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
    private static function counting_result($event_id, $route_id, $gender)
    {
        $users = User::query()
            ->leftJoin('participants', 'users.id', '=', 'participants.user_id')
            ->where('participants.event_id', '=', $event_id)
            ->where('participants.active', '=', 1)
            ->select(
                'users.id',
            )
            ->pluck('id');
        return count(ResultParticipant::whereIn('user_id', $users)
            ->where('gender', '=', $gender)
            ->where('route_id', '=', $route_id)
            ->whereNotIn('attempt',[0])
            ->get()
            ->toArray());
    }

    public static function get_coefficient($event_id, $route_id, $gender){
        $active_participant = Participant::participant_with_result($event_id, $gender);
        $count_route_passed = self::counting_result($event_id, $route_id, $gender);
        if ($count_route_passed == 0) {
            $count_route_passed = 1;
        }
        return sqrt($active_participant / $count_route_passed);
    }
    public function get_value_route($attempt, $route, $format, $event_id) {
        switch ($format) {
            # 10 лучших
            case 1:
                return self::get_flash_value_for_mode_ten_better_route($attempt, $event_id, $route);
//                return $route->value + self::POINT_VALUES[$attempt];
            # Все трассы
            case 2:
                self::get_flash_value_for_mode_all_route($attempt, $event_id);
//                return $this->values[$attempt];
        }
    }

    public static function participant_with_result($user_id, $event_id) {
        $event = Event::find($event_id);
        if($event->is_qualification_counting_like_final){
            $participant = ResultRouteQualificationLikeFinal::where('event_id', '=', $event_id)
                ->where('user_id', '=', $user_id)
                ->first();

        } else {
            $participant = ResultParticipant::where('event_id', '=', $event_id)
                ->where('user_id', '=', $user_id)
                ->first();
        }

        return boolval($participant);
    }

    public static function is_pay_participant($user_id, $event_id)
    {

        $event = Event::find($event_id);
        if($event->is_qualification_counting_like_final){
            $participant = ResultQualificationLikeFinal::where('event_id', '=', $event_id)
                ->where('user_id', '=', $user_id)
                ->first();
        } else {
            $participant = Participant::where('event_id', '=', $event_id)
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
        if($event->is_qualification_counting_like_final){
            $participant = ResultQualificationLikeFinal::where('event_id', '=', $event_id)
                ->where('user_id', '=', $user_id)
                ->first();
        } else {
            $participant = Participant::where('event_id', '=', $event_id)
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

    public static function get_participant_qualification_group($event, $amount, $gender=null)
    {
        $all_group_participants = array();
        foreach ($event->categories as $category){
            $category_id = ParticipantCategory::where('category', $category)->where('event_id', $event->id)->first()->id;
            if($event->is_qualification_counting_like_final) {
                $all_group_participants[] = ResultQualificationLikeFinal::better_of_participants_qualification_like_final_stage($event->id, 'female', $amount, $category_id)->toArray();
                $all_group_participants[] = ResultQualificationLikeFinal::better_of_participants_qualification_like_final_stage($event->id, 'male', $amount, $category_id)->toArray();
            } else {
                $all_group_participants[] = Participant::better_participants($event->id, 'male', $amount, $category_id);
                $all_group_participants[] = Participant::better_participants($event->id, 'female', $amount, $category_id);
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

    public static function get_participant_qualification_only_one_group($event, $amount, $group)
    {
        $all_group_participants = array();
        if($event->is_qualification_counting_like_final) {
            $all_group_participants[] = ResultQualificationLikeFinal::better_of_participants_qualification_like_final_stage($event->id, 'female', $amount, $group->id)->toArray();
            $all_group_participants[] = ResultQualificationLikeFinal::better_of_participants_qualification_like_final_stage($event->id, 'male', $amount, $group->id)->toArray();
        } else {
            $all_group_participants[] = Participant::better_participants($event->id, 'male', $amount, $group->id);
            $all_group_participants[] = Participant::better_participants($event->id, 'female', $amount, $group->id);
        }
        $merged_users = collect();
        foreach ($all_group_participants as $participant) {
            foreach ($participant as $a){
                $merged_users[] = $a;
            }
        }
        return $merged_users;
    }
    public static function get_participant_qualification_gender($event, $amount)
    {
        if($event->is_qualification_counting_like_final) {
            $users_female = ResultQualificationLikeFinal::better_of_participants_qualification_like_final_stage($event->id, 'female', $amount);
            $users_male = ResultQualificationLikeFinal::better_of_participants_qualification_like_final_stage($event->id, 'male', $amount);
        } else {
            $users_female = Participant::better_participants($event->id, 'female', $amount);
            $users_male = Participant::better_participants($event->id, 'male', $amount);
        }
        return $users_male->merge($users_female);
    }
}
