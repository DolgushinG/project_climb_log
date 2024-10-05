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

    public function __construct()
    {
        $this->initInteractor();
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
            $merged_users = ResultFinalStage::get_final_global_participant($event);
        } else {
            $merged_users = ResultFinalStage::get_final_participant($event);
        }
        $result = $merged_users->pluck( 'middlename','id');
        $result_final = ResultRouteFinalStage::where('event_id', '=', $event->id)->select('user_id')->distinct()->pluck('user_id')->toArray();
        $categories = ParticipantCategory::where('event_id', $event->id)->pluck('category', 'id')->toArray();
        foreach ($result as $user_id => $middlename){
            $result[$user_id] = $middlename;
            if(in_array($user_id, $result_final)){
                $result_user = ResultRouteFinalStage::where('event_id', $event->id)->where('user_id', $user_id);
                $routes = $result_user->get()->sortBy('final_route_id')->pluck('final_route_id')->toArray();
                $string_version = '';
                foreach ($routes as $value) {
                    $string_version .= $value . ', ';
                }
                if($result_user->get()->count() == $event->amount_routes_in_final){
                    $result[$user_id] = $middlename.' [Добавлены все трассы]';
                } else {
                    $result[$user_id] = $middlename.' [Трассы: '.$string_version.']';
                }
            }
        }
        $routes = [];
        for($i = 1; $i <= $event->amount_routes_in_final; $i++){
            $routes[$i] = $i;
        }
        $result = $result->toArray();
        asort($result);
        $this->select('category_id', 'Группа')->attribute('autocomplete', 'off')->attribute('data-final-category-id', 'category_id')->options($categories);
        $this->select('user_id', 'Участник')->attribute('autocomplete', 'off')->attribute('data-final-user-id', 'user_id')->options($result)->required();
        $this->hidden('event_id', '')->attribute('autocomplete', 'off')->attribute('data-final-event-id', 'event_id')->value($event->id);
        $this->text('final_user_gender', 'Пол')->attribute('autocomplete', 'off')->readonly();
        $this->text('final_category', 'Группа')->attribute('autocomplete', 'off')->readonly();
        $this->select('final_route_id', 'Трасса')->attribute('autocomplete', 'off')->attribute('data-final-route-id', 'final_route_id')->options($routes)->required();
        $this->integer('all_attempts', 'Все попытки')
            ->attribute('autocomplete', 'off')
            ->attribute('id', 'all_attempts')
            ->attribute('data-all-attempts-id', 'all-attempts');
        $this->integer('amount_try_zone', 'Попытки на зону')->attribute('id', 'amount_try_zone')->attribute('data-amount-try-zone', 'amount_try_zone');
        $this->integer('amount_try_top', 'Попытки на топ')->attribute('id', 'amount_try_top')->attribute('data-amount-try-top', 'amount_try_top');
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
        $script_one_route = <<<EOT
                            function set_attempt_one(){
                                var routeId = $('[data-final-route-id=final_route_id]').val(); // ID выбранного маршрута
                                var userId = $('[data-final-user-id=user_id]').select2('val')
                                var eventId = $('[data-final-event-id=event_id]').val(); // ID выбранного участника
                                var attempt = $('[data-all-attempts-id=all-attempts]').val();
                                var amount_try_top = $('[id=amount_try_top]').val();
                                var amount_try_zone = $('[id=amount_try_zone]').val();
                                let missingFields = [];

                                if (!attempt) {
                                    missingFields.push('Попытки');
                                }
                                if (!userId) {
                                    missingFields.push('Участник');
                                }
                                if (!eventId) {
                                    missingFields.push('событие');
                                }
                                if (!routeId) {
                                    missingFields.push('Трасса');
                                }

                                // Если есть недостающие поля, формируем сообщение об ошибке
                                if (missingFields.length > 0) {
                                   return  $.admin.toastr.error(
                                        'Не хватает данных для их отправки: ' + missingFields.join(', '),
                                        '',
                                        { positionClass: "toast-bottom-center", timeOut: 1000 }
                                    ).css("width", "200px");
                                }
                                 if(Number(amount_try_top) == 1 && Number(amount_try_zone) == 0){
                                         return $.admin.toastr.error(
                                            'У трассы '+routeId+ ' отмечен ТОП, а зона 0 попыток' + missingFields.join(', '),
                                            '',
                                            { positionClass: "toast-bottom-center", timeOut: 2000 }
                                        ).css("width", "200px");
                                    }
                                if(routeId){
                                    $.get("/admin/api/final/set_attempts",
                                        {
                                            route_id: routeId,
                                            user_id: userId,
                                            event_id: eventId,
                                            attempt: attempt,
                                            amount_try_top: amount_try_top,
                                            amount_try_zone: amount_try_zone
                                        },
                                        function (data) {
                                            $.admin.toastr.success(
                                                'Сохранено',
                                                { positionClass: "toast-bottom-center", timeOut: 1000 }
                                            ).css("width", "200px");
                                            $('[data-amount_try_top=amount_try_top]').val(data.amount_try_top);
                                            $('[data-amount_try_zone=amount_try_zone]').val(data.amount_try_zone);
                                            $('[data-all-attempts-id=all-attempts]').val(data.all_attempts);
                                        }
                                    );
                                }
                            }
                         $(document).on("click", '[modal="app-admin-actions-resultroutefinalstage-batchresultfinalcustomfilloneroute"]', function () {
                            const allAttemptsInput = document.getElementById('all_attempts');
                            const incrementBtn = document.getElementById('increment-btn');
                            const decrementBtn = document.getElementById('decrement-btn');
                            const topInput = document.getElementById('amount_try_top');
                            const zoneInput = document.getElementById('amount_try_zone');
                            const zoneBtn = document.getElementById('zone-btn-final');
                            const topBtn = document.getElementById('top-btn-final');

                                if (!incrementBtn && !decrementBtn && !zoneBtn && !topBtn) {
                                    const inputGroupAppend = document.createElement('div');
                                    const inputGroupAppend2 = document.createElement('div');
                                    const inputGroupAppend3 = document.createElement('div');
                                    inputGroupAppend.className = 'input-group-append';
                                    inputGroupAppend2.className = 'input-group-append';
                                    inputGroupAppend3.className = 'input-group-append';

                                    // Создаем кнопку для увеличения
                                    const newIncrementBtn = document.createElement('button');
                                    newIncrementBtn.type = 'button';
                                    newIncrementBtn.className = 'btn btn-warning';
                                    newIncrementBtn.id = 'increment-btn';

                                    const newZoneBtn = document.createElement('button');
                                    newZoneBtn.type = 'button';
                                    newZoneBtn.className = 'btn btn-success';
                                    newZoneBtn.id = 'zone-btn-final';
                                    const newTopBtn = document.createElement('button');
                                    newTopBtn.type = 'button';
                                    newTopBtn.className = 'btn btn-success';
                                    newTopBtn.id = 'top-btn-final';
                                    // Создаем иконку для увеличения
                                    const incrementIcon = document.createElement('i');
                                    incrementIcon.className = 'fa fa-plus';

                                    // Создаем текст для увеличения
                                    const incrementText = document.createElement('span');
                                    const zoneText = document.createElement('span');
                                    const topText = document.createElement('span');
                                    zoneText.textContent = 'Зона'; // Текст "Попытка"
                                    incrementText.textContent = ' Попытка'; // Текст "Попытка"
                                    topText.textContent = 'Топ'; // Текст "Попытка"

                                    // Добавляем иконку и текст в кнопку увеличения
                                    newIncrementBtn.appendChild(incrementIcon);
                                    newIncrementBtn.appendChild(incrementText);
                                    newZoneBtn.appendChild(zoneText);
                                    newTopBtn.appendChild(topText);

                                    // Создаем кнопку для удаления
                                    const newDecrementBtn = document.createElement('button');
                                    newDecrementBtn.type = 'button';
                                    newDecrementBtn.className = 'btn btn-danger';
                                    newDecrementBtn.id = 'decrement-btn';

                                    // Создаем иконку для удаления
                                    const decrementIcon = document.createElement('i');
                                    decrementIcon.className = 'fa fa-minus';

                                    // Добавляем иконку в кнопку удаления
                                    newDecrementBtn.appendChild(decrementIcon);

                                    // Добавляем кнопки в группу ввода
                                    inputGroupAppend.appendChild(newDecrementBtn);
                                    inputGroupAppend.appendChild(newIncrementBtn);
                                    inputGroupAppend2.appendChild(newZoneBtn);
                                    inputGroupAppend3.appendChild(newTopBtn);

                                    // Находим родительский элемент и добавляем группу ввода после поля
                                    allAttemptsInput.parentNode.appendChild(inputGroupAppend);
                                    zoneInput.parentNode.appendChild(inputGroupAppend2);
                                    topInput.parentNode.appendChild(inputGroupAppend3);

                                    newZoneBtn.addEventListener('click', function () {
                                        let currentValue = parseInt(allAttemptsInput.value) || 0;
                                         $('[data-amount-try-zone=amount_try_zone]').val(currentValue);
                                        set_attempt_one()
                                    });
                                    newTopBtn.addEventListener('click', function () {
                                        let currentValue = parseInt(allAttemptsInput.value) || 0;
                                         $('[data-amount-try-top=amount_try_top]').val(currentValue);
                                        set_attempt_one()
                                    });
                                    // Обработчик клика на кнопку увеличения
                                    newIncrementBtn.addEventListener('click', function () {
                                        let currentValue = parseInt(allAttemptsInput.value) || 0;
                                        allAttemptsInput.value = currentValue + 1;
                                        set_attempt_one()
                                    });

                                    // Обработчик клика на кнопку удаления
                                    newDecrementBtn.addEventListener('click', function () {
                                        let currentValue = parseInt(allAttemptsInput.value) || 0;
                                        if (currentValue > 0) {
                                            allAttemptsInput.value = currentValue - 1;
                                        }
                                        set_attempt_one()
                                    });
                                    $('[data-all-attempts-id=all-attempts]').val('');
                                    $('[data-amount-try-top=amount_try_top]').val('');
                                    $('[data-amount-try-zone=amount_try_zone]').val('');
                                    $('[data-final-user-id=user_id]').val('');
                                }
                            });
                            $(document).on("change", '[data-final-user-id="user_id"]', function () {
                                var routeId = $('[data-final-route-id=final_route_id]').val(); // ID выбранного маршрута
                                var userId = $('[data-final-user-id="user_id"]').select2('val')
                                var eventId = $('[data-final-event-id=event_id]').val(); // ID выбранного участника
                                if(routeId){
                                    $.get("/admin/api/final/get_attempts", // URL эндпоинта
                                        {
                                            route_id: routeId,
                                            user_id: userId,
                                            event_id: eventId
                                        }, // Передаем ID маршрута и участника в запросе
                                        function (data) {
                                            // Обновляем поля с количеством попыток
                                            $('[data-amount-try-top=amount_try_top]').val(data.amount_try_top);
                                            $('[data-amount-try-zone=amount_try_zone]').val(data.amount_try_zone);
                                            $('[data-all-attempts-id=all-attempts]').val(data.all_attempts);
                                        }
                                    );
                                }
                                if(userId){
                                    $.get("/admin/api/get_user_info", // URL эндпоинта
                                        {
                                            user_id: userId,
                                            event_id: eventId
                                        },
                                        function (data) {
                                            $('[id="final_user_gender"]').val(data.gender);
                                            $('[id="final_category"]').val(data.category);
                                        }
                                    );
                                }
                            });
                            $(document).on("change", '[data-final-route-id=final_route_id]', function () {
                                var routeId = $(this).val(); // ID выбранного маршрута
                                var userId = $('[data-final-user-id="user_id"]').select2('val')
                                var eventId = $('[data-final-event-id=event_id]').val(); // ID выбранного участника
                                $.get("/admin/api/final/get_attempts", // URL эндпоинта
                                    {
                                        route_id: routeId,
                                        user_id: userId,
                                        event_id: eventId
                                    },
                                    function (data) {
                                        // Обновляем поля с количеством попыток
                                        $('[data-all-attempts-id=all-attempts]').val(data.all_attempts);
                                        $('[data-amount-try-top=amount_try_top]').val(data.amount_try_top);
                                        $('[data-amount-try-zone=amount_try_zone]').val(data.amount_try_zone);
                                    }
                                );

                            });
                            $(document).on("change", '[data-final-category-id=category_id]', function () {
                                var categoryId = $('[data-final-category-id=category_id]').select2('val')
                                var eventId = $('[data-final-event-id=event_id]').val(); // ID выбранного участника
                                $('[data-final-user-id=user_id]').val(''); // ID выбранного участника

                                $.get("/admin/api/get_users",
                                    {eventId: eventId, categoryId: categoryId, stage: 'final'},
                                    function (data) {
                                        var model = $('[data-final-user-id=user_id]');
                                        model.empty();
                                        model.append("<option>Выбрать</option>");
                                        var sortedData = Object.entries(data).sort(function (a, b) {
                                            return a[1].localeCompare(b[1]); // сортируем по значению (имя пользователя)
                                        });
                                        $.each(sortedData, function (i, item) {
                                            var userId = item[0];
                                            var userName = item[1];
                                            model.append("<option data-final-user-id='" + userId + "' value='" + userId + "'>" + userName + "</option>");
                                        });
                                    }
                                );

                            });
                            let btn_close_icon_modal_one_route = '[id="app-admin-actions-resultroutefinalstage-batchresultfinalcustomfilloneroute"] [data-dismiss="modal"][class="close"]'
                            $(document).on("click", btn_close_icon_modal_one_route, function () {
                                window.location.reload();
                            });
                            let btn_close_modal_one_route = '[id="app-admin-actions-resultroutefinalstage-batchresultfinalcustomfilloneroute"] [data-dismiss="modal"][class="btn btn-default"]'
                            $(document).on("click", btn_close_modal_one_route, function () {
                                window.location.reload();
                            });
                        EOT;
        \Encore\Admin\Facades\Admin::script($script_one_route);

    }

    public function html()
    {
        $event = Event::where('owner_id', '=', \Encore\Admin\Facades\Admin::user()->id)->where('active', 1)->first();
        if($event->amount_the_best_participant_to_go_final > 0){
            return "<a class='result-add-final-one-route btn btn-sm btn-primary'><i class='fa fa-plus-circle'></i> По одной трассе</a>
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
