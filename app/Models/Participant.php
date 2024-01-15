<?php

namespace App\Models;

use Encore\Admin\Facades\Admin;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Participant extends Model
{
    use HasFactory;

    protected $table = 'participants';


    public function owner()
    {
        return $this->belongsTo(User::class);
    }

    public static function custom_sort($sort, $array)
    {
        usort($array, function($a, $b) use ($sort) {
            return $a['result'][$sort] <=> $b['result'][$sort];
        });
        return $array;
    }
    public static function is_next_step($array, $sort){
        $next_sort = false;
        $same_amount_top = array();
        foreach ($array as $res){
            if (isset($same_amount_top[$res[$sort]])){
                $same_amount_top[$res[$sort]] += 1;
                $next_sort = true;
            } else {
                $same_amount_top[$res[$sort]] = 1;
            }
        }
        return $next_sort;
    }
    public static function counting_final_place($event_id, $result_final = null){
        # Считаем результаты по результатам квалификации и финала
        $sorted_amount_top = self::custom_sort('amount_top', $result_final);
        # Проверяем есть ли участники с одинаковым кол-вом топов, если есть идем дальше по сортировкам
        if (!self::is_next_step($sorted_amount_top, 'amount_top')){
            return $sorted_amount_top;
        }
        $sorted_amount_try_top = self::custom_sort('amount_try_top', $sorted_amount_top);
        if (!self::is_next_step($sorted_amount_try_top, 'amount_try_top')){
            return $sorted_amount_try_top;
        }
        $sorted_amount_zone = self::custom_sort('amount_zone', $sorted_amount_try_top);
        if (!self::is_next_step($sorted_amount_zone, 'amount_zone')){
            return $sorted_amount_zone;
        }
        $sorted_try_zone = self::custom_sort('amount_try_zone', $sorted_amount_zone);
        if (!self::is_next_step($sorted_try_zone, 'amount_try_zone')){
           return $sorted_try_zone;
        }
        # Если не разложились смотрим результаты квалификации
        $new = array();
        foreach ($sorted_try_zone as $res){
            $res['place'] = Participant::get_places_participant_in_qualification($event_id, $res['result']['user_id']);
            $new[] = $res;
        }
        usort($new, function($a, $b) {
            return $a['place'] <=> $b['place'];
        });
        return $new;
    }

    public static function get_places_participant_in_qualification($event_id, $user_id = null){
        $gender = User::gender($user_id);
        $users_id = User::where('gender', '=', $gender)->pluck('id');
        $all_participant_event = Participant::whereIn('user_id', $users_id)->where('event_id', '=', $event_id)->orderBy('points', 'DESC')->get();
        $user_places = array();
        foreach ($all_participant_event as $index => $user){
            $user_places[$user->user_id] = $index+1;
        }
        if ($user_id){
            return $user_places[$user_id];
        }
        return $user_places;
    }

    public static function participant_with_result($event_id, $gender)
    {
        $active_participant = Participant::where('event_id', '=', $event_id)->where('active', '=', 1)->pluck('user_id')->toArray();
        if ($active_participant) {
            return count(User::whereIn('id', $active_participant)->where('gender', '=', $gender)->get()->toArray());
        } else {
            return 1;
        }
    }
    public static function is_active_participant($event_id, $user_id)
    {
        $active_participant = Participant::where('event_id', '=', $event_id)->where('user_id', '=', $user_id)->where('active', '=', 1)->first();
        if ($active_participant) {
            return true;
        } else {
            return false;
        }
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
