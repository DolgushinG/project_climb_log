<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use function Symfony\Component\String\s;

class ResultQualificationClassic extends Model
{
    use HasFactory;
    public $timestamps = true;

    protected $table = 'result_qualification_classic';

    protected $casts = [
        'result_for_edit' =>'json',
    ];


    public function owner()
    {
        return $this->belongsTo(User::class);
    }

    public static function number_sets($owner_id)
    {
        return Set::where('owner_id', $owner_id)->pluck('number_set', 'id')->toArray();
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
            $users_for_filter = ResultQualificationClassic::where('event_id', $event_id)->pluck('user_id')->toArray();
            if($type == 'final'){
                $is_semifinal = Event::find($event_id)->is_semifinal;
                if($is_semifinal){
                    $place = ResultQualificationClassic::get_place_participant_in_semifinal($event_id, $d_array['user_id']);
                } else {
                    $gender = User::find($d_array['user_id'])->gender;
                    $category_id = ResultQualificationClassic::where('user_id', '=', $d_array['user_id'])->where('event_id', '=', $event_id)->first()->category_id;
                    $place = ResultQualificationClassic::get_places_participant_in_qualification($event_id, $users_for_filter, $d_array['user_id'], $gender, $category_id, true);
                }
            } else {
                $gender = User::find($d_array['user_id'])->gender;
                if($type == "france_system_qualification"){
                    $place = ResultQualificationClassic::get_place_participant_in_france_system_qualification($event_id, $d_array['user_id']);
                } else {
                    $category_id = ResultQualificationClassic::where('user_id', '=', $d_array['user_id'])->where('event_id', '=', $event_id)->first()->category_id;
                    $place = ResultQualificationClassic::get_places_participant_in_qualification($event_id, $users_for_filter, $d_array['user_id'], $gender, $category_id, true);
                }

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
    public static function get_place_participant_in_france_system_qualification($event_id, $user_id){
        return ResultFranceSystemQualification::where('user_id','=', $user_id)->where('event_id', '=', $event_id)->first()->place;
    }

    public static function update_places_in_qualification_classic($event_id, $participants)
    {
        foreach ($participants as $index => $participant){
            $participant_result = ResultQualificationClassic::where('event_id', '=', $event_id)->where('user_id', '=', $participant->user_id)->first();
            $participant_result->user_place = $index+1;
            $participant_result->save();
        }
    }

    public static function get_places_participant_in_qualification($event_id, $filter_users, $user_id, $gender, $category_id=null, $get_place_user = false){
        $users_id = User::whereIn('id', $filter_users)->where('gender', '=', $gender)->pluck('id');
        if($category_id){
            $all_participant_event = ResultQualificationClassic::whereIn('user_id', $users_id)->where('category_id', '=', $category_id)->where('event_id', '=', $event_id)->orderBy('points', 'DESC')->get();
        } else {
            $all_participant_event = ResultQualificationClassic::whereIn('user_id', $users_id)->where('event_id', '=', $event_id)->orderBy('points', 'DESC')->get();
        }
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
            $active_participant = ResultQualificationClassic::where('event_id', '=', $event_id)->where('category_id', '=', $category_id)->where('active', '=', 1)->pluck('user_id')->toArray();
        } else {
            $active_participant = ResultQualificationClassic::where('event_id', '=', $event_id)->where('active', '=', 1)->pluck('user_id')->toArray();
        }
        if ($active_participant) {
            return count(User::whereIn('id', $active_participant)->where('gender', '=', $gender)->get()->toArray());
        } else {
            return 1;
        }
    }
    public static function is_active_participant($event_id, $user_id)
    {
        $active_participant = ResultQualificationClassic::where('event_id', '=', $event_id)->where('user_id', '=', $user_id)->where('active', '=', 1)->first();
        if ($active_participant) {
            return true;
        } else {
            return false;
        }
    }

    public static function better_participants($event_id, $gender, $amount_better, $category_id = null){
        if($category_id){
            $participant_users_id = ResultQualificationClassic::where('event_id', '=', $event_id)->where('category_id', '=', $category_id)->pluck('user_id')->toArray();
        } else {
            $participant_users_id = ResultQualificationClassic::where('event_id', '=', $event_id)->pluck('user_id')->toArray();
        }
        $users_id = User::whereIn('id', $participant_users_id)->where('gender', '=', $gender)->pluck('id');
        $participant_sort_id = ResultQualificationClassic::whereIn('user_id', $users_id)->where('event_id', '=', $event_id)->where('active', '=', 1)->get()->sortByDesc('points')->pluck('user_id');
        $after_slice_participant_final_sort_id = array_slice($participant_sort_id->toArray(), 0, $amount_better);
        return User::whereIn('id', $after_slice_participant_final_sort_id)->get();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function participant_number_set($user_id, $event_id){
        if($user_id && $event_id){
            $event = Event::find($event_id);
            if($event->is_france_system_qualification){
                $number_set_id = ResultFranceSystemQualification::where('user_id', $user_id)->where('event_id', $event_id)->first()->number_set_id ?? null;
            } else {
                $number_set_id = ResultQualificationClassic::where('user_id', $user_id)->where('event_id', $event_id)->first()->number_set_id ?? null;
            }
            return Set::find($number_set_id)->number_set;
        }
        return null;
    }

    public function event()
    {
        return $this->belongsTo(Event::class)->where('active', '=', 1);
    }

    public function category(){
        return $this->belongsTo(ParticipantCategory::class);
    }

    public function number_set(){
        return $this->belongsTo(Set::class);
    }

    public static function get_sorted_group_participant($event_id, $gender, $category_id)
    {
        $users = User::query()
            ->leftJoin('result_qualification_classic', 'users.id', '=', 'result_qualification_classic.user_id')
            ->where('result_qualification_classic.event_id', '=', $event_id)
            ->where('result_qualification_classic.category_id', '=', $category_id)
            ->select(
                'users.id',
                'users.city',
                'result_qualification_classic.user_place',
                'users.middlename',
                'result_qualification_classic.points',
                'result_qualification_classic.owner_id',
                'result_qualification_classic.gender',
                'result_qualification_classic.category_id',
                'result_qualification_classic.number_set_id',
            )
            ->where('result_qualification_classic.gender', '=', $gender)->get()->sortBy('user_place')->toArray();
        $event = Event::find($event_id);
        $users_for_filter = ResultQualificationClassic::where('event_id', $event_id)->pluck('user_id')->toArray();
        foreach ($users as $index => $user){
            $place = ResultQualificationClassic::get_places_participant_in_qualification($event_id, $users_for_filter,  $user['id'], $gender, $category_id, true);
            $set = Set::find($user['number_set_id']);
            $users[$index]['user_place'] = $place;
            if($event->is_input_set != 1){
                $users[$index]['number_set_id'] = $set->number_set;
            }

        }
        $users_need_sorted = collect($users)->toArray();
        usort($users_need_sorted, function ($a, $b) {
            // Проверяем, если значение 'user_place' пустое, перемещаем его в конец
            if (empty($a['user_place'])) {
                return 1; // $a должно быть после $b
            } elseif (empty($b['user_place'])) {
                return -1; // $a должно быть перед $b
            } else {
                return $a['user_place'] <=> $b['user_place'];
            }
        });
        return collect($users_need_sorted);
    }

    public static function get_list_passed_route($event_id, $user_id)
    {
        return ResultRouteQualificationClassic::where('event_id', $event_id)->where('user_id', $user_id)->whereIn('attempt', [1,2])->pluck('grade');
    }

    public static function find_grade($list_grades, $grade)
    {
        return in_array($grade, $list_grades);
    }

    public static function findMaxKey($array) {
        $maxValue = null;
        $maxKey = null;

        foreach ($array as $key => $value) {
            if ($maxValue === null || $value > $maxValue) {
                $maxValue = $value;
                $maxKey = $key;
            }
        }

        return $maxKey;
    }

    public static function get_category_from_result($event, $the_best_route)
    {
        $participant_categories = ParticipantCategory::where('event_id', $event->id)->get()->pluck('category')->toArray();
        $new_convert_options = self::convert_categories($event->options_categories, $participant_categories);
        $next = [];
        foreach ($new_convert_options as $opt){
            $next[] = self::values_in_range(Grades::grades(), $opt['from'], $opt['to']);
        }

        $result = array();
        # Бежим по лучшим трассам (6C, 7A, 7A+)
        foreach ($the_best_route as $route){
            foreach ($new_convert_options as $index => $opt){
                 if(self::find_grade($next[$index], $route)){
                     if (array_key_exists($opt['category'], $result)) {
                         $result[$opt['category']]++;
                     } else {
                         $result[$opt['category']] = 1;
                     }
                 }
            }
        }
        return self::findMaxKey($result);
    }

    public static function values_in_range($array, $start, $end)
    {
        $startPrinting = false;
        $valuesInRange = [];

        foreach ($array as $value) {
            if ($value == $start) {
                $startPrinting = true;
            }

            if ($startPrinting) {
                $valuesInRange[] = $value;
            }

            if ($value == $end) {
                break;
            }
        }

        if (!in_array($end, $valuesInRange)) {
            // If end value not found in the array, add it to the end
            $valuesInRange[] = $end;
        }

        return $valuesInRange;
    }

    public static function convert_categories($array, $categories)
    {
        $res = [];
        foreach ($array as $el){
            $res[] = array(
                'category' => $categories[$el['Категория участника']],
                'to' => $el['До какой категории сложности определять эту категорию'],
                'from' => $el['От какой категории сложности определять эту категорию']);
        }
        return $res;
    }

    public static function send_main_about_take_part($event, $user)
    {
        if (!str_contains($user->email, 'telegram')) {
            $details = array();
            $details['title'] = $event->title;
            $details['event_start_date'] = $event->start_date;
            $details['event_url'] = env('APP_URL').$event->link;
            $details['link_payment'] = $event->link_payment;
            if($event->registration_time_expired){
                $details['pay_time_expired'] = $event->registration_time_expired;
            }
            $details['img_payment'] = $event->img_payment;
            $details['info_payment'] = $event->info_payment;
            $details['image'] = $event->image;
            Mail::to($user->email)->queue(new \App\Mail\TakePart($details));
        }

    }

    public static function send_confirm_bill($event, $user)
    {
        if (!str_contains($user->email, 'telegram')) {
            $details = array();
            $details['title'] = $event->title;
            $details['event_start_date'] = $event->start_date;
            $details['event_url'] = env('APP_URL').$event->link;
//            $details['image'] = $event->image;
            Mail::to($user->email)->queue(new \App\Mail\ConfirmBill($details));
        }

    }

    public static function send_message_from_climbing_gym($subject, $message, $user, $climbing_gym_name)
    {
        if (!str_contains($user['email'], 'telegram')) {
            $details = array();
            $details['subject'] = $subject;
            $details['message'] = $message;
            $details['middlename'] = $user['middlename'];
            $details['climbing_gym_name'] = $climbing_gym_name;
            Mail::to($user['email'])->queue(new \App\Mail\MessageParticipants($details));
        }

    }
}
