<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResultFinalStage extends Model
{
    protected $table = 'result_final_stage';

    public function event()
    {
        return $this->belongsTo(Event::class)->where('active', 1);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function get_final_participant($event, $one_group=null, $get_array=false)
    {
        $amount_the_best_participant_to_go_final = $event->amount_the_best_participant_to_go_final ?? 10;
        if($event->is_additional_final){
            if($one_group){
                $merged_users = ResultParticipant::get_participant_qualification_only_one_group($event, $amount_the_best_participant_to_go_final, $one_group);
            } else {
                $merged_users = ResultParticipant::get_participant_qualification_group($event, $amount_the_best_participant_to_go_final);
            }
        } else {
            if($event->is_semifinal){
                $merged_users = ResultSemiFinalStage::get_participant_semifinal($event, $amount_the_best_participant_to_go_final);
            } else {
                $merged_users = ResultParticipant::get_participant_qualification_gender($event, $amount_the_best_participant_to_go_final);
            }
        }
        if($get_array){
            return $merged_users->toArray();
        } else {
            return $merged_users;
        }

    }

    public static function get_participant_semifinal($event, $amount)
    {
        $users_female = ResultSemiFinalStage::better_of_participants_semifinal_stage($event->id, 'female', $amount);
        $users_male = ResultSemiFinalStage::better_of_participants_semifinal_stage($event->id, 'male', $amount);

        return $users_male->merge($users_female);
    }


}
