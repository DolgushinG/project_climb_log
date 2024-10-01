<?php

namespace App\Admin\Actions\ResultRoute;

use App\Admin\Extensions\CustomAction;
use App\Models\Event;
use App\Models\ParticipantCategory;
use App\Models\Set;
use Encore\Admin\Admin;
use Illuminate\Http\Request;

class BatchResultRouteUniversal extends CustomAction
{

    protected $selector = '.result-universal';
    private mixed $stage;

    /**
     * @throws \Exception
     */
    public function __construct($stage = '')
    {
        $this->initInteractor();
        $this->stage = $stage;
    }

    public function handle(Request $request)
    {
        $results = $request->toArray();
        $stage = $results['stage'];
        $amount_try_top = intval($results['amount_try_top']);
        $amount_try_zone = intval($results['amount_try_zone']);
        $all_attempts = intval($results['all_attempts']);
        $event_id = intval($results['event_id']);
        $user_id = intval($results['user_id']);
        $event = Event::find($event_id);
        $route_id = intval($results['route_id']);
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
        $validate = BatchBaseRoute::validate(
            route_id: $route_id,
            amount_try_top: $amount_try_top,
            amount_try_zone: $amount_try_zone,
            amount_top: $amount_top,
            amount_zone: $amount_zone,
            all_attempts: $all_attempts);

        if($validate){
            return $this->response()->error($validate);
        }
        BatchBaseRoute::handle(
            event: $event,
            stage: $stage,
            user_id: $user_id,
            route_id: $route_id,
            amount_try_top: $amount_try_top,
            amount_try_zone: $amount_try_zone,
            amount_top: $amount_top,
            amount_zone: $amount_zone,
            all_attempts: $all_attempts
        );
        return $this->response()->success('Результат успешно внесен')->refresh();
    }

    public function custom_form()
    {
        $this->modalSmall();
        $url_get_attempts = BatchBaseRoute::URLS_GET_ATTEMPTS[$this->stage];
        $url_set_attempts = BatchBaseRoute::URLS_SET_ATTEMPTS[$this->stage];
        $event = Event::where('owner_id', '=', \Encore\Admin\Facades\Admin::user()->id)
            ->where('active', '=', 1)->first();
        $amount_routes = BatchBaseRoute::get_amount_routes($event, $this->stage);
        $categories = ParticipantCategory::where('event_id', $event->id)->pluck('category', 'id')->toArray();
        $result = BatchBaseRoute::merged_users($event, $this->stage);
        $routes = BatchBaseRoute::routes($amount_routes);

        $this->select('category_id', 'Группа')->attribute('autocomplete', 'off')->attribute('data-category-id', 'category_id')->options($categories);
        if ($this->stage == 'qualification'){
            $sets = Set::where('event_id', $event->id)->pluck('number_set', 'id')->toArray();
            $this->multipleSelect('number_set_id', 'Сеты')->attribute('autocomplete', 'off')->attribute('data-number-set-id', 'number_set_id')->options($sets);
        }
        $this->select('gender', 'Пол')->attribute('autocomplete', 'off')->attribute('data-gender-id', 'gender')->options(['male' => 'М', 'female' => 'Ж']);
        $this->select('user_id', 'Участник')->attribute('autocomplete', 'off')->attribute('data-user-id', 'user_id')->options($result)->required();
        $this->hidden('event_id', '')->attribute('autocomplete', 'off')->attribute('data-event-id', 'event_id')->value($event->id);
        $this->hidden('stage', '')->attribute('autocomplete', 'off')->value($this->stage);
        $this->text('user_gender_one', 'Пол')->placeholder('Пол')->attribute('autocomplete', 'off')->width('70px')->readonly();
        $this->select('route_id', 'Трасса')->attribute('autocomplete', 'off')->attribute('data-route-id', 'route_id')->options($routes)->required();
        $this->integer('all_attempts', 'Все попытки')
            ->placeholder('...')
            ->attribute('autocomplete', 'off')
            ->attribute('id', 'all_attempts')
            ->width('70px')
            ->attribute('data-all-attempts-id', 'all-attempts');
        $this->integer('amount_try_zone', 'Попытки на зону')->placeholder('..')->width('70px')->attribute('id', 'amount_try_zone')->attribute('data-amount-try-zone', 'amount_try_zone');
        $this->integer('amount_try_top', 'Попытки на топ')->placeholder('..')->width('70px')->attribute('id', 'amount_try_top')->attribute('data-amount-try-top', 'amount_try_top');

        Admin::style('
                .input-group-append {
                    display: flex;
                    align-items: center;
                    gap: 10px;
                }
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

            .btn-warning {
                margin-left: 5px;
            }

        ');
        $script_one_route = <<<EOT
                            function set_attempt(){
                                var routeId = $('[data-route-id=route_id]').val(); // ID выбранного маршрута
                                var userId = $('[data-user-id=user_id]').select2('val')
                                var eventId = $('[data-event-id=event_id]').val(); // ID выбранного участника
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
                                if (!routeId) {
                                    missingFields.push('Трасса');
                                }
                                if (missingFields.length > 0) {
                                   return  $.admin.toastr.error(
                                        'Не хватает данных для их отправки: ' + missingFields.join(', '),
                                        '',
                                        { positionClass: "toast-bottom-center", timeOut: 1000 }
                                    ).css("width", "200px");
                                }
                                 if(Number(amount_try_top) > 0 && Number(amount_try_zone) == 0){
                                        amount_try_zone = amount_try_top
                                 }
                                if(routeId){
                                    $.get("$url_set_attempts",
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
                                            $('[data-amount-try-top=amount_try_top]').val(data.amount_try_top);
                                            $('[data-amount-try-zone=amount_try_zone]').val(data.amount_try_zone);
                                            $('[data-all-attempts-id=all-attempts]').val(data.all_attempts);
                                        }
                                    );
                                }
                            }
                         $(document).on("click", '#universal-$this->stage', function () {
                            const allAttemptsInput = document.getElementById('all_attempts');
                            const incrementBtn = document.getElementById('increment-btn');
                            const decrementBtn = document.getElementById('decrement-btn');
                            const topInput = document.getElementById('amount_try_top');
                            const zoneInput = document.getElementById('amount_try_zone');
                            const zoneBtn = document.getElementById('zone-btn');
                            const topBtn = document.getElementById('top-btn');
                            const categoryText = document.getElementById('user_category_one');
                            if(!categoryText){
                                $(document).ready(function() {
                                var genderInput = $('#user_gender_one');
                                var formGroup = genderInput.closest('.form-group');

                                var categoryLabel = $('<label>').text('Категория');
                                var categoryInput = $('<input>').attr({
                                    'autocomplete': 'off',
                                    'style': 'width: 100%',
                                    'readonly': '',
                                    'type': 'text',
                                    'id': 'user_category_one',
                                    'name': 'user_category_one',
                                    'value': '',
                                    'class': 'form-control user_category_one action',
                                    'placeholder': 'Группа'
                                });
                                genderInput.after(categoryLabel).after(categoryInput);

                                // Находим родительский элемент с классом form-group
                                var formGroup = genderInput.closest('.form-group');

                                // Применяем стили для вертикального расположения label и input для gender
                                formGroup.css({
                                    'display': 'flex',
                                    'gap': '20px', // Отступ между колонками gender и category
                                    'align-items': 'flex-start' // Выравнивание по верхнему краю
                                });

                                // Применяем стили для меток и input внутри form-group, чтобы они располагались вертикально
                                formGroup.find('label').css({
                                    'display': 'block',
                                    'margin-bottom': '5px'
                                });

                                // Создаем обертку для первой колонки (gender)
                                var genderColumn = $('<div>').css({
                                    'display': 'flex',
                                    'flex-direction': 'column'
                                });

                                // Создаем обертку для второй колонки (category)
                                var categoryColumn = $('<div>').css({
                                    'display': 'flex',
                                    'flex-direction': 'column'
                                });

                                // Перемещаем существующие элементы в их колонки
                                var genderLabel = formGroup.find('label[for="user_gender_one"]');
                                var categoryLabel = formGroup.find('label[for="user_category_one"]');
                                var categoryInput = $('#user_category_one');

                                genderColumn.append(genderLabel).append(genderInput); // Добавляем метку и поле gender в колонку
                                categoryColumn.append(categoryLabel).append(categoryInput); // Добавляем метку и поле category в колонку

                                // Очищаем содержимое form-group и добавляем обе колонки
                                formGroup.empty().append(genderColumn).append(categoryColumn);

                                });
                            }


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
                                    newTopBtn.id = 'top-btn';
                                    const incrementIcon = document.createElement('i');
                                    incrementIcon.className = 'fa fa-plus';

                                    const incrementText = document.createElement('span');
                                    const zoneText = document.createElement('span');
                                    const topText = document.createElement('span');
                                    zoneText.textContent = 'Зона';
                                    incrementText.textContent = ' Попытка';
                                    topText.textContent = 'Топ';

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

                                    const formGroupAttempt = allAttemptsInput.closest('.form-group');
                                    allAttemptsInput.style.height = '44px';
                                    allAttemptsInput.style.marginBottom = '-10px';
                                    allAttemptsInput.style.fontSize = '25px';
                                    formGroupAttempt.insertBefore(inputGroupAppend, allAttemptsInput);
                                    const labelAttempt = formGroupAttempt.querySelector('label');
                                    if (labelAttempt) {
                                        labelAttempt.remove();
                                    }
                                    formGroupAttempt.style.display = 'flex';
                                    formGroupAttempt.style.alignItems = 'center';
                                    formGroupAttempt.style.gap = '10px';


                                    const formGroupZone = zoneInput.closest('.form-group');
                                    zoneInput.style.height = '44px';
                                    zoneInput.style.marginBottom = '-10px';
                                    zoneInput.style.fontSize = '25px';
                                    formGroupZone.insertBefore(inputGroupAppend2, zoneInput);
                                    const labelZone = formGroupZone.querySelector('label');
                                    if (labelZone) {
                                        labelZone.remove();
                                    }
                                    formGroupZone.style.display = 'flex';
                                    formGroupZone.style.alignItems = 'center';
                                    formGroupZone.style.gap = '10px';

                                    const formGroupTop = topInput.closest('.form-group');
                                    topInput.style.height = '44px';
                                    topInput.style.marginBottom = '-10px';
                                    topInput.style.fontSize = '25px';
                                    formGroupTop.insertBefore(inputGroupAppend3, topInput);
                                    const labelTop = formGroupTop.querySelector('label');
                                    if (labelTop) {
                                        labelTop.remove();
                                    }
                                    formGroupTop.style.display = 'flex';
                                    formGroupTop.style.alignItems = 'center';
                                    formGroupTop.style.gap = '10px';


                                    newZoneBtn.addEventListener('click', function () {
                                        let currentValue = parseInt(allAttemptsInput.value) || 0;
                                         $('[data-amount-try-zone=amount_try_zone]').val(currentValue);
                                        set_attempt()
                                    });
                                    newTopBtn.addEventListener('click', function () {
                                        let currentValue = parseInt(allAttemptsInput.value) || 0;
                                         $('[data-amount-try-top=amount_try_top]').val(currentValue);
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
                                    $('[data-all-attempts-id=all-attempts]').val('');
                                    $('[data-amount-try-top=amount_try_top]').val('');
                                    $('[data-amount-try-zone=amount_try_zone]').val('');
                                    $('[data-user-id=user_id]').val('');
                                }
                            });
                            $(document).on("change", '[data-user-id="user_id"]', function () {
                                var routeId = $('[data-route-id=route_id]').val();
                                var userId = $('[data-user-id="user_id"]').select2('val')
                                var eventId = $('[data-event-id=event_id]').val();
                                if(routeId){
                                    $.get("$url_get_attempts",
                                        {
                                            route_id: routeId,
                                            user_id: userId,
                                            event_id: eventId
                                        },
                                        function (data) {
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
                                            $('[id="user_gender_one"]').val(data.gender);
                                            $('[id="user_category_one"]').val(data.category);
                                        }
                                    );
                                }
                            });
                            $(document).on("change", '[data-route-id=route_id]', function () {
                                var routeId = $(this).val(); // ID выбранного маршрута
                                var userId = $('[data-user-id="user_id"]').select2('val')
                                var eventId = $('[data-event-id=event_id]').val(); // ID выбранного участника
                                $.get("$url_get_attempts", // URL эндпоинта
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
                            $(document).on("change", '[data-category-id=category_id]', function () {
                                var categoryId = $('[data-category-id=category_id]').select2('val')
                                var eventId = $('[data-event-id=event_id]').val(); // ID выбранного участника
                                $('[data-user-id=user_id]').val(''); // ID выбранного участника

                                $.get("/admin/api/get_users",
                                    {eventId: eventId, categoryId: categoryId, stage: '$this->stage'},
                                    function (data) {
                                        var model = $('[data-user-id=user_id]');
                                        model.empty();
                                        model.append("<option>Выбрать</option>");
                                        var sortedData = Object.entries(data).sort(function (a, b) {
                                            return a[1].localeCompare(b[1]); // сортируем по значению (имя пользователя)
                                        });
                                        $.each(sortedData, function (i, item) {
                                            var userId = item[0];
                                            var userName = item[1];
                                            model.append("<option data-user-id='" + userId + "' value='" + userId + "'>" + userName + "</option>");
                                        });
                                    }
                                );

                            });
                            $(document).on("change", '[data-gender-id=gender]', function () {
                                var gender = $('[data-gender-id=gender]').select2('val')
                                var numberSetId = $('[data-number-set-id=number_set_id]').select2('val')
                                var categoryId = $('[data-category-id=category_id]').select2('val')
                                var eventId = $('[data-event-id=event_id]').val();
                                $.get("/admin/api/get_users",
                                    {eventId: eventId, numberSetId: numberSetId, categoryId: categoryId, gender: gender, stage: '$this->stage'},
                                    function (data) {
                                        var model = $('[data-user-id=user_id]');
                                        model.empty();
                                        model.append("<option>Выбрать</option>");
                                        var sortedData = Object.entries(data).sort(function (a, b) {
                                            return a[1].localeCompare(b[1]);
                                        });
                                        $.each(sortedData, function (i, item) {
                                            var userId = item[0];
                                            var userName = item[1];
                                            model.append("<option data-user-id='" + userId + "' value='" + userId + "'>" + userName + "</option>");
                                        });
                                    }
                                );

                            });
                            $(document).on("change", '[data-number-set-id=number_set_id]', function () {
                                var numberSetId = $('[data-number-set-id=number_set_id]').select2('val')
                                var categoryId = $('[data-category-id=category_id]').select2('val')
                                var eventId = $('[data-event-id=event_id]').val();
                                $.get("/admin/api/get_users",
                                    {eventId: eventId, numberSetId: numberSetId, categoryId: categoryId, stage: '$this->stage'},
                                    function (data) {
                                        var model = $('[data-user-id=user_id]');
                                        model.empty();
                                        model.append("<option>Выбрать</option>");
                                        var sortedData = Object.entries(data).sort(function (a, b) {
                                            return a[1].localeCompare(b[1]);
                                        });
                                        $.each(sortedData, function (i, item) {
                                            var userId = item[0];
                                            var userName = item[1];
                                            model.append("<option data-user-id='" + userId + "' value='" + userId + "'>" + userName + "</option>");
                                        });
                                    }
                                );

                            });
                            let icon_close = '[id="app-admin-actions-resultroute-batchresultrouteuniversal"] [data-dismiss="modal"][class="close"]'
                            $(document).on("click", icon_close, function () {
                                window.location.reload();
                            });
                            let btn_close = '[id="app-admin-actions-resultroute-batchresultrouteuniversal"] [data-dismiss="modal"][class="btn btn-default"]'
                            $(document).on("click", btn_close, function () {
                                window.location.reload();
                            });

                        EOT;

        \Encore\Admin\Facades\Admin::script($script_one_route);

    }

    public function html()
    {
        return "<a id='universal-$this->stage' class='result-universal btn btn-sm btn-primary'><i class='fa fa-plus-circle'></i> По одной трассе</a>
                 <style>
                 .result-universal {margin-top:8px;}
                 @media screen and (max-width: 767px) {
                        .result-universal {margin-top:8px;}
                    }
                </style>
            ";

    }
}



