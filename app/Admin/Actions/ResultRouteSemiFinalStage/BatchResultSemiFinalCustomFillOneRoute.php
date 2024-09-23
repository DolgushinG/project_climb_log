<?php

namespace App\Admin\Actions\ResultRouteSemiFinalStage;

use App\Admin\Extensions\CustomAction;
use App\Helpers\Helpers;
use App\Models\Event;
use App\Models\ResultQualificationClassic;
use App\Models\ParticipantCategory;
use App\Models\ResultFranceSystemQualification;
use App\Models\ResultRouteFinalStage;
use App\Models\ResultRouteSemiFinalStage;
use App\Models\ResultSemiFinalStage;
use App\Models\User;
use Encore\Admin\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BatchResultSemiFinalCustomFillOneRoute extends CustomAction
{
    protected $selector = '.result-add-one-route';

    public $category;
    private mixed $script;

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
            return $this->response()->error('У трассы '.$results['final_route_id'].' отмечен ТОП, и получается зона не может быть 0');
        }

        # Кол-во попыток на зону не может быть меньше чем кол-во на ТОП
        if(Helpers::validate_amount_try_top_and_zone($results['amount_try_top'], $results['amount_try_zone'])){
            return $this->response()->error('Кол-во попыток на зону не может быть меньше, чем кол-во попыток на ТОП, трасса '.$results['final_route_id'] );
        }

        if($event->is_france_system_qualification){
            $participant = ResultFranceSystemQualification::where('event_id', $results['event_id'])->where('user_id', $results['user_id'])->first();
        } else {
            $participant = ResultQualificationClassic::where('event_id', $results['event_id'])->where('user_id', $results['user_id'])->first();
        }
        if($event->is_open_main_rating && $event->is_auto_categories){
            $category_id = $participant->global_category_id;
        } else {
            $category_id = $participant->category_id;
        }
        $gender = $participant->gender;
        $owner_id = \Encore\Admin\Facades\Admin::user()->id;

        ResultRouteFinalStage::update_semi_or_final_route_results(
            stage: 'semifinal',
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
        Event::refresh_final_points_all_participant_in_semifinal($event->id);
        return $this->response()->success('Результат успешно внесен')->refresh();
    }

    public function custom_form()
    {
        $this->modalSmall();
        $event = Event::where('owner_id', '=', \Encore\Admin\Facades\Admin::user()->id)
            ->where('active', '=', 1)->first();
        $amount_the_best_participant = $event->amount_the_best_participant ?? 10;
        if($event->is_open_main_rating){
            $merged_users = ResultSemiFinalStage::get_global_participant_semifinal($event, $amount_the_best_participant, $this->category);
        } else {
            $merged_users = ResultSemiFinalStage::get_participant_semifinal($event, $amount_the_best_participant, $this->category);
        }
        $result = $merged_users->pluck( 'middlename','id');
        $result_semifinal = ResultRouteSemiFinalStage::where('event_id', '=', $event->id)->select('user_id')->distinct()->pluck('user_id')->toArray();
        foreach ($result as $user_id => $middlename){
            if($event->is_france_system_qualification) {
                $category_id = ResultFranceSystemQualification::where('event_id', '=', $event->id)->where('user_id', '=', $user_id)->first()->category_id;
            } else {
                if($event->is_open_main_rating && $event->is_auto_categories){
                    $category_id = ResultQualificationClassic::where('event_id', $event->id)->where('user_id', $user_id)->first()->global_category_id;
                } else {
                    $category_id = ResultQualificationClassic::where('event_id', $event->id)->where('user_id', $user_id)->first()->category_id;
                }
            }
            $category = ParticipantCategory::find($category_id)->category;
            $result[$user_id] = $middlename.' ['.$category.']';
            if(in_array($user_id, $result_semifinal)){
                $result_user = ResultRouteSemiFinalStage::where('event_id', $event->id)->where('user_id', $user_id);
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
        for($i = 1; $i <= $event->amount_routes_in_semifinal; $i++){
            $routes[$i] = $i;
        }
        $this->select('user_id', 'Участник')->attribute('autocomplete', 'off')->attribute('data-semifinal-user-id-'.$this->category->id, 'user_id')->options($result)->required();
        $this->hidden('event_id', '')->attribute('autocomplete', 'off')->attribute('data-semifinal-event-id-'.$this->category->id, 'event_id')->value($event->id);
        $this->select('final_route_id', 'Трасса')->attribute('autocomplete', 'off')->attribute('data-semifinal-route-id-'.$this->category->id, 'final_route_id')->options($routes)->required();
        $this->integer('all_attempts', 'Все попытки')
            ->attribute('autocomplete', 'off')
            ->attribute('id', 'all_attempts-'.$this->category->id)
            ->attribute('data-all-attempts-id-'.$this->category->id, 'all-attempts');
        $this->integer('amount_try_zone', 'Попытки на зону')->attribute('id', 'amount_try_zone_'.$this->category->id)->attribute('data-amount-try-zone-'.$this->category->id, 'amount_try_zone');
        $this->integer('amount_try_top', 'Попытки на топ')->attribute('id', 'amount_try_top_'.$this->category->id)->attribute('data-amount-try-top-'.$this->category->id, 'amount_try_top');
        \Encore\Admin\Facades\Admin::script($this->script);
        $script = <<<EOT
                        const elementsWithModalAttribute2 = document.querySelectorAll('[modal="app-admin-actions-resultroutesemifinalstage-batchresultsemifinalcustomfilloneroute"]');
                        const elementsWithIdAttribute2 = document.querySelectorAll('[id="app-admin-actions-resultroutesemifinalstage-batchresultsemifinalcustomfilloneroute"]');

                        const modalCounters2 = {};
                        const idCounters2 = {};
                        elementsWithModalAttribute2.forEach(element => {
                            const modalValue2 = element.getAttribute('modal');
                            if (modalValue2 in modalCounters2) {
                                modalCounters2[modalValue2]++;
                            } else {
                                modalCounters2[modalValue2] = 1;
                            }
                            const elementNumber2 = modalCounters2[modalValue2];
                            element.setAttribute('modal', modalValue2 + '-' + elementNumber2);
                        });
                        elementsWithIdAttribute2.forEach(element => {
                            const idValue2 = element.getAttribute('id');
                            if (idValue2 in idCounters2) {
                                idCounters2[idValue2]++;
                            } else {
                                idCounters2[idValue2] = 1;
                            }
                            const elementNumber2 = idCounters2[idValue2];
                            element.setAttribute('id', idValue2 + '-' + elementNumber2);
                        });
                    EOT;
        Admin::script($script);
        Admin::css('/resource_admin/css/add_result.css');
    }

    public function html()
    {
        $event = Event::where('owner_id', '=', \Encore\Admin\Facades\Admin::user()->id)
            ->where('active', '=', 1)->first();
        if($event->is_semifinal && $event->amount_the_best_participant > 0){
            return "<a class='result-add-one-route btn btn-sm btn-primary'><i class='fa fa-plus-circle'></i> {$this->category->category} по одной трассе</a>
            ";
        } else {
            return "<a disabled class='result-add-one-route btn btn-sm btn-warning' style='display: none'><i class='fa fa-info-circle'></i></a>";
        }
    }

}
