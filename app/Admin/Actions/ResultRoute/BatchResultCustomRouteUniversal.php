<?php

namespace App\Admin\Actions\ResultRoute;

use App\Admin\Extensions\CustomAction;
use App\Helpers\Helpers;
use App\Models\Event;
use App\Models\Grades;
use App\Models\ResultQualificationClassic;
use App\Models\ParticipantCategory;
use App\Models\ResultFinalStage;
use App\Models\ResultFranceSystemQualification;
use App\Models\ResultRouteFinalStage;
use App\Models\ResultRouteFranceSystemQualification;
use App\Models\ResultRouteSemiFinalStage;
use App\Models\ResultSemiFinalStage;
use App\Models\User;
use Encore\Admin\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BatchResultCustomRouteUniversal extends CustomAction
{
    private string $stage;
    protected $selector = '.result-custom-universal';

    public function __construct($stage = '')
    {
        $this->initInteractor();
        $this->stage = $stage;
    }

    public function handle(Request $request)
    {
        $results = $request->toArray();
        $event = Event::find($results['event_id']);
        $stage = $results['stage'];

        $amount_routes = BatchBaseRoute::get_amount_routes($event, $stage);
        $modelClass = BatchBaseRoute::get_models($event);
        $resultModelClass = $modelClass::where('event_id', $results['event_id'])->where('user_id', $results['user_id'])->first();
        if(!$resultModelClass){
            return $this->response()->error('Не выбран участник');
        }
        $data = array();
        $result_for_edit = [];
        for($i = 1; $i <= $amount_routes; $i++){
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
            $validate = BatchBaseRoute::validate(
                route_id: $i,
                amount_try_top: intval($results['amount_try_top_'.$i]),
                amount_try_zone: intval($results['amount_try_zone_'.$i]),
                amount_top: $amount_top,
                amount_zone: $amount_zone
            );
            if($validate){
                return $this->response()->error($validate);
            }
            $data[] = array('owner_id' => \Encore\Admin\Facades\Admin::user()->id,
                'user_id' => intval($results['user_id']),
                'category_id' => $resultModelClass->category_id,
                'event_id' => intval($results['event_id']),
                BatchBaseRoute::ROUTE[$stage] => intval($results[BatchBaseRoute::ROUTE[$stage].'_'.$i]),
                'gender' => $resultModelClass->gender,
                'all_attempts' =>  Helpers::find_max_attempts(intval($results['amount_try_top_'.$i]), intval($results['amount_try_zone_'.$i])),
                'amount_top' => $amount_top,
                'amount_try_top' => intval($results['amount_try_top_'.$i]),
                'amount_zone' => $amount_zone,
                'amount_try_zone' => intval($results['amount_try_zone_'.$i]),
            );
            $result_for_edit[] = array(
                'Номер маршрута' => intval($results[BatchBaseRoute::ROUTE[$stage].'_'.$i]),
                'Попытки на топ' => intval($results['amount_try_top_'.$i]),
                'Попытки на зону' => intval($results['amount_try_zone_'.$i])
            );
        }
        $model = BatchBaseRoute::MODELS[$stage];
        $participant = $model::where('event_id', $results['event_id'])->where('user_id', $results['user_id'])->first();
        if($stage == 'qualification'){
            $result = ResultRouteFranceSystemQualification::where('event_id', $results['event_id'])->where('user_id', $results['user_id'])->first();
            if(!$result){
                $participant->active = 1;
            }
            $participant->result_for_edit_france_system_qualification = $result_for_edit;
            $participant->save();
            DB::table('result_route_france_system_qualification')->insert($data);
            Event::refresh_france_system_qualification_counting($event);
        }
        if($stage == 'final'){
            $user = User::find(intval($results['user_id']))->middlename;
            if($participant) {
                return $this->response()->error('Результат уже есть по ' . $user);
            }
            DB::table('result_route_final_stage')->insert($data);
            Event::send_result_final(intval($results['event_id']), $event->owner_id, intval($results['user_id']), $resultModelClass->category_id, $result_for_edit, $resultModelClass->gender);
            Event::refresh_final_points_all_participant_in_final($event->id);
        }
        if($stage == 'semifinal'){
            $user = User::find(intval($results['user_id']))->middlename;
            if($participant) {
                return $this->response()->error('Результат уже есть по ' . $user);
            }
            DB::table('result_route_semifinal_stage')->insert($data);
            Event::send_result_semifinal(intval($results['event_id']), $event->owner_id, intval($results['user_id']), $resultModelClass->category_id, $result_for_edit, $resultModelClass->gender);
            Event::refresh_final_points_all_participant_in_semifinal($event->id);
        }

        return $this->response()->success('Результат успешно внесен')->refresh();
    }

    public function custom_form()
    {
        $this->modalSmall();
        $event = Event::where('owner_id', '=', \Encore\Admin\Facades\Admin::user()->id)
            ->where('active', '=', 1)->first();
        $amount_routes = BatchBaseRoute::get_amount_routes($event, $this->stage);
        $categories = ParticipantCategory::where('event_id', $event->id)->pluck('category', 'id')->toArray();
        $result = BatchBaseRoute::merged_users($event, $this->stage);
        $this->select('category_id', 'Группа')->attribute('autocomplete', 'off')->attribute('data-custom-category-id', 'category_id')->options($categories);
        $this->select('user_id', 'Участник')->attribute('autocomplete', 'off')->attribute('data-custom-user-id', 'user_id')->options($result)->required();
        $this->hidden('stage', '')->attribute('autocomplete', 'off')->value($this->stage);
        $this->hidden('event_id', '')->attribute('autocomplete', 'off')->attribute('data-custom-event-id', 'event_id')->value($event->id);
        $this->text('user_gender', 'Пол')->attribute('autocomplete', 'off')->readonly();
        $this->text('user_category', 'Группа')->attribute('autocomplete', 'off')->readonly();
        for($i = 1; $i <= $amount_routes; $i++){
            $this->integer(BatchBaseRoute::ROUTE[$this->stage].'_'.$i, 'Трасса')->value($i)->readOnly();
            $this->integer('amount_try_top_'.$i, 'Попытки на топ')->attribute('autocomplete', 'off');
            $this->integer('amount_try_zone_'.$i, 'Попытки на зону')->attribute('autocomplete', 'off');
        }
        $script_custom = <<<EOT
                        $(document).on('change', '[data-custom-user-id=user_id]', function () {
                            var userId = $('[data-custom-user-id=user_id]').select2('val')
                            var eventId = $('[data-custom-event-id]').val();
                            var amountRoutesInFinal = $amount_routes;
                            for (var i = 1; i <= amountRoutesInFinal; i++) {
                                $('#amount_try_top_' + i).val('');
                                $('#amount_try_zone_' + i).val('');
                            }
                            if(userId){
                                    $.get("/admin/api/get_user_info", // URL эндпоинта
                                        {
                                            user_id: userId,
                                            event_id: eventId
                                        },
                                        function (data) {
                                            $('[id="user_gender"]').val(data.gender);
                                            $('[id="user_category"]').val(data.category);
                                        }
                                    );
                            }
                        });
                        $(document).on("change", '[data-custom-category-id=category_id]', function () {
                            var amountRoutesInFinal = $amount_routes;
                            for (var i = 1; i <= amountRoutesInFinal; i++) {
                                $('#amount_try_top_' + i).val('');
                                $('#amount_try_zone_' + i).val('');
                            }
                            var categoryId = $('[data-custom-category-id=category_id]').select2('val')
                            var eventId = $('[data-custom-event-id=event_id]').val(); // ID выбранного участника
                            $('[data-custom-user-id=user_id]').val('');
                            $.get("/admin/api/get_users",
                                {eventId: eventId, categoryId: categoryId, stage: '$this->stage'},
                                function (data) {
                                    var model = $('[data-custom-user-id=user_id]');
                                    model.empty();
                                    model.append("<option>Выбрать</option>");
                                    var sortedData = Object.entries(data).sort(function (a, b) {
                                        return a[1].localeCompare(b[1]); // сортируем по значению (имя пользователя)
                                    });
                                    $.each(sortedData, function (i, item) {
                                        var userId = item[0];
                                        var userName = item[1];
                                        model.append("<option data-final-custom-user-id='" + userId + "' value='" + userId + "'>" + userName + "</option>");
                                    });
                                }
                            );

                        });
                        let icon_custom = '[id="app-admin-actions-resultroute-batchresultcustomrouteuniversal"] [data-dismiss="modal"][class="close"]'
                            $(document).on("click", icon_custom, function () {
                                window.location.reload();
                            });
                        let btn_close_modal_custom = '[id="app-admin-actions-resultroute-batchresultcustomrouteuniversal"] [data-dismiss="modal"][class="btn btn-default"]'
                        $(document).on("click", btn_close_modal_custom, function () {
                            window.location.reload();
                        });
                    EOT;
        \Encore\Admin\Facades\Admin::script($script_custom);
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
    }

    public function html()
    {
        return "<a class='result-custom-universal btn btn-sm btn-primary'><i class='fa fa-plus-circle'></i> Все трассы</a>
                 <style>
                 .result-custom-universal {margin-top:8px;}
                 @media screen and (max-width: 767px) {
                        .result-custom-universal {margin-top:8px;}
                    }
                </style>
            ";
    }

}



