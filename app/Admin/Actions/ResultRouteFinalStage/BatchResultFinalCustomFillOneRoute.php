<?php

namespace App\Admin\Actions\ResultRouteFinalStage;

use App\Admin\Extensions\CustomAction;
use App\Helpers\Helpers;
use App\Models\Event;
use App\Models\ResultQualificationClassic;
use App\Models\ParticipantCategory;
use App\Models\ResultFinalStage;
use App\Models\ResultFranceSystemQualification;
use App\Models\ResultRouteFinalStage;
use App\Models\ResultRouteFranceSystemQualification;
use App\Models\User;
use Encore\Admin\Actions\Action;
use Encore\Admin\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BatchResultFinalCustomFillOneRoute extends CustomAction
{
    protected $selector = '.result-add-final-one-route';

    public $category;
    private string $script;

    public function __construct(ParticipantCategory $category, string $script = '')
    {
        $this->initInteractor();
        $this->category = $category;
        $this->script = $script;
    }
    public function handle(Request $request)
    {
        $results = $request->toArray();
        $amount_try_top = intval($results['amount_try_top']);
        $amount_try_zone = intval($results['amount_try_zone']);
        $all_attempts = intval($results['all_attempts']);
        $event_id = intval($results['event_id']);
        $user_id = intval($results['user_id']);
        $event = Event::find($event_id);
        $final_route_id = intval($results['final_route_id']);
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
        if($final_route_id == 0){
            return $this->response()->error('Вы не выбрали номер маршрута');
        }
        $max_attempts = Helpers::find_max_attempts($amount_try_top, $amount_try_zone);
        if(Helpers::validate_amount_sum_top_and_zone_and_attempts($all_attempts, $amount_try_top, $amount_try_zone)){
            return $this->response()->error(
                'У трассы '.$final_route_id.' Максимальное кол-во попыток '.$max_attempts.' а в поле все попытки - '. $all_attempts);
        }
        # Если есть ТОП то зона не может быть 0
        if(Helpers::validate_amount_top_and_zone($amount_top, $amount_zone)){
            return $this->response()->error('У трассы '.$final_route_id.' отмечен ТОП, и получается зона не может быть 0');
        }

        # Кол-во попыток на зону не может быть меньше чем кол-во на ТОП
        if(Helpers::validate_amount_try_top_and_zone($amount_try_top, $amount_try_zone)){
            return $this->response()->error('Кол-во попыток на зону не может быть меньше, чем кол-во попыток на ТОП, трасса '.$final_route_id );
        }

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

        ResultRouteFinalStage::update_semi_or_final_route_results(
            stage: 'final',
            owner_id: $owner_id,
            event_id: $event_id,
            category_id: $category_id,
            route_id: $final_route_id,
            user_id: $user_id,
            amount_try_top: $amount_try_top,
            amount_try_zone: $amount_try_zone,
            amount_top: $amount_top,
            amount_zone: $amount_zone,
            gender: $gender,
            all_attempts: $all_attempts,
        );
        Event::refresh_final_points_all_participant_in_final($event_id);
        return $this->response()->success('Результат успешно внесен')->refresh();
    }

    public function custom_form()
    {
        $this->modalSmall();
        $event = Event::where('owner_id', '=', \Encore\Admin\Facades\Admin::user()->id)
            ->where('active', '=', 1)->first();
        if($event->is_open_main_rating){
            $merged_users = ResultFinalStage::get_final_global_participant($event, $this->category);
        } else {
            $merged_users = ResultFinalStage::get_final_participant($event, $this->category);
        }
        $result = $merged_users->pluck( 'middlename','id');
        $result_final = ResultRouteFinalStage::where('event_id', '=', $event->id)->select('user_id')->distinct()->pluck('user_id')->toArray();
        foreach ($result as $user_id => $middlename){
            if($event->is_france_system_qualification) {
                $category_id = ResultFranceSystemQualification::where('event_id', '=', $event->id)->where('user_id', '=', $user_id)->first()->category_id;
            } else {
                if($event->is_open_main_rating && $event->is_auto_categories){
                    $category_id = ResultQualificationClassic::where('event_id', $event->id)->where('user_id', $user_id)->first()->global_category_id;
                } else {
                    $category_id = ResultQualificationClassic::where('event_id', $event->id)->where('user_id', $user_id)->where('active', 1)->first()->category_id;
                }
            }
            $category = ParticipantCategory::find($category_id)->category;
            $result[$user_id] = $middlename.' ['.$category.']';
            if(in_array($user_id, $result_final)){
                $result_user = ResultRouteFinalStage::where('event_id', $event->id)->where('user_id', $user_id);
                $routes = $result_user->get()->sortBy('final_route_id')->pluck('final_route_id')->toArray();
                $string_version = '';
                foreach ($routes as $value) {
                    $string_version .= $value . ', ';
                }
                if($result_user->get()->count() == $event->amount_routes_in_final){
                    $result[$user_id] = $middlename.' ['.$category.']'.' [Добавлены все трассы]';
                } else {
                    $result[$user_id] = $middlename.' ['.$category.']'.' [Трассы: '.$string_version.']';
                }
            }
        }
        $routes = [];
        for($i = 1; $i <= $event->amount_routes_in_final; $i++){
            $routes[$i] = $i;
        }
        $this->select('user_id', 'Участник')->attribute('autocomplete', 'off')->attribute('data-final-user-id-'.$this->category->id, 'user_id')->options($result)->required();
        $this->hidden('event_id', '')->attribute('autocomplete', 'off')->attribute('data-final-event-id-'.$this->category->id, 'event_id')->value($event->id);
        $this->select('final_route_id', 'Трасса')->attribute('autocomplete', 'off')->attribute('data-final-route-id-'.$this->category->id, 'final_route_id')->options($routes)->required();
        $this->integer('all_attempts', 'Все попытки')
            ->attribute('autocomplete', 'off')
            ->attribute('id', 'all_attempts-'.$this->category->id)
            ->attribute('data-all-attempts-id-'.$this->category->id, 'all-attempts');
        $this->integer('amount_try_zone', 'Попытки на зону')->attribute('id', 'amount_try_zone_'.$this->category->id)->attribute('data-amount-try-zone-'.$this->category->id, 'amount_try_zone');
        $this->integer('amount_try_top', 'Попытки на топ')->attribute('id', 'amount_try_top_'.$this->category->id)->attribute('data-amount-try-top-'.$this->category->id, 'amount_try_top');
        $script = <<<EOT
                        const elementsWithModalAttribute = document.querySelectorAll('[modal="app-admin-actions-resultroutefinalstage-batchresultfinalcustomfilloneroute"]');
                        const elementsWithIdAttribute = document.querySelectorAll('[id="app-admin-actions-resultroutefinalstage-batchresultfinalcustomfilloneroute"]');
                        const modalCounters= {};
                        const idCounters = {};

                        // Перебираем найденные элементы
                        elementsWithModalAttribute.forEach(element => {
                            const modalValue = element.getAttribute('modal');
                            if (modalValue in modalCounters) {
                                modalCounters[modalValue]++;
                            } else {
                                modalCounters[modalValue] = 1;
                            }
                            const elementNumber = modalCounters[modalValue];
                            element.setAttribute('modal', modalValue + '-' + elementNumber);
                        });
                        elementsWithIdAttribute.forEach(element => {
                            const idValue = element.getAttribute('id');
                            if (idValue in idCounters) {
                                idCounters[idValue]++;
                            } else {
                                idCounters[idValue] = 1;
                            }
                            const elementNumber = idCounters[idValue];
                            element.setAttribute('id', idValue + '-' + elementNumber);
                        });

                    EOT;
        Admin::script($script);
        Admin::style('
            .input-group {
                display: flex;
                align-items: center;
            }

            #increment-btn {
                font-size: 20px;
            }

            #zone-btn {
                font-size: 20px;
            }

            #top-btn {
                font-size: 20px;
            }

            .form-control {
                margin-right: -1px; /* Небольшой выступ для кнопки */
            }

            .input-group-append {
                margin-top: 10px;
                margin-left: 5px; /* Убираем отступ слева */
            }

            .btn-warning {
                margin-left: 5px;
            }

        ');
        \Encore\Admin\Facades\Admin::script($this->script);
    }

    public function html()
    {
        $event = Event::where('owner_id', '=', \Encore\Admin\Facades\Admin::user()->id)->where('active', 1)->first();
        if($event->amount_the_best_participant_to_go_final > 0){
            return "<a class='result-add-final-one-route btn btn-sm btn-primary'><i class='fa fa-plus-circle'></i> {$this->category->category} по одной трассе</a>
                 <style>
                 .result-add-final-one-route {margin-top:8px;}
                 @media screen and (max-width: 767px) {
                        .result-add-final-one-route {margin-top:8px;}
                    }
                </style>
            ";
        } else {
            return "<a href='#' class='result-add-final-one-route btn btn-sm btn-primary' disabled>Кол-во участников в финал 0</a>
                 <style>
                 .result-add-final-one-route {margin-top:8px;}
                 @media screen and (max-width: 767px) {
                        .result-add-final-one-route {margin-top:8px;}
                    }
                </style>
            ";
        }

    }
}
