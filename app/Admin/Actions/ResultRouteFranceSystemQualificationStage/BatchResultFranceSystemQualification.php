<?php

namespace App\Admin\Actions\ResultRouteFranceSystemQualificationStage;

use App\Admin\Extensions\CustomAction;
use App\Helpers\Helpers;
use App\Models\Event;
use App\Models\Grades;
use App\Models\ParticipantCategory;
use App\Models\ResultFranceSystemQualification;
use App\Models\ResultRouteFinalStage;
use App\Models\ResultRouteFranceSystemQualification;
use App\Models\User;
use Encore\Admin\Actions\Action;
use Encore\Admin\Admin;
use Encore\Admin\Form;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BatchResultFranceSystemQualification extends CustomAction
{
    public function __construct()
    {
        $this->initInteractor();
    }
    protected $selector = '.send-add';
    public function handle(Request $request)
    {
        $results = $request->toArray();
        $event = Event::find($results['event_id']);
        $grades = Grades::where('event_id', $event->id)->first();
        $result_qualification_like = ResultFranceSystemQualification::where('event_id', $results['event_id'])->where('user_id', $results['user_id'])->first();
        if(!$result_qualification_like){
            return $this->response()->error('Не выбран участник');
        }
        $data = array();
        $result_for_edit = [];
        for($i = 1; $i <= $grades->count_routes; $i++){
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

            $data[] = array('owner_id' => \Encore\Admin\Facades\Admin::user()->id,
                'user_id' => intval($results['user_id']),
                'category_id' => $result_qualification_like->category_id,
                'event_id' => intval($results['event_id']),
                'route_id' => intval($results['route_id_'.$i]),
                'gender' => $result_qualification_like->gender,
                'all_attempts' =>  Helpers::find_max_attempts(intval($results['amount_try_top_'.$i]), intval($results['amount_try_zone_'.$i])),
                'amount_top' => $amount_top,
                'amount_try_top' => intval($results['amount_try_top_'.$i]),
                'amount_zone' => $amount_zone,
                'amount_try_zone' => intval($results['amount_try_zone_'.$i]),
            );
            $result_for_edit[] = array(
                'Номер маршрута' => intval($results['route_id_'.$i]),
                'Попытки на топ' => intval($results['amount_try_top_'.$i]),
                'Попытки на зону' => intval($results['amount_try_zone_'.$i])
            );
        }
        $participant = ResultFranceSystemQualification::where('event_id', $results['event_id'])->where('user_id', $results['user_id'])->first();
        $result = ResultRouteFranceSystemQualification::where('event_id', $results['event_id']
        )->where('user_id', $results['user_id'])->first();
        if(!$result){
            $participant->active = 1;
        }
        $participant->result_for_edit_france_system_qualification = $result_for_edit;
        $participant->save();

        DB::table('result_route_france_system_qualification')->insert($data);
        Event::refresh_france_system_qualification_counting($event);
        return $this->response()->success('Результат успешно внесен')->refresh();

    }

    public function custom_form()
    {
        $this->modalLarge();
        $event = Event::where('owner_id', '=', \Encore\Admin\Facades\Admin::user()->id)
            ->where('active', '=', 1)->first();

        $participant_users_id = ResultFranceSystemQualification::where('event_id', '=', $event->id)->pluck('user_id')->toArray();
        $result = User::whereIn('id', $participant_users_id)->pluck('middlename','id')->toArray();
        $result_france_system_qualification = ResultRouteFranceSystemQualification::where('event_id', '=', $event->id)->select('user_id')->distinct()->pluck('user_id')->toArray();
        $categories = ParticipantCategory::where('event_id', '=', $event->id)->pluck('category', 'id')->toArray();
        foreach ($result as $user_id => $middlename){
            $new_middlename = implode(' ', array_reverse(explode(' ', $middlename, 2)));
            $result[$user_id] = $new_middlename;
            if(in_array($user_id, $result_france_system_qualification)){
                $result[$user_id] = $new_middlename.' [Уже добавлен]';
            }
        }
        asort($result);
        $this->hidden('event_id', '')->attribute('data-france-custom-event-id', 'event_id')->value($event->id);
        $this->select('category_id', 'Категория')->attribute('autocomplete', 'off')->attribute('data-france-custom-category-id', 'category_id')->options($categories);
        $this->select('user_id', 'Участник')->attribute('autocomplete', 'off')->attribute('data-france-custom-user-id', 'user_id')->options($result)->required(true);
        $grades = Grades::where('event_id', $event->id)->first();
        if($grades){
            for($i = 1; $i <= $grades->count_routes; $i++){
                $this->integer('route_id_'.$i, 'Трасса')->value($i)->readOnly();
                $this->integer('amount_try_top_'.$i, 'Попытки на топ')->attribute('autocomplete', 'off');
                $this->integer('amount_try_zone_'.$i, 'Попытки на зону')->attribute('autocomplete', 'off');

            }
        }
        $script = <<<EOT
                        $(document).on('change', '[data-final-custom-user-id=user_id]', function () {
                                var amountRoutesInFinal = $event->amount_routes_in_final;
                                for (var i = 1; i <= amountRoutesInFinal; i++) {
                                    $('#amount_try_top_' + i).val('');
                                    $('#amount_try_zone_' + i).val('');
                                }
                          });
                        $(document).on("change", '[data-france-custom-category-id=category_id]', function () {
                             var amountRoutesInFinal = $event->amount_routes_in_final;
                            for (var i = 1; i <= amountRoutesInFinal; i++) {
                                $('#amount_try_top_' + i).val('');
                                $('#amount_try_zone_' + i).val('');
                            }
                            var categoryId = $('[data-france-custom-category-id=category_id]').select2('val')
                            var eventId = $('[data-france-custom-event-id=event_id]').val(); // ID выбранного участника
                            $('[data-france-custom-user-id=user_id]').val('');
                            $.get("/admin/api/get_users",
                                {eventId: eventId, categoryId: categoryId, stage: 'france_system_qualification'},
                                function (data) {
                                    var model = $('[data-france-custom-user-id=user_id]');
                                    model.empty();
                                    model.append("<option>Выбрать</option>");
                                    var sortedData = Object.entries(data).sort(function (a, b) {
                                        return a[1].localeCompare(b[1]); // сортируем по значению (имя пользователя)
                                    });
                                    $.each(sortedData, function (i, item) {
                                        var userId = item[0];
                                        var userName = item[1];
                                        model.append("<option data-france-custom-user-id='" + userId + "' value='" + userId + "'>" + userName + "</option>");
                                    });
                                }
                            );

                        });
                     let btn_close_icon_modal_custom_france = '[id="app-admin-actions-resultroutefrancesystemqualificationstage-batchresultfrancesystemqualification"] [data-dismiss="modal"][class="close"]'
                            $(document).on("click", btn_close_icon_modal_custom_france, function () {
                                window.location.reload();
                            });
                    let btn_close_modal_custom_france = '[id="app-admin-actions-resultroutefrancesystemqualificationstage-batchresultfrancesystemqualification"] [data-dismiss="modal"][class="btn btn-default"]'
                    $(document).on("click", btn_close_modal_custom_france, function () {
                        window.location.reload();
                    });
                 EOT;
        \Encore\Admin\Facades\Admin::script($script);
    }

    public function html()
    {
        $event = Event::where('owner_id', '=', \Encore\Admin\Facades\Admin::user()->id)
            ->where('active', '=', 1)->first();
        $grades = Grades::where('event_id', $event->id)->first();
        if($grades){
            return "<a class='send-add btn btn-sm btn-success'><i class='fa fa-arrow-down'></i> Все трассы</a>
                <style>
                    .send-add {margin-top:8px;}
                 @media screen and (max-width: 767px) {
                        .send-add {margin-top:8px;}
                    }
                </style>

            ";
        } else {
            return "<a class='send-add btn btn-sm btn-success disabled'><i class='fa fa-arrow-down'></i>Все трассы (Необходимо настроить трассы) </a>
                <style>
                    .send-add {margin-top:8px;}
                 @media screen and (max-width: 767px) {
                        .send-add {margin-top:8px;}
                    }
                </style>

            ";
        }


    }

}
