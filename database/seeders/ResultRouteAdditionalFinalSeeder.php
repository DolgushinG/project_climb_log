<?php

namespace Database\Seeders;

use App\Models\Participant;
use App\Models\ResultFinalStage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ResultRouteAdditionalFinalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        function prepare_data_result_route_passed_with_owner($owner_id, $event_id)
        {

            $result_female = ResultFinalStage::better_of_participants_final_stage($event_id, 'female', 6)->toArray();
            $result_male = ResultFinalStage::better_of_participants_final_stage($event_id, 'male', 6)->toArray();
            $final_users = array_merge($result_female, $result_male);
            $result = array();
            foreach ($final_users as $user) {
                for ($route = 1; $route <= 5; $route++) {
                    $amount_zone = rand(0, 1);
                    if ($amount_zone) {
                        $amount_try_zone = rand(1, 10);
                    } else {
                        $amount_try_zone = 0;
                    }
                    if ($amount_zone) {
                        $amount_top = rand(0, 1);
                        if ($amount_top) {
                            $amount_try_top = rand(1, 10);
                        } else {
                            $amount_try_top = 0;
                        }
                    } else {
                        $amount_top = 0;
                        $amount_try_top = 0;
                    }
                    $result[] = array('owner_id' => $owner_id, 'event_id' => $event_id, 'user_id' => $user['id'], 'final_route_id' => $route, 'amount_try_top' => $amount_try_top, 'amount_try_zone' => $amount_try_zone, 'amount_top' => $amount_top, 'amount_zone' => $amount_zone);
                }
            }
            DB::table('result_route_additional_final_stage')->insert($result);
        }
        prepare_data_result_route_passed_with_owner(2, 1);
        prepare_data_result_route_passed_with_owner(3, 2);
    }
}
