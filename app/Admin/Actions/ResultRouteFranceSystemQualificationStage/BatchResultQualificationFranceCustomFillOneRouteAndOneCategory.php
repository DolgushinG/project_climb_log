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
use App\Models\Set;
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
    public function __construct()
    {
        $this->initInteractor();
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
        $eventId = $event->id;
        $participant_users_id = ResultFranceSystemQualification::where('event_id', $eventId)->pluck('user_id')->toArray();
        $result = User::whereIn('id', $participant_users_id)->pluck('middlename','id');
        $amount_routes = Grades::where('event_id', $eventId)->first();
        if($amount_routes){
            $amount_routes = $amount_routes->count_routes;
        } else {
            $amount_routes = 0;
        }
        $sortedUsers = $result->mapWithKeys(function ($middlename, $id) use($eventId, $amount_routes) {
            $result_user = ResultRouteFranceSystemQualification::where('event_id', $eventId)->where('user_id', $id);
            $routes = $result_user->get()->sortBy('route_id')->pluck('route_id')->toArray();
            $string_version = '';
            foreach ($routes as $value) {
                $string_version .= $value . ', ';
            }
            if($result_user->get()->count() == $amount_routes){
                $str = ' [Добавлены все трассы]';
            } else {
                $str =  ' [Трассы: '.$string_version.']';
            }
            return [$id => $middlename. $str ];
        })->toArray();
        $sets = Set::where('event_id', $event->id)->pluck('number_set', 'id')->toArray();
        $this->multipleSelect('number_set_id', 'Сеты')->attribute('autocomplete', 'off')->attribute('data-category-number-set-id', 'number_set_id')->options($sets);
        $this->select('user_id', 'Участник')->attribute('autocomplete', 'off')->attribute('data-category-user-id', 'user_id')->options($sortedUsers);
        $this->text('user_gender', 'Пол')->attribute('autocomplete', 'off')->readonly();
        $this->text('category', 'Группа')->attribute('autocomplete', 'off')->readonly();
        $this->hidden('event_id', '')->attribute('autocomplete', 'off')->attribute('data-category-event-id', 'event_id')->value($event->id);
        $this->select('route_id', 'Трасса')->attribute('autocomplete', 'off')->attribute('data-category-route-id', 'route_id')->options($routes)->required();
        $this->integer('all_attempts', 'Все попытки')
            ->attribute('autocomplete', 'off')
            ->attribute('data-all-attempts-id', 'all-attempts');
        $this->integer('amount_try_zone_category', 'Попытки на зону')->attribute('autocomplete', 'off');
        $this->integer('amount_try_top_category', 'Попытки на топ')->attribute('autocomplete', 'off');
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
        $script = <<<EOT
                    function set_attempt(){
                        var routeId = $('[data-category-route-id=route_id]').val(); // ID выбранного маршрута
                        var userId = $('[data-category-user-id="user_id"]').select2('val')
                        var eventId = $('[data-category-event-id=event_id]').val(); // ID выбранного участника
                        var attempt = $('[data-all-attempts-id=all-attempts]').val();
                        var amount_try_top = $('[id=amount_try_top_category]').val();
                        var amount_try_zone = $('[id=amount_try_zone_category]').val();

                        / Проверяем необходимые поля
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
                            missingFields.push('Маршрут');
                        }

                        // Если есть недостающие поля, формируем сообщение об ошибке
                        if (missingFields.length > 0) {
                            $.admin.toastr.error(
                                'Не хватает данных для их отправки: ' + missingFields.join(', '),
                                '',
                                { positionClass: "toast-bottom-center", timeOut: 10000 }
                            ).css("width", "500px");
                        }


                        if(routeId){
                            $.get("/admin/api/set_attempts",
                                {
                                    route_id: routeId,
                                    user_id: userId,
                                    event_id: eventId,
                                    attempt: attempt,
                                    amount_try_top: amount_try_top,
                                    amount_try_zone: amount_try_zone
                                },
                                function (data) {
                                    $('[id=amount_try_top_category]').val(data.amount_try_top);
                                    $('[id=amount_try_zone_category]').val(data.amount_try_zone);
                                    $('[data-all-attempts-id=all-attempts]').val(data.all_attempts);
                                }
                            );
                        }

                    }
                    $(document).on("click", '#result-all-user', function () {

                        const allAttemptsInput = document.getElementById('all_attempts');
                        const topInput = document.getElementById('amount_try_top_category');
                        const zoneInput = document.getElementById('amount_try_zone_category');
                        const incrementBtn = document.getElementById('increment-btn');
                        const decrementBtn = document.getElementById('decrement-btn');
                        const zoneBtn = document.getElementById('zone-btn');
                        const topBtn = document.getElementById('top-btn');
                        if (!incrementBtn && !decrementBtn && !zoneBtn && !topBtn) {
                            const inputGroupAppend = document.createElement('div');
                            const inputGroupAppend2 = document.createElement('div');
                            const inputGroupAppend3 = document.createElement('div');
                            inputGroupAppend.className = 'input-group-append';
                            inputGroupAppend2.className = 'input-group-append';
                            inputGroupAppend3.className = 'input-group-append';
                            const newIncrementBtn = document.createElement('button');
                            newIncrementBtn.type = 'button';
                            newIncrementBtn.className = 'btn btn-warning';
                            newIncrementBtn.id = 'increment-btn';

                            const newZoneBtn = document.createElement('button');
                            newZoneBtn.type = 'button';
                            newZoneBtn.className = 'btn btn-success';
                            newZoneBtn.id = 'zone-btn';
                            const newTopBtn = document.createElement('button');
                            newTopBtn.type = 'button';
                            newTopBtn.className = 'btn btn-success';
                            newTopBtn.id = 'zone-btn';
                            const incrementIcon = document.createElement('i');
                            incrementIcon.className = 'fa fa-plus';
                            const incrementText = document.createElement('span');
                            const zoneText = document.createElement('span');
                            const topText = document.createElement('span');
                            zoneText.textContent = 'Зона'; // Текст "Попытка"
                            incrementText.textContent = ' Попытка'; // Текст "Попытка"
                            topText.textContent = 'Топ'; // Текст "Попытка"
                            newIncrementBtn.appendChild(incrementIcon);
                            newIncrementBtn.appendChild(incrementText);
                            newZoneBtn.appendChild(zoneText);
                            newTopBtn.appendChild(topText);
                            const newDecrementBtn = document.createElement('button');
                            newDecrementBtn.type = 'button';
                            newDecrementBtn.className = 'btn btn-danger';
                            newDecrementBtn.id = 'decrement-btn';
                            const decrementIcon = document.createElement('i');
                            decrementIcon.className = 'fa fa-minus';
                            newDecrementBtn.appendChild(decrementIcon);
                            inputGroupAppend.appendChild(newDecrementBtn);
                            inputGroupAppend.appendChild(newIncrementBtn);
                            inputGroupAppend2.appendChild(newZoneBtn);
                            inputGroupAppend3.appendChild(newTopBtn);
                            allAttemptsInput.parentNode.appendChild(inputGroupAppend);
                            zoneInput.parentNode.appendChild(inputGroupAppend2);
                            topInput.parentNode.appendChild(inputGroupAppend3);
                            newZoneBtn.addEventListener('click', function () {
                                let currentValue = parseInt(allAttemptsInput.value) || 0;
                                $('[id=amount_try_zone_category]').val(currentValue);
                                set_attempt()
                            });
                            newTopBtn.addEventListener('click', function () {
                                let currentValue = parseInt(allAttemptsInput.value) || 0;
                                $('[id=amount_try_top_category]').val(currentValue);
                                set_attempt()
                            });
                            newIncrementBtn.addEventListener('click', function () {
                                let currentValue = parseInt(allAttemptsInput.value) || 0;
                                allAttemptsInput.value = currentValue + 1;
                                set_attempt()
                            });
                            newDecrementBtn.addEventListener('click', function () {
                                let currentValue = parseInt(allAttemptsInput.value) || 0;
                                if (currentValue > 0) {
                                    allAttemptsInput.value = currentValue - 1;
                                }
                                set_attempt()
                            });
                        }
                    });

                    let btn_close_modal_category = '[id="app-admin-actions-resultroutefrancesystemqualificationstage-batchresultqualificationfrancecustomfillonerouteandonecategory"] [data-dismiss="modal"][class="btn btn-default"]'
                    $(document).on("click", btn_close_modal_category, function () {
                        window.location.reload();
                    });
                    let btn_close_icon_modal_category = '[id="app-admin-actions-resultroutefrancesystemqualificationstage-batchresultqualificationfrancecustomfillonerouteandonecategory"] [data-dismiss="modal"][class="close"]'
                    $(document).on("click", btn_close_icon_modal_category, function () {
                        window.location.reload();
                    });
                    $(document).on("change", '[data-category-number-set-id=number_set_id]', function () {
                        var numberSetId = $('[data-category-number-set-id=number_set_id]').select2('val')
                        var eventId = $('[data-category-event-id=event_id]').val(); // ID выбранного участника
                        $.get("/admin/api/get_users",
                            {eventId: eventId, numberSetId: numberSetId},
                            function (data) {
                                var model = $('[data-category-user-id=user_id]');
                                model.empty();
                                model.append("<option>Выбрать</option>");
                                var sortedData = Object.entries(data).sort(function (a, b) {
                                    return a[1].localeCompare(b[1]); // сортируем по значению (имя пользователя)
                                });
                                $.each(sortedData, function (i, item) {
                                    var userId = item[0];
                                    var userName = item[1];
                                    model.append("<option data-category-user-id='" + userId + "' value='" + userId + "'>" + userName + "</option>");
                                });
                            }
                        );

                    });
                    $(document).on("change", '[data-category-user-id=user_id]', function () {
                        var routeId = $('[data-category-route-id=route_id]').val(); // ID выбранного маршрута
                        var userId = $('[data-category-user-id="user_id"]').select2('val')
                        var eventId = $('[data-category-event-id=event_id]').val(); // ID выбранного участника
                        if(routeId){
                            $.get("/admin/api/get_attempts", // URL эндпоинта
                                {
                                    route_id: routeId,
                                    user_id: userId,
                                    event_id: eventId
                                }, // Передаем ID маршрута и участника в запросе
                                function (data) {
                                    // Обновляем поля с количеством попыток
                                    $('[id=amount_try_top_category]').val(data.amount_try_top);
                                    $('[id=amount_try_zone_category]').val(data.amount_try_zone);
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
                                    // Обновляем поля с количеством попыток
                                    $('[id="user_gender"]').val(data.gender);
                                    $('[id="category"]').val(data.category);
                                }
                            );
                        }
                    });
                    $(document).on("change", '[data-category-route-id=route_id]', function () {
                        var routeId = $(this).val(); // ID выбранного маршрута
                        var userId = $('[data-category-user-id="user_id"]').select2('val')
                        var eventId = $('[data-category-event-id=event_id]').val(); // ID выбранного участника
                        $.get("/admin/api/get_attempts", // URL эндпоинта
                            {
                                route_id: routeId,
                                user_id: userId,
                                event_id: eventId
                            },
                            function (data) {
                                // Обновляем поля с количеством попыток
                                $('[data-all-attempts-id=all-attempts]').val(data.all_attempts);
                                $('[id=amount_try_top_category]').val(data.amount_try_top);
                                $('[id=amount_try_zone_category]').val(data.amount_try_zone);
                            }
                        );
                    });
                    EOT;
        Admin::script($script);
    }
    public function html()
    {
        return "<a id='result-all-user' class='result-add-qualification-france-one-route-one-category btn btn-sm btn-warning'><i class='fa fa-plus-circle'></i> Все участники по одной трассе </a>
                 <style>
                 .result-add-qualification-france-one-route-one-category {margin-top:8px;}
                 @media screen and (max-width: 767px) {
                        .result-add-qualification-france-one-route-one-category {margin-top:8px; margin-left: 0px!important;}
                    }
                </style>
            ";
    }

}
