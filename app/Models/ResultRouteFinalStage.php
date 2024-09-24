<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ResultRouteFinalStage extends Model
{
    protected $table = 'result_route_final_stage';
    public $timestamps = true;

    public function event()
    {
        return $this->belongsTo(Event::class)->where('active', '=', 1);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category(){
        return $this->belongsTo(ParticipantCategory::class);
    }

    public static function count_route_in_final_stage($event_id, $toArrayString = false){
        $count_routes = ResultRouteFinalStage::where('event_id', '=', $event_id)->distinct()->get('final_route_id')->count();
        if($toArrayString){
            $routes = [];
            for($i = 1; $i <= $count_routes; $i++){
                $routes[] =  $i.' трасса финала';
            }
            return $routes;
        }
        return $count_routes;

    }

    public static function update_semi_or_final_route_results(
        $stage,
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
    ): void
    {
        $result_for_edit = [[
            'Номер маршрута' => $route_id,
            'Попытки на топ' => $amount_try_top,
            'Попытки на зону' => $amount_try_zone
        ]];
        if($stage == 'final'){
            $participant = ResultFinalStage::where('event_id', $event_id)
                ->where('user_id', $user_id)
                ->first();
            if(!$participant){
                $participant = new ResultFinalStage;
            }
            $result_route = ResultRouteFinalStage::where('event_id', $event_id)
                ->where('user_id', $user_id)
                ->where('final_route_id', $route_id)
                ->first();
            $existing_result_for_edit = $participant->result_for_edit_final ?? [];
            $table = 'result_route_final_stage';
        }
        if($stage == 'semifinal'){
            $participant = ResultSemiFinalStage::where('event_id', $event_id)
                ->where('user_id', $user_id)
                ->first();
            if(!$participant){
                $participant = new ResultSemiFinalStage;
            }
            $result_route = ResultRouteSemiFinalStage::where('event_id', $event_id)
                ->where('user_id', $user_id)
                ->where('final_route_id', $route_id)
                ->first();
            $existing_result_for_edit = $participant->result_for_edit_semifinal ?? [];
            $table = 'result_route_semifinal_stage';
        }

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
            self::update_results_semi_or_final($stage, $participant, $existing_result_for_edit);
        } else {
            # Создание результата трассы который еще не было
            self::create_results_semi_or_final($stage, $owner_id, $event_id, $user_id, $gender, $category_id, $participant, $existing_result_for_edit, $result_for_edit);
            $data = [['owner_id' => $owner_id,
                'user_id' => $user_id,
                'event_id' => $event_id,
                'final_route_id' => $route_id,
                'category_id' => $category_id,
                'amount_top' => $amount_top,
                'gender' => $gender,
                'all_attempts' => $all_attempts,
                'amount_try_top' => $amount_try_top,
                'amount_zone' => $amount_zone,
                'amount_try_zone' => $amount_try_zone,
            ]];
            self::update_results_in_db($data, $table);
        }
    }
    public static function update_results_semi_or_final($stage, $participant, $result_for_edit)
    {
        if($stage == 'final'){
            $participant->result_for_edit_final = $result_for_edit;
        }
        if($stage == 'semifinal'){
            $participant->result_for_edit_semifinal = $result_for_edit;
        }
        $participant->save();
    }
    public static function update_results_in_db($data, $table)
    {
        DB::table($table)->insert($data);
    }
    public static function create_results_semi_or_final($stage, $owner_id, $event_id, $user_id, $gender, $category_id, $participant, $results_old_for_edit, $result_for_edit)
    {
        $merged_result_for_edit = array_merge($results_old_for_edit, $result_for_edit);
        // Сортируем массив по "Номеру маршрута"
        usort($merged_result_for_edit, function ($a, $b) {
            return $a['Номер маршрута'] <=> $b['Номер маршрута'];
        });
        $participant->owner_id = $owner_id;
        $participant->event_id = $event_id;
        $participant->user_id = $user_id;
        $participant->gender = $gender;
        $participant->category_id = $category_id;
        if($stage == 'final'){
            $participant->result_for_edit_final = $merged_result_for_edit;
        }
        if($stage == 'semifinal'){
            $participant->result_for_edit_semifinal = $merged_result_for_edit;
        }

        $participant->save();
    }
}
