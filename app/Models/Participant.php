<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Participant extends Model
{
    use HasFactory;

    protected $table = 'participants';

    public function owner()
    {
        return $this->belongsTo(User::class);
    }
    public static function counting_final_place($event_id, $result_final, $type='final'){
//        dd($result_final);
        // Сортировка по amount_top в убывающем порядке, затем по amount_try_top в возрастающем порядке,
        // затем по amount_zone в убывающем порядке, затем по amount_try_zone в возрастающем порядке
        usort($result_final, function ($a, $b) {
            return $b['amount_top'] <=> $a['amount_top']
                ?: $b['amount_zone'] <=> $a['amount_zone']
                ?: $a['amount_try_top'] <=> $b['amount_try_top']
                ?: $a['amount_try_zone'] <=> $b['amount_try_zone'];
        });

        // Группировка по ключам amount_top, amount_try_top, amount_zone, amount_try_zone
        $grouped_results = [];
        foreach ($result_final as $item) {
            $key = "{$item['amount_top']}_{$item['amount_try_top']}_{$item['amount_zone']}_{$item['amount_try_zone']}";
            $grouped_results[$key][] = $item;
        }
//        dd($grouped_results);
// Фильтрация групп, оставляем только те, где количество элементов больше 1 (т.е., где есть дубликаты)
        $duplicate_groups = array_filter($grouped_results, function ($group) {
            return count($group) > 1;
        });

// Преобразование групп в одномерный массив
        $duplicate_arrays = array_reduce($duplicate_groups, 'array_merge', []);

        $user_places = array();
        foreach ($duplicate_arrays as $index => $d_array){
            if($type == 'final'){
                $is_semifinal = Event::find($event_id)->is_semifinal;
                if($is_semifinal){
                    $place = Participant::get_place_participant_in_semifinal($event_id, $d_array['user_id']);
                } else {
                    $gender = User::find($d_array['user_id'])->gender;
                    $category_id = Participant::where('user_id', '=', $d_array['user_id'])->where('event_id', '=', $event_id)->first()->category_id;
                    $place = Participant::get_places_participant_in_qualification($event_id, $d_array['user_id'], $gender, $category_id, true);
                }
            } else {
                $gender = User::find($d_array['user_id'])->gender;
                $category_id = Participant::where('user_id', '=', $d_array['user_id'])->where('event_id', '=', $event_id)->first()->category_id;
                $place = Participant::get_places_participant_in_qualification($event_id, $d_array['user_id'], $gender, $category_id, true);
            }
            $index_user_final_in_res = self::findIndexBy($result_final, $d_array['user_id'], 'user_id');
            $user_places[] = array('user_id' => $d_array['user_id'], 'place' => $place, 'index' => $index_user_final_in_res);

        }
       # Если есть дубликаты то в $user_places будут сортированы результаты
        if($user_places != []) {
            usort($user_places, function ($a, $b) {
                return $a['index'] <=> $b['index'];
            });
            $start_replace_in_result = $user_places[0]['index'];
            $count_replace_el_in_result = count($user_places);
            usort($user_places, function ($a, $b) {
                return $a['place'] <=> $b['place'];
            });
            $index = 0;
            $temp_array_for_result = array();

            for ($i = $start_replace_in_result; $i < $count_replace_el_in_result; $i++) {
                $temp_array_for_result[] = $result_final[$user_places[$index]['index']];
                $index++;
            }
            $x = 0;
            for ($i = $start_replace_in_result; $i < $count_replace_el_in_result; $i++) {
                $result_final[$i] = $temp_array_for_result[$x];
                $x++;
            }
            # Расставляем места
            foreach ($user_places as $index => $user_place) {
                $result_final[$user_place['index']]['place'] = $user_place['index'] + 1;
            }
        }
//        // Расставляем места в зависимости от результатов квалификации
        foreach ($result_final as $index => $result){
            if (!$result['place']){
                $result_final[$index]['place'] = $index+1;
            }
        }
        usort($result_final, function ($a, $b) {
            return $a['place'] <=> $b['place'];
        });
//        dd($result_final);
       return $result_final;
    }
    public static function findIndexBy($array, $element, $needle) {
        foreach ($array as $key => $item) {
            if ($item[$needle] == $element) {
                return $key;
            }
        }
        return -1; // Если не найдено
    }

    public static function get_place_participant_in_semifinal($event_id, $user_id){
        return ResultSemiFinalStage::where('user_id','=', $user_id)->where('event_id', '=', $event_id)->first()->place;
    }
    public static function get_places_participant_in_qualification($event_id, $user_id, $gender, $category_id, $get_place_user = false){
        $users_id = User::where('gender', '=', $gender)->pluck('id');
        $all_participant_event = Participant::whereIn('user_id', $users_id)->where('category_id', '=', $category_id)->where('event_id', '=', $event_id)->orderBy('points', 'DESC')->get();
        $user_places = array();
        foreach ($all_participant_event as $index => $user){
            $user_places[$user->user_id] = $index+1;
        }
        if ($get_place_user){
            return $user_places[$user_id];
        }
        return $user_places;
    }

    public static function participant_with_result($event_id, $gender, $category_id=null)
    {

        if($category_id){
            $active_participant = Participant::where('event_id', '=', $event_id)->where('category_id', '=', $category_id)->where('active', '=', 1)->pluck('user_id')->toArray();
        } else {
            $active_participant = Participant::where('event_id', '=', $event_id)->where('active', '=', 1)->pluck('user_id')->toArray();
        }
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

    public static function better_participants($event_id, $gender, $amount_better, $category_id = null){
        if($category_id){
            $participant_users_id = Participant::where('event_id', '=', $event_id)->where('category_id', '=', $category_id)->pluck('user_id')->toArray();
        } else {
            $participant_users_id = Participant::where('event_id', '=', $event_id)->pluck('user_id')->toArray();
        }
        $users_id = User::whereIn('id', $participant_users_id)->where('gender', '=', $gender)->pluck('id');
        $participant_sort_id = Participant::whereIn('user_id', $users_id)->where('event_id', '=', $event_id)->where('active', '=', 1)->get()->sortByDesc('points')->pluck('user_id');
        $after_slice_participant_final_sort_id = array_slice($participant_sort_id->toArray(), 0, $amount_better);
        return User::whereIn('id', $after_slice_participant_final_sort_id)->get();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class)->where('active', '=', 1);
    }

    public function category(){
        return $this->belongsTo(ParticipantCategory::class);
    }
}
