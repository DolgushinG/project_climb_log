<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class ResultParticipant extends Model
{
    const FLASH = 1;

    const STATUS_PASSED_FLASH = "1";
    const STATUS_PASSED_REDPOINT = "2";
    const STATUS_NOT_PASSED = "0";

    const REDPOINT = 0.9;
    const FAIL = 0;
    const FLASH_VALUE = 10;
    const REDPOINT_VALUE = 0;

    const POINT_VALUES = array(self::STATUS_NOT_PASSED => self::FAIL, self::STATUS_PASSED_FLASH => self::FLASH_VALUE, self::STATUS_PASSED_REDPOINT => self::REDPOINT_VALUE);

    var $values = array(self::STATUS_NOT_PASSED => self::FAIL, self::STATUS_PASSED_FLASH => self::FLASH, self::STATUS_PASSED_REDPOINT => self::REDPOINT);


    protected $table = 'result_participant';

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
        return sqrt($count_route_passed / $active_participant );
    }
    public function get_value_route($attempt, $value_category, $format, $custom_value=null) {
        switch ($format) {
            # 10 лучших
            case 1:
                return $value_category + self::POINT_VALUES[$attempt];
            # Все трассы
            case 2:
                return $this->values[$attempt];
        }
    }

    public static function participant_with_result($user_id, $event_id) {
        return 0 < count(ResultParticipant::where('event_id', '=', $event_id)
            ->where('user_id', '=', $user_id)
            ->get()
            ->toArray());

    }

    public static function get_participant_qualification_group($event, $amount)
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
