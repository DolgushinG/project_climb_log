<?php

namespace App\Models;

use Encore\Admin\Facades\Admin;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use function Symfony\Component\Translation\t;

class Participant extends Model
{
    use HasFactory;

    protected $table = 'participants';


    public function owner()
    {
        return $this->belongsTo(User::class);
    }
    public static function counting_final_place($event_id, $result_final){
        // Сортировка по amount_top в убывающем порядке, затем по amount_try_top в возрастающем порядке,
        // затем по amount_zone в убывающем порядке, затем по amount_try_zone в возрастающем порядке
        usort($result_final, function ($a, $b) {
            return $b['amount_top'] <=> $a['amount_top']
                ?: $a['amount_try_top'] <=> $b['amount_try_top']
                ?: $b['amount_zone'] <=> $a['amount_zone']
                ?: $a['amount_try_zone'] <=> $b['amount_try_zone'];
        });

        // Группировка по ключам amount_top, amount_try_top, amount_zone, amount_try_zone
        $grouped_results = [];
        foreach ($result_final as $item) {
            $key = "{$item['amount_top']}_{$item['amount_try_top']}_{$item['amount_zone']}_{$item['amount_try_zone']}";
            $grouped_results[$key][] = $item;
        }

// Фильтрация групп, оставляем только те, где количество элементов больше 1 (т.е., где есть дубликаты)
        $duplicate_groups = array_filter($grouped_results, function ($group) {
            return count($group) > 1;
        });

// Преобразование групп в одномерный массив
        $duplicate_arrays = array_reduce($duplicate_groups, 'array_merge', []);
        $user_places = array();
        foreach ($duplicate_arrays as $index => $d_array){
            $place = Participant::get_places_participant_in_qualification($event_id, $d_array['user_id'], true);
            $index_user_final_in_res = self::findIndexByUserId($result_final, $d_array['user_id']);
            $user_places[] = array('user_id' => $d_array['user_id'], 'qualification_place' => $place, 'index' => $index_user_final_in_res);
        }
        usort($user_places, function ($a, $b) {
            return $a['qualification_place'] <=> $b['qualification_place'];
        });
        foreach ($user_places as $index => $user_place){
            $result_final[$user_place['index']]['place'] = $user_place['index'] + 1;
        }
        // Расставляем места в зависимости от результатов квалификации
        foreach ($result_final as $index => $result){
            if (!$result['place']){
                $result_final[$index]['place'] = $index+1;
            }
        }
       return $result_final;
    }
    public static function findIndexByUserId($array, $userId) {
        foreach ($array as $key => $item) {
            if ($item['user_id'] == $userId) {
                return $key;
            }
        }
        return -1; // Если не найдено
    }

    public static function get_places_participant_in_qualification($event_id, $user_id, $get_place_user = false){
        $user = User::find($user_id);
        $users_id = User::where('gender', '=', $user->gender)->where('category', '=', $user->category)->pluck('id');
        $all_participant_event = Participant::whereIn('user_id', $users_id)->where('event_id', '=', $event_id)->orderBy('points', 'DESC')->get();
        $user_places = array();
        foreach ($all_participant_event as $index => $user){
            $user_places[$user->user_id] = $index+1;
        }
        if ($get_place_user){
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
