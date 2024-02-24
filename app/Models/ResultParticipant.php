<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;



class ResultParticipant extends Model
{
    const FLASH = 1;
    const REDPOINT = 0.9;
    const FAIL = 0;
    const FLASH_VALUE = 10;
    const REDPOINT_VALUE = 0;

    const POINT_VALUES = array(0 => self::FAIL, 1 => self::FLASH_VALUE, 2 => self::REDPOINT_VALUE);

    var $values = array(0 => self::FAIL, 1 => self::FLASH, 2 => self::REDPOINT);


    protected $table = 'result_participant';

    private static function counting_result($event_id, $route_id, $gender)
    {
        $users = User::query()
            ->leftJoin('participants', 'users.id', '=', 'participants.user_id')
            ->where('participants.event_id', '=', $event_id)
            ->where('participants.active', '=', 1)
            ->where('gender', '=', $gender)
            ->select(
                'users.id',
            )
            ->pluck('id');
        return count(ResultParticipant::whereIn('user_id', $users)
            ->where('route_id', '=', $route_id)
            ->whereNotIn('attempt',[0])
            ->get()
            ->toArray());
    }

    public static function get_coefficient($event_id, $route_id, $gender){
        $active_participant = Participant::participant_with_result($event_id, $gender);
        $count_route_passed = self::counting_result($event_id, $route_id, $gender);
        if ($count_route_passed == 0) {
            $count_route_passed = 1;
        }
        return sqrt($active_participant / $count_route_passed);
    }
    public function get_value_route($attempt, $value_category, $format) {
        switch ($format) {
            # 10 лучших
            case 1:
                return $value_category + self::POINT_VALUES[$attempt];
            # Все трассы
            case 2:
                return $this->values[$attempt];
        }
    }

    public static function get_increase_category($user_model, $format_transfer_category){

        $categories_for_increase = array();
        $categories = ['6A+','6B','6B+','6C', '6C+', '7A', '7A+', '7B', '7C', '7C+', '8A', '8A+', '8B+', '8C', '8C+', '9A'];
        for($i = array_search($format_transfer_category["От какой категории будет перевод"], $categories); $i < count($categories);$i++){
            $categories_for_increase[] = $categories[$i];
        }
//        ['Категория участника' => '0', 'Кол-во трасс для перевода'=> '2','В какую категорию переводить' => '1', 'От какой категории будет перевод'=> '6C'],

        switch ($format_transfer_category["Категория участника"]){
                case "1":
//                    if($user_model->grade == "6C"){
//                        if($user_model->attempt == 1 || $user_model->attempt == 2){
//                            dd(11);
//                        }
//                    }
                    if(in_array($user_model->grade, $categories_for_increase)){

                        if($user_model->attempt == 1 || $user_model->attempt == 2){

                            return array('user_id' => $user_model->user_id, 'increase_category' => true, 'next_category' => "2");
                        } else {
                            return null;
                        }

                        }
                    break;
                case "2":
//                    if($user_model->grade == "6C"){
//                        if($user_model->attempt == 1 || $user_model->attempt == 2){
//                            dd($user_model);
//                        }
//                    }
                    if(in_array($user_model->grade, $categories_for_increase)){

                        if($user_model->attempt == 1 || $user_model->attempt == 2){
                            return array('user_id' => $user_model->user_id, 'increase_category' => true, 'next_category' => "3");
                        } else {
                            return null;
                        }
                    }
            }
    }

    public static function participant_with_result($user_id, $event_id) {
        return 0 < count(ResultParticipant::where('event_id', '=', $event_id)
            ->where('user_id', '=', $user_id)
            ->get()
            ->toArray());

    }
}
