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

    private static function counting_result($event_id, $route_id)
    {
        return count(ResultParticipant::where('event_id', '=', $event_id)
            ->where('route_id', '=', $route_id)
            ->whereNotIn('attempt',[0])
            ->get()
            ->toArray());
    }

    public static function get_coefficient($event_id, $route_id, $gender){
        $active_participant = Participant::participant_with_result($event_id, $gender);
        $count_route_passed = self::counting_result($event_id, $route_id);
        if ($count_route_passed == 0) {
            $count_route_passed = 1;
        }
        return sqrt($active_participant / $count_route_passed );
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

    public static function participant_with_result($user_id, $event_id) {
        return 0 < count(ResultParticipant::where('event_id', '=', $event_id)
            ->where('user_id', '=', $user_id)
            ->get()
            ->toArray());

    }
}
