<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResultQualificationLikeFinal extends Model
{
    protected $table = 'result_qualification_like_final';

    public $timestamps = true;


    public function event()
    {
        return $this->belongsTo(Event::class)->where('active', '=', 1);
    }

    public function number_set(){
        return $this->belongsTo(Set::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function better_of_participants_qualification_like_final_stage($event_id, $gender, $amount_better, $category_id=null){
        if($category_id){
            $participant_final_users_id = ResultQualificationLikeFinal::where('event_id', '=', $event_id)->where('category_id', '=', $category_id)->pluck('user_id')->toArray();
        } else {
            $participant_final_users_id = ResultQualificationLikeFinal::where('event_id', '=', $event_id)->pluck('user_id')->toArray();
        }
        $users_id = User::whereIn('id', $participant_final_users_id)->where('gender', '=', $gender)->pluck('id');
        $participant_final_sort_id = ResultQualificationLikeFinal::whereIn('user_id', $users_id)->where('event_id', '=', $event_id)->get()->sortBy('place')->pluck('user_id');
        $after_slice_participant_final_sort_id = array_slice($participant_final_sort_id->toArray(), 0, $amount_better);
        return User::whereIn('id', $after_slice_participant_final_sort_id)->get();
    }
}
