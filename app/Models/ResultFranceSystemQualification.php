<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResultFranceSystemQualification extends Model
{
    protected $table = 'result_france_system_qualification';

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

    public static function better_of_participants_france_system_qualification($event_id, $gender, $amount_better, $category_id=null){
        if($category_id){
            $participant_final_users_id = ResultFranceSystemQualification::where('event_id', '=', $event_id)->where('category_id', '=', $category_id)->pluck('user_id')->toArray();
        } else {
            $participant_final_users_id = ResultFranceSystemQualification::where('event_id', '=', $event_id)->pluck('user_id')->toArray();
        }
        $users_id = User::whereIn('id', $participant_final_users_id)->where('gender', '=', $gender)->pluck('id');
        $participant_final_sort_id = ResultFranceSystemQualification::whereIn('user_id', $users_id)->where('event_id', '=', $event_id)->get()->sortBy('place')->pluck('user_id');
        $after_slice_participant_final_sort_id = array_slice($participant_final_sort_id->toArray(), 0, $amount_better);
        return User::whereIn('id', $after_slice_participant_final_sort_id)->get();
    }

    public static function get_users_qualification_result($table, $event_id, $gender)
    {
        return User::query()
            ->leftJoin($table, 'users.id', '=', $table.'.user_id')
            ->where($table.'.event_id', '=', $event_id)
            ->select(
                $table.'.place',
                'users.id',
                'users.middlename',
                $table.'.category_id',
                $table.'.amount_top',
                $table.'.amount_try_top',
                $table.'.amount_zone',
                $table.'.amount_try_zone',
            )->where($table.'.gender', '=', $gender)->get();
    }
}
