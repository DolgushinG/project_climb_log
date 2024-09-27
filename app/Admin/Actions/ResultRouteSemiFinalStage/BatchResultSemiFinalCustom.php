<?php

namespace App\Admin\Actions\ResultRouteSemiFinalStage;

use App\Admin\Extensions\CustomAction;
use App\Helpers\Helpers;
use App\Models\Event;
use App\Models\ResultQualificationClassic;
use App\Models\ParticipantCategory;
use App\Models\ResultFranceSystemQualification;
use App\Models\ResultRouteFranceSystemQualification;
use App\Models\ResultRouteSemiFinalStage;
use App\Models\ResultSemiFinalStage;
use App\Models\User;
use Encore\Admin\Actions\Action;
use Encore\Admin\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BatchResultSemiFinalCustom extends CustomAction
{
    protected $selector = '.result-add';


    public function __construct()
    {
        $this->initInteractor();
    }
    public function handle(Request $request)
    {
        $results = $request->toArray();
        $event = Event::find($results['event_id']);
        $data = array();
        $result_for_edit = array();
        for($i = 1; $i <= $event->amount_routes_in_semifinal; $i++){
            if(intval($results['amount_try_top_'.$i]) > 0){
                $amount_top  = 1;
            } else {
                $amount_top  = 0;
            }
            if(intval($results['amount_try_zone_'.$i]) > 0){
                $amount_zone  = 1;
            } else {
                $amount_zone  = 0;
            }

            # Если есть ТОП то зона не может быть 0
            if(Helpers::validate_amount_top_and_zone($amount_top, $amount_zone)){
                return $this->response()->error('У трассы '.$i.' отмечен ТОП, и получается зона не может быть 0');
            }

            # Кол-во попыток на зону не может быть меньше чем кол-во на ТОП
            if(Helpers::validate_amount_try_top_and_zone($results['amount_try_top_'.$i], $results['amount_try_zone_'.$i])){
                return $this->response()->error('Кол-во попыток на зону не может быть меньше, чем кол-во попыток на ТОП, трасса '.$i );
            }

            if($event->is_france_system_qualification){
                $participant = ResultFranceSystemQualification::where('event_id', $results['event_id'])->where('user_id', $results['user_id'])->first();
            } else {
                $participant = ResultQualificationClassic::where('event_id', $results['event_id'])->where('user_id', $results['user_id'])->first();
            }
            if(!$participant){
                Log::error('Category id not found -event_id - '.$results['event_id'].'user_id'.$results['user_id']);
            }
            if($event->is_open_main_rating && $event->is_auto_categories){
                $category_id = $participant->global_category_id;
            } else {
                $category_id = $participant->category_id;
            }
            $gender = $participant->gender;
            $owner_id = \Encore\Admin\Facades\Admin::user()->id;
            $data[] = array('owner_id' => $owner_id,
                'user_id' => intval($results['user_id']),
                'event_id' => intval($results['event_id']),
                'final_route_id' => intval($results['final_route_id_'.$i]),
                'category_id' => $category_id,
                'amount_top' => $amount_top,
                'all_attempts' =>  Helpers::find_max_attempts(intval($results['amount_try_top_'.$i]), intval($results['amount_try_zone_'.$i])),
                'gender' => $gender,
                'amount_try_top' => intval($results['amount_try_top_'.$i]),
                'amount_zone' => $amount_zone,
                'amount_try_zone' => intval($results['amount_try_zone_'.$i]),
                );
            $result_for_edit[] = array(
                'Номер маршрута' => intval($results['final_route_id_'.$i]),
                'Попытки на топ' => intval($results['amount_try_top_'.$i]),
                'Попытки на зону' => intval($results['amount_try_zone_'.$i])
            );
        }
        $result = ResultRouteSemiFinalStage::where('event_id', $results['event_id']
        )->where('user_id', $results['user_id'])->first();
        $user = User::find(intval($results['user_id']))->middlename;
        if($result) {
            return $this->response()->error('Результат уже есть по ' . $user);
        }
        DB::table('result_route_semifinal_stage')->insert($data);

        Event::send_result_semifinal(intval($results['event_id']), $owner_id, intval($results['user_id']), $category_id, $result_for_edit, $gender);

        Event::refresh_final_points_all_participant_in_semifinal($event->id);
        return $this->response()->success('Результат успешно внесен')->refresh();
    }

    public function custom_form()
    {
        $this->modalSmall();
        $event = Event::where('owner_id', '=', \Encore\Admin\Facades\Admin::user()->id)
            ->where('active', '=', 1)->first();
        if($event->is_open_main_rating){
            $merged_users = ResultSemiFinalStage::get_global_participant_semifinal($event);
        } else {
            $merged_users = ResultSemiFinalStage::get_participant_semifinal($event);
        }
        $result = $merged_users->pluck( 'middlename','id');
        $result_semifinal = ResultRouteSemiFinalStage::where('event_id', '=', $event->id)->select('user_id')->distinct()->pluck('user_id')->toArray();
        $categories = ParticipantCategory::where('event_id', '=', $event->id)->pluck('category', 'id')->toArray();
        foreach ($result as $user_id => $middlename){
            $result[$user_id] = $middlename;
            if(in_array($user_id, $result_semifinal)){
                $result[$user_id] = $middlename.' [Уже добавлен]';
            }
        }
        $result = $result->toArray();
        asort($result);

        $this->select('category_id', 'Категория')->attribute('autocomplete', 'off')->attribute('data-semifinal-custom-category-id', 'category_id')->options($categories)->required();
        $this->select('user_id', 'Участник')->attribute('autocomplete', 'off')->attribute('data-semifinal-custom-user-id', 'user_id')->options($result)->required();
        $this->hidden('event_id', '')->attribute('autocomplete', 'off')->attribute('data-semifinal-custom-event-id', 'event_id')->value($event->id);
        for($i = 1; $i <= $event->amount_routes_in_semifinal; $i++){
            $this->integer('final_route_id_'.$i, 'Трасса')->value($i)->readOnly();
            $this->integer('amount_try_top_'.$i, 'Попытки на топ')->attribute('autocomplete', 'off');
            $this->integer('amount_try_zone_'.$i, 'Попытки на зону')->attribute('autocomplete', 'off');
        }
        $script = <<<EOT

           $(document).on('change', '[data-final-custom-user-id=user_id]', function () {
                        var amountRoutesInFinal = $event->amount_routes_in_final;
                        for (var i = 1; i <= amountRoutesInFinal; i++) {
                            $('#amount_try_top_' + i).val('');
                            $('#amount_try_zone_' + i).val('');
                        }
                });
            $(document).on("change", '[data-semifinal-custom-category-id=category_id]', function () {
                var amountRoutesInFinal = $event->amount_routes_in_final;
                for (var i = 1; i <= amountRoutesInFinal; i++) {
                    $('#amount_try_top_' + i).val('');
                    $('#amount_try_zone_' + i).val('');
                }
                var categoryId = $('[data-semifinal-custom-category-id=category_id]').select2('val')
                var eventId = $('[data-semifinal-custom-event-id=event_id]').val(); // ID выбранного участника
                $('[data-semifinal-custom-user-id=user_id]').val('');
                $.get("/admin/api/get_users",
                    {eventId: eventId, categoryId: categoryId, stage: 'semifinal'},
                    function (data) {
                        var model = $('[data-semifinal-custom-user-id=user_id]');
                        model.empty();
                        model.append("<option>Выбрать</option>");
                        var sortedData = Object.entries(data).sort(function (a, b) {
                            return a[1].localeCompare(b[1]); // сортируем по значению (имя пользователя)
                        });
                        $.each(sortedData, function (i, item) {
                            var userId = item[0];
                            var userName = item[1];
                            model.append("<option data-semifinal-custom-user-id='" + userId + "' value='" + userId + "'>" + userName + "</option>");
                        });
                    }
                );

            });
            let btn_close_icon_modal_custom_semifinal = '[id="app-admin-actions-resultroutesemifinalstage-batchresultsemifinalcustom"] [data-dismiss="modal"][class="close"]'
                $(document).on("click", btn_close_icon_modal_custom_semifinal, function () {
                    window.location.reload();
                });
            let btn_close_modal_custom_semifinal = '[id="app-admin-actions-resultroutesemifinalstage-batchresultsemifinalcustom"] [data-dismiss="modal"][class="btn btn-default"]'
            $(document).on("click", btn_close_modal_custom_semifinal, function () {
                window.location.reload();
            });
        EOT;
        \Encore\Admin\Facades\Admin::script($script);

    }

    public function html()
    {
        $event = Event::where('owner_id', '=', \Encore\Admin\Facades\Admin::user()->id)
            ->where('active', '=', 1)->first();
        if($event->is_semifinal && $event->amount_the_best_participant > 0){
            return "<a class='result-add btn btn-sm btn-primary'><i class='fa fa-plus-circle'></i> Все трассы </a>
                 <style>
                 .result-add {margin-top:8px;}
                 @media screen and (max-width: 767px) {
                        .result-add {margin-top:8px;}
                    }
                </style>
            ";
        } else {
            return "<a disabled class='result-add btn btn-sm btn-warning' style='display: none'><i class='fa fa-info-circle'></i></a>";
        }
    }

}
