<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResultFinalStage extends Model
{
    protected $table = 'result_final_stage';
    public $timestamps = true;
    protected $casts = [
        'result_for_edit_final' =>'json',
    ];
    public function event()
    {
        return $this->belongsTo(Event::class)->where('active', 1);
    }

    public function category(){
        return $this->belongsTo(ParticipantCategory::class);
    }


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function get_final_participant($event, $one_group=null, $get_array=false)
    {
        $amount_the_best_participant_to_go_final = $event->amount_the_best_participant_to_go_final ?? 10;

        if($event->is_semifinal){
            $merged_users = self::get_participant_semifinal($event, $amount_the_best_participant_to_go_final, $one_group);
        } else {
            if($event->is_sort_group_final){
                if($one_group){
                    $merged_users = ResultRouteQualificationClassic::get_participant_qualification_only_one_group($event, $amount_the_best_participant_to_go_final, $one_group);
                } else {
                    $merged_users = ResultRouteQualificationClassic::get_participant_qualification_group($event, $amount_the_best_participant_to_go_final);

                }
            } else {
                $merged_users = ResultRouteQualificationClassic::get_participant_qualification_gender($event, $amount_the_best_participant_to_go_final);
            }
        }

        if($get_array){
            return $merged_users->toArray();
        } else {
            return $merged_users;
        }

    }

    public static function get_participant_semifinal($event, $amount, $one_group)
    {
        if($one_group) {
            $users_female = ResultSemiFinalStage::better_of_participants_semifinal_stage($event->id, 'female', $amount, $one_group->id);
            $users_male = ResultSemiFinalStage::better_of_participants_semifinal_stage($event->id, 'male', $amount, $one_group->id);
        } else {
            $users_female = ResultSemiFinalStage::better_of_participants_semifinal_stage($event->id, 'female', $amount);
            $users_male = ResultSemiFinalStage::better_of_participants_semifinal_stage($event->id, 'male', $amount);
        }
        return $users_male->merge($users_female);
    }


}
