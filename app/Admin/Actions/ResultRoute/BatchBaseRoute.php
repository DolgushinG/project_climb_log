<?php
namespace App\Admin\Actions\ResultRoute;


use App\Helpers\Helpers;
use App\Models\Event;
use App\Models\Grades;
use App\Models\ResultFinalStage;
use App\Models\ResultFranceSystemQualification;
use App\Models\ResultQualificationClassic;
use App\Models\ResultRouteFinalStage;
use App\Models\ResultRouteFranceSystemQualification;
use App\Models\ResultRouteSemiFinalStage;
use App\Models\ResultSemiFinalStage;
use App\Models\User;

class BatchBaseRoute
{
    const URLS_SET_ATTEMPTS = [
        'final' => '/admin/api/final/set_attempts',
        'semifinal' => '/admin/api/semifinal/set_attempts',
        'qualification' => '/admin/api/set_attempts'
    ];
    const MODELS_ROUTE = [
        'final' => ResultRouteFinalStage::class,
        'semifinal' => ResultRouteSemiFinalStage::class,
        'qualification' => ResultRouteFranceSystemQualification::class,
    ];
    const MODELS = [
        'final' => ResultFinalStage::class,
        'semifinal' => ResultSemiFinalStage::class,
        'qualification' => ResultFranceSystemQualification::class,
    ];
    const URLS_GET_ATTEMPTS = [
        'final' => '/admin/api/final/get_attempts',
        'semifinal' => '/admin/api/semifinal/get_attempts',
        'qualification' => '/admin/api/get_attempts'
    ];
    const ROUTE = [
        'final' => 'final_route_id',
        'semifinal' => 'final_route_id',
        'qualification' => 'route_id',
    ];

    public static function handle($event, $stage, $user_id, $route_id, $amount_try_top, $amount_try_zone, $amount_top, $amount_zone, $all_attempts)
    {
        $event_id = $event->id;
        if($event->is_france_system_qualification){
            $participant = ResultFranceSystemQualification::where('event_id', $event_id)->where('user_id', $user_id)->first();
        } else {
            $participant = ResultQualificationClassic::where('event_id', $event_id)->where('user_id', $user_id)->first();
        }
        if($event->is_open_main_rating && $event->is_auto_categories){
            $category_id = $participant->global_category_id;
        } else {
            $category_id = $participant->category_id;
        }
        $gender = $participant->gender;
        $owner_id = \Encore\Admin\Facades\Admin::user()->id;
        if($stage == 'final' || $stage == 'semifinal'){
            ResultRouteFinalStage::update_semi_or_final_route_results(
                stage: $stage,
                owner_id: $owner_id,
                event_id: $event_id,
                category_id: $category_id,
                route_id: $route_id,
                user_id: $user_id,
                amount_try_top: $amount_try_top,
                amount_try_zone: $amount_try_zone,
                amount_top: $amount_top,
                amount_zone: $amount_zone,
                gender: $gender,
                all_attempts: $all_attempts,
            );
        }
        if($stage == 'qualification'){
            $number_set_id = $participant->number_set_id;
            ResultFranceSystemQualification::update_france_route_results(
                owner_id: $owner_id,
                event_id: $event_id,
                category_id: $category_id,
                route_id: $route_id,
                user_id: $user_id,
                amount_try_top: $amount_try_top,
                amount_try_zone: $amount_try_zone,
                amount_top: $amount_top,
                amount_zone: $amount_zone,
                gender: $gender,
                all_attempts: $all_attempts,
                number_set_id: $number_set_id
            );
        }

        switch ($stage){
            case 'final':
                Event::refresh_final_points_all_participant_in_final($event_id);
                break;
            case 'semifinal':
                Event::refresh_final_points_all_participant_in_semifinal($event_id);
                break;
            case 'qualification':
                Event::refresh_france_system_qualification_counting($event);
                break;
        }
    }

    public static function get_amount_routes($event, $stage)
    {
        switch ($stage){
            case 'final':
                return $event->amount_routes_in_final;
            case 'semifinal':
                return $event->amount_routes_in_semifinal;
            case 'qualification':
                return Grades::where('event_id', $event->id)->first()->count_routes ?? 0;
        }
    }

    public static function merged_users($event, $stage)
    {
        if($event->is_open_main_rating){
            switch ($stage){
                case 'final':
                    $merged_users = ResultFinalStage::get_final_global_participant($event);
                    $result = $merged_users->pluck( 'middlename','id');
                    break;
                case 'semifinal':
                    $merged_users = ResultSemiFinalStage::get_global_participant_semifinal($event);
                    $result = $merged_users->pluck( 'middlename','id');
                    break;
                case 'qualification':
                    $participant_users_id = ResultFranceSystemQualification::where('event_id', $event->id)->pluck('global_user_id')->toArray();
                    $result = User::whereIn('id', $participant_users_id)->pluck('middlename','id');
                    break;
            }
        } else {
            switch ($stage){
                case 'final':
                    $merged_users = ResultFinalStage::get_final_participant($event);
                    $result = $merged_users->pluck( 'middlename','id');
                    break;
                case 'semifinal':
                    $merged_users = ResultSemiFinalStage::get_participant_semifinal($event);
                    $result = $merged_users->pluck( 'middlename','id');
                    break;
                case 'qualification':
                    $participant_users_id = ResultFranceSystemQualification::where('event_id', $event->id)->pluck('user_id')->toArray();
                    $result = User::whereIn('id', $participant_users_id)->pluck('middlename','id');
                    break;
            }
        }

        $modelClass = self::MODELS_ROUTE[$stage];
        $result_final = $modelClass::where('event_id', '=', $event->id)->select('user_id')->distinct()->pluck('user_id')->toArray();

        foreach ($result as $user_id => $middlename){
            $result[$user_id] = $middlename;
            if(in_array($user_id, $result_final)){
                $result_user = $modelClass::where('event_id', $event->id)->where('user_id', $user_id);
                $routes = $result_user->get()->sortBy(self::ROUTE[$stage])->pluck(self::ROUTE[$stage])->toArray();
                $string_version = '';
                foreach ($routes as $value) {
                    $string_version .= $value . ', ';
                }
                if($result_user->get()->count() == self::get_amount_routes($event, $stage)){
                    $result[$user_id] = $middlename.' [Добавлены все трассы]';
                } else {
                    $result[$user_id] = $middlename.' [Трассы: '.$string_version.']';
                }
            }
        }
        $result = $result->toArray();
        asort($result);
        return  $result;
    }

    public static function routes($amount_routes)
    {
        $routes = [];
        for($i = 1; $i <= $amount_routes; $i++){
            $routes[$i] = $i;
        }
        return $routes;
    }

    public static function validate($route_id, $amount_try_top, $amount_try_zone, $amount_top, $amount_zone, $all_attempts = null)
    {
        if($route_id == 0){
            return 'Вы не выбрали номер маршрута';
        }
        if($all_attempts){
            $max_attempts = Helpers::find_max_attempts($amount_try_top, $amount_try_zone);
            if(Helpers::validate_amount_sum_top_and_zone_and_attempts($all_attempts, $amount_try_top, $amount_try_zone)){
                return 'У трассы '.$route_id.' Максимальное кол-во попыток '.$max_attempts.' а в поле все попытки - '. $all_attempts;
            }
        }
        if(Helpers::validate_amount_top_and_zone($amount_top, $amount_zone)){
            return 'У трассы '.$route_id.' отмечен ТОП, и получается зона не может быть 0';
        }
        if(Helpers::validate_amount_try_top_and_zone($amount_try_top, $amount_try_zone)){
            return 'Кол-во попыток на зону не может быть меньше, чем кол-во попыток на ТОП, трасса '.$route_id;
        }
        return false;
    }
    public static function get_models($event)
    {
        if($event->is_france_system_qualification){
            return ResultFranceSystemQualification::class;
        } else {
            return ResultQualificationClassic::class;
        }
    }
}
