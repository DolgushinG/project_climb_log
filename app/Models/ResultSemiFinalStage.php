<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResultSemiFinalStage extends Model
{
    use HasFactory;
    protected $table = 'result_semifinal_stage';
    public $timestamps = true;
    protected $casts = [
        'result_for_edit_semifinal' =>'json',
    ];
    public function event()
    {
        return $this->belongsTo(Event::class)->where('active', '=', 1);
    }
    public function category(){
        return $this->belongsTo(ParticipantCategory::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function better_of_participants_semifinal_stage($event_id, $gender, $amount_better, $category_id=null){
        if($category_id){
            $participant_final_users_id = ResultSemiFinalStage::where('event_id', '=', $event_id)->where('category_id', '=', $category_id)->pluck('user_id')->toArray();
        } else{
            $participant_final_users_id = ResultSemiFinalStage::where('event_id', '=', $event_id)->pluck('user_id')->toArray();
        }
        $users_id = User::whereIn('id', $participant_final_users_id)->pluck('id');
        $participant_final_sort_id = ResultSemiFinalStage::whereIn('user_id', $users_id)->where('gender', '=', $gender)->where('event_id', '=', $event_id)->get()->sortBy('place')->pluck('user_id');
        $after_slice_participant_final_sort_id = array_slice($participant_final_sort_id->toArray(), 0,$amount_better);
        return User::whereIn('id', $after_slice_participant_final_sort_id)->get();
    }


    public static function get_participant_semifinal($event, $one_group=null, $get_array=false)
    {
        $amount_the_best_participant_to_go_semifinal = $event->amount_the_best_participant ?? 10;
        if($event->is_sort_group_semifinal){
            if($one_group){
                $merged_users = ResultRouteQualificationClassic::get_participant_qualification_only_one_group($event, $amount_the_best_participant_to_go_semifinal, $one_group);
            } else {
                $merged_users = ResultRouteQualificationClassic::get_participant_qualification_group($event, $amount_the_best_participant_to_go_semifinal);
            }
        } else {
            $merged_users = ResultRouteQualificationClassic::get_participant_qualification_gender($event, $amount_the_best_participant_to_go_semifinal);
        }
        if($get_array){
            return $merged_users->toArray();
        } else {
            return $merged_users;
        }
    }
    public static function get_global_participant_semifinal($event, $one_group=null, $get_array=false)
    {
        $amount_the_best_participant_to_go_semifinal = $event->amount_the_best_participant ?? 10;
        if($event->is_sort_group_semifinal){
            if($one_group){
                $merged_users = ResultRouteQualificationClassic::get_global_participant_qualification_only_one_group($event, $amount_the_best_participant_to_go_semifinal, $one_group);
            } else {
                $merged_users = ResultRouteQualificationClassic::get_global_participant_qualification_group($event, $amount_the_best_participant_to_go_semifinal);
            }
        } else {
            $merged_users = ResultRouteQualificationClassic::get_global_participant_qualification_gender($event, $amount_the_best_participant_to_go_semifinal);
        }
        if($get_array){
            return $merged_users->toArray();
        } else {
            return $merged_users;
        }
    }

}
