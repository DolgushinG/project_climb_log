<?php

namespace App\Admin\Actions\ResultRouteFranceSystemQualificationStage;

use App\Helpers\Helpers;
use App\Models\Event;
use App\Models\Grades;
use App\Models\ParticipantCategory;
use App\Models\ResultFinalStage;
use App\Models\ResultFranceSystemQualification;
use App\Models\ResultRouteFranceSystemQualification;
use App\Models\ResultSemiFinalStage;
use App\Models\User;
use Encore\Admin\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Admin\Extensions\CustomAction;
use Illuminate\Support\Facades\Log;
use function Symfony\Component\String\s;

class BatchResultQualificationFranceCustomFillOneRouteAndOneCategory extends CustomAction
{
    protected $selector = '.result-add-qualification-france-one-route-one-category';

    private string $script;

    public function __construct(string $script = 'значение_по_умолчанию')
    {
        $this->initInteractor();
        $this->script = $script;
    }
    public function handle(Request $request)
    {
        $results = $request->toArray();
        $amount_try_top = intval($results['amount_try_top_category']);
        $amount_try_zone = intval($results['amount_try_zone_category']);
        $all_attempts = intval($results['all_attempts']);
        $event_id = intval($results['event_id']);
        $event = Event::find($results['event_id']);
        $route_id = $results['route_id'];
        $user_id = $results['user_id'];
        if($amount_try_top > 0){
            $amount_top  = 1;
        } else {
            $amount_top  = 0;
        }
        if($amount_try_zone > 0){
            $amount_zone  = 1;
        } else {
            $amount_zone  = 0;
        }
        $max_attempts = Helpers::find_max_attempts($amount_try_top, $amount_try_zone);
        if(Helpers::validate_amount_sum_top_and_zone_and_attempts($all_attempts, $amount_try_top, $amount_try_zone)){
            return $this->response()->error(
                'У трассы '.$route_id.' Максимальное кол-во попыток '.$max_attempts.' а в поле все попытки - '. $all_attempts);
        }

        # Если есть ТОП то зона не может быть 0
        if(Helpers::validate_amount_top_and_zone($amount_top, $amount_zone)){
            return $this->response()->error('У трассы '.$route_id.' отмечен ТОП, и получается зона не может быть 0');
        }

        # Кол-во попыток на зону не может быть меньше чем кол-во на ТОП
        if(Helpers::validate_amount_try_top_and_zone($amount_try_top, $amount_try_zone)){
            return $this->response()->error('Кол-во попыток на зону не может быть меньше, чем кол-во попыток на ТОП, трасса '.$route_id );
        }

        $participant = ResultFranceSystemQualification::where('event_id', $results['event_id'])->where('user_id', $user_id)->first();
        if($event->is_open_main_rating){
            $category_id = $participant->global_category_id;
        } else {
            $category_id = $participant->category_id;
        }
        $number_set_id = $participant->number_set_id;
        $gender = $participant->gender;
        $owner_id = \Encore\Admin\Facades\Admin::user()->id;

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

        Event::refresh_france_system_qualification_counting($event);
        return $this->response()->success('Результат успешно внесен')->refresh();
    }


    public function custom_form()
    {
        $this->modalSmall();
        $event = Event::where('owner_id', '=', \Encore\Admin\Facades\Admin::user()->id)
            ->where('active', '=', 1)->first();
        $amount_routes = Grades::where('event_id', $event->id)->first();
        if($amount_routes){
            $amount_routes = $amount_routes->count_routes;
        } else {
            $amount_routes = 0;
        }
        $routes = [];
        for($i = 1; $i <= $amount_routes; $i++){
            $routes[$i] = $i;
        }
        $this->select('user_id', 'Участник')->attribute('autocomplete', 'off')->attribute('data-category-user-id', 'user_id')->required();
        $this->hidden('event_id', '')->attribute('autocomplete', 'off')->attribute('data-category-event-id', 'event_id')->value($event->id);
        $this->select('route_id', 'Трасса')->attribute('autocomplete', 'off')->attribute('data-category-route-id', 'route_id')->options($routes)->required();
        $this->integer('all_attempts', 'Все попытки')
            ->attribute('autocomplete', 'off')
            ->attribute('data-all-attempts-id', 'all-attempts');
        $this->integer('amount_try_top_category', 'Попытки на топ')->attribute('autocomplete', 'off');
        $this->integer('amount_try_zone_category', 'Попытки на зону')->attribute('autocomplete', 'off');
        Admin::style('
                    .input-group {
                    display: flex;
                    align-items: center;
                }

                .form-control {
                    margin-right: -1px; /* Небольшой выступ для кнопки */
                }

                .input-group-append {
                    margin-top: 10px;
                    margin-left: 5px; /* Убираем отступ слева */
                }

                .btn-outline-secondary {
                    background-color: #28a745; /* Зеленый фон */
                    border-color: #28a745; /* Цвет границы совпадает с фоном */
                    color: #fff; /* Белый цвет текста */
                     margin-left: 5px;
                }

                .btn-outline-secondary:hover {
                    background-color: #218838; /* Темнее зеленый при наведении */
                    border-color: #1e7e34; /* Темнее граница при наведении */
                     margin-left: 5px;
                }

        ');
        \Encore\Admin\Facades\Admin::script($this->script);
    }
    public function html()
    {
       return "<a class='result-add-qualification-france-one-route-one-category btn btn-sm btn-warning'><i class='fa fa-plus-circle'></i> Все участники по одной трассе </a>
                 <style>
                 .result-add-qualification-france-one-route-one-category {margin-top:8px;}
                 @media screen and (max-width: 767px) {
                        .result-add-qualification-france-one-route-one-category {margin-top:8px;}
                    }
                </style>
            ";
    }

}
