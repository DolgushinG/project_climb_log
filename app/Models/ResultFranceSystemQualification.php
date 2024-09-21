<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ResultFranceSystemQualification extends Model
{
    protected $table = 'result_france_system_qualification';

    public $timestamps = true;

    protected $casts = [
        'result_for_edit_france_system_qualification' =>'json',
        'products_and_discounts' =>'json',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class)->where('active', '=', 1);
    }

    public function number_set(){
        return $this->belongsTo(Set::class);
    }

    public function category(){
        return $this->belongsTo(ParticipantCategory::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function better_of_participants_france_system_qualification($event_id, $gender, $amount_better, $category_id=null){
        if($category_id){
            $participant_final_users_id = ResultFranceSystemQualification::where('event_id', '=', $event_id)->where('gender', '=', $gender)->where('category_id', '=', $category_id)->pluck('user_id')->toArray();
        } else {
            $participant_final_users_id = ResultFranceSystemQualification::where('event_id', '=', $event_id)->where('gender', '=', $gender)->pluck('user_id')->toArray();
        }
        $users_id = User::whereIn('id', $participant_final_users_id)->pluck('id');
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

    public static function get_qualification_france_global_participants($event, $one_group=null, $get_array=false)
    {
        $amount_participant = ResultFranceSystemQualification::where('event_id', '=', $event->id)->get()->count();
        if($one_group){
            $merged_users = ResultRouteQualificationClassic::get_global_participant_qualification_only_one_group($event, $amount_participant, $one_group);
        } else {
            $merged_users = ResultRouteQualificationClassic::get_global_participant_qualification_group($event, $amount_participant);
        }

        if($get_array){
            return $merged_users->toArray();
        } else {
            return $merged_users;
        }

    }
    public static function get_qualification_france_participants($event, $one_group=null, $get_array=false)
    {
        $amount_participant = ResultFranceSystemQualification::where('event_id', '=', $event->id)->get()->count();
        if($one_group){
            $merged_users = ResultRouteQualificationClassic::get_participant_qualification_only_one_group($event, $amount_participant, $one_group);
        } else {
            $merged_users = ResultRouteQualificationClassic::get_participant_qualification_group($event, $amount_participant);
        }

        if($get_array){
            return $merged_users->toArray();
        } else {
            return $merged_users;
        }

    }

    public static function update_france_route_results(
        $owner_id,
        $event_id,
        $category_id,
        $route_id,
        $user_id,
        $amount_try_top,
        $amount_try_zone,
        $amount_top,
        $amount_zone,
        $gender,
        $all_attempts,
        $number_set_id
    )
    {
        $result_for_edit = [[
            'Номер маршрута' => $route_id,
            'Попытки на топ' => $amount_try_top,
            'Попытки на зону' => $amount_try_zone
        ]];
        $participant = ResultFranceSystemQualification::where('event_id', $event_id)
            ->where('user_id', $user_id)
            ->first();
        $result_route = ResultRouteFranceSystemQualification::where('event_id', $event_id)
            ->where('user_id', $user_id)
            ->where('route_id', $route_id)
            ->first();
        $result_all_route = ResultRouteFranceSystemQualification::where('event_id', $event_id)
            ->where('user_id', $user_id)
            ->first();
        if(!$result_all_route){
            $participant->active = 1;
            $participant->save();
        }
        $existing_result_for_edit = $participant->result_for_edit_france_system_qualification ?? [];
        # Если уже есть результат надо обновить его как в grid - $participant - json for edit так и в $result по трассам
        if($result_route){
            $result_route->all_attempts = $all_attempts;
            $result_route->amount_top = $amount_top;
            $result_route->amount_try_top = $amount_try_top;
            $result_route->amount_zone = $amount_zone;
            $result_route->amount_try_zone = $amount_try_zone;
            $result_route->save();
            foreach ($existing_result_for_edit as $index => $res){
                if($res['Номер маршрута'] == $route_id){
                    $existing_result_for_edit[$index]['Попытки на топ'] = $amount_try_top;
                    $existing_result_for_edit[$index]['Попытки на зону'] = $amount_try_zone;
                }
            }
            self::update_results_fsq($participant, $existing_result_for_edit);
        } else {
            # Создание результата трассы который еще не было
            self::create_results_fsq($participant, $existing_result_for_edit, $result_for_edit);
            $data = [['owner_id' => $owner_id,
                'user_id' => $user_id,
                'event_id' => $event_id,
                'route_id' => $route_id,
                'category_id' => $category_id,
                'number_set_id' => $number_set_id,
                'amount_top' => $amount_top,
                'gender' => $gender,
                'all_attempts' => $all_attempts,
                'amount_try_top' => $amount_try_top,
                'amount_zone' => $amount_zone,
                'amount_try_zone' => $amount_try_zone,
            ]];
            self::update_results_rrfsq($data, 'result_route_france_system_qualification');
        }
    }
    public static function update_results_rrfsq($data, $table)
    {
        DB::table($table)->insert($data);
    }

    public static function create_results_fsq($participant, $results_old_for_edit, $result_for_edit)
    {
        $merged_result_for_edit = array_merge($results_old_for_edit, $result_for_edit);
        // Сортируем массив по "Номеру маршрута"
        usort($merged_result_for_edit, function ($a, $b) {
            return $a['Номер маршрута'] <=> $b['Номер маршрута'];
        });
        $participant->result_for_edit_france_system_qualification = $merged_result_for_edit;
        $participant->save();
    }
    public static function update_results_fsq($participant, $result_for_edit)
    {
        $participant->result_for_edit_france_system_qualification = $result_for_edit;
        $participant->save();
    }
}
