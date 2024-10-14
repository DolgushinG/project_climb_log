<?php
namespace App\Admin\Controllers;

use App\Admin\Actions\ResultRoute\BatchBaseRoute;
use App\Http\Controllers\Controller;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Layout\Content;
use App\Models\Event;
use App\Models\Set;
use App\Models\ParticipantCategory;

class CustomFormFinalController extends Controller
{
    public function index(Content $content)
    {
        $content->body($this->customForm('final'));
        return $content;
    }

    public static function customForm($stage)
    {
        // Создаем объект формы
        $form = new \Encore\Admin\Widgets\Form();

        // Получаем необходимые данные для формы
        $url_get_attempts = BatchBaseRoute::URLS_GET_ATTEMPTS[$stage];
        $url_set_attempts = BatchBaseRoute::URLS_SET_ATTEMPTS[$stage];
        $event = Event::where('owner_id', '=', Admin::user()->id)
            ->where('active', '=', 1)->first();
        $amount_routes = BatchBaseRoute::get_amount_routes($event, $stage);
        $categories = ParticipantCategory::where('event_id', $event->id)->pluck('category', 'id')->toArray();
        $result = BatchBaseRoute::merged_users($event, $stage);
        $routes = BatchBaseRoute::routes($amount_routes);

        $form->select('category_id', 'Группа')
            ->attribute('autocomplete', 'off')
            ->attribute('data-category-id', 'category_id')
            ->options($categories);

        if ($stage == 'qualification') {
            $sets = Set::where('event_id', $event->id)->pluck('number_set', 'id')->toArray();
            $form->multipleSelect('number_set_id', 'Сеты')
                ->attribute('autocomplete', 'off')
                ->attribute('data-number-set-id', 'number_set_id')
                ->options($sets);
        }
        $form->select('gender', 'Пол')
            ->attribute('autocomplete', 'off')
            ->attribute('data-stage', $stage)
            ->attribute('data-event-id', $event->id)
            ->attribute('data-get-url', $url_get_attempts)
            ->attribute('data-set-url', $url_set_attempts)
            ->attribute('data-gender-id', 'gender')
            ->options(['male' => 'М', 'female' => 'Ж']);
        $form->select('user_id', 'Участник')
            ->attribute('autocomplete', 'off')
            ->attribute('data-user-id', 'user_id')
            ->options($result)
            ->required();
        $form->text('user_gender_one', 'Пол')
            ->placeholder('Пол')
            ->withoutIcon()
            ->readonly()
            ->width('70px');
        $form->text('user_category_one', 'Группа')
            ->placeholder('Группа')
            ->withoutIcon()
            ->readonly()
            ->width('70px');

        $form->select('route_id', 'Трасса')
            ->attribute('autocomplete', 'off')
            ->attribute('data-route-id', 'route_id')
            ->options($routes)
            ->required();

        $form->number('all_attempts', 'Все попытки')
            ->placeholder('...')
            ->attribute('autocomplete', 'off')
            ->attribute('id', 'all_attempts')
            ->attribute('data-all-attempts-id', 'all-attempts');

        $form->number('amount_try_zone', 'Попытки на зону')
            ->placeholder('..')
            ->attribute('id', 'amount_try_zone')
            ->attribute('data-amount-try-zone', 'amount_try_zone');

        $form->number('amount_try_top', 'Попытки на топ')
            ->placeholder('..')
            ->attribute('id', 'amount_try_top')
            ->attribute('data-amount-try-top', 'amount_try_top');

        $form->disableSubmit();
        $form->disableReset();
        $script_custom = <<<EOT
                            async function set_attempt() {
                                const routeId = $('[data-route-id=route_id]').val(); // ID выбранного маршрута
                                const userId = $('[data-user-id=user_id]').select2('val'); // ID выбранного участника
                                const eventId = document.querySelector('[data-gender-id="gender"]').getAttribute('data-event-id'); // ID события
                                const attempt = Number($('[data-all-attempts-id=all-attempts]').val());
                                let amount_try_top = $('[id=amount_try_top]').val();
                                let amount_try_zone = $('[id=amount_try_zone]').val();

                                const missingFields = [];
                                if (!attempt) missingFields.push('Попытки');
                                if (!userId) missingFields.push('Участник');
                                if (!routeId) missingFields.push('Трасса');

                                if (missingFields.length > 0) {
                                    return $.admin.toastr.error(
                                        'Не хватает данных: ' + missingFields.join(', '),
                                        '',
                                        { positionClass: "toast-bottom-center", timeOut: 1000 }
                                    ).css("width", "200px");
                                }

                                // Если значение amount_try_top есть, но amount_try_zone отсутствует, приравниваем их
                                if (Number(amount_try_top) > 0 && Number(amount_try_zone) == 0) {
                                    amount_try_zone = amount_try_top;
                                }
                                const urlSetAttempts =  document.querySelector('[data-gender-id="gender"]').getAttribute('data-set-url');
                                if (routeId) {
                                    try {
                                        const response = await $.get(urlSetAttempts, {
                                            route_id: routeId,
                                            user_id: userId,
                                            event_id: eventId,
                                            attempt: attempt,
                                            amount_try_top: amount_try_top,
                                            amount_try_zone: amount_try_zone
                                        });

                                        $.admin.toastr.success('Сохранено', '', { positionClass: "toast-bottom-center", timeOut: 1000 }).css("width", "200px");

                                        $('[data-amount-try-top=amount_try_top]').val(response.amount_try_top);
                                        $('[data-amount-try-zone=amount_try_zone]').val(response.amount_try_zone);
                                        $('[data-all-attempts-id=all-attempts]').val(response.all_attempts);

                                    } catch (error) {
                                        console.error("Ошибка при выполнении запроса:", error);
                                        $.admin.toastr.error('Ошибка при сохранении данных', '', { positionClass: "toast-bottom-center", timeOut: 1000 });
                                    }
                                }
                            }

                            $(document).on("change", '[data-user-id="user_id"]', function () {
                                var routeId = $('[data-route-id=route_id]').val();
                                var userId = $('[data-user-id="user_id"]').select2('val')
                                var eventId = document.querySelector('[data-gender-id="gender"]').getAttribute('data-event-id');
                                const urlGetAttempts = document.querySelector('[data-gender-id="gender"]').getAttribute('data-get-url')
                                if(routeId){
                                    $.get(urlGetAttempts,
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
                                var eventId = document.querySelector('[data-gender-id="gender"]').getAttribute('data-event-id'); // ID выбранного участника
                                const urlGetAttempts = document.querySelector('[data-gender-id="gender"]').getAttribute('data-get-url')
                                $.get(urlGetAttempts, // URL эндпоинта
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
                                var eventId = document.querySelector('[data-gender-id="gender"]').getAttribute('data-event-id'); // ID выбранного участника
                                $('[data-user-id=user_id]').val(''); // ID выбранного участника
                                var stage = document.querySelector('[data-gender-id="gender"]').getAttribute('data-stage');
                                $.get("/admin/api/get_users",
                                    {eventId: eventId, categoryId: categoryId, stage: stage},
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
                                var eventId = document.querySelector('[data-gender-id="gender"]').getAttribute('data-event-id');
                                var stage = document.querySelector('[data-gender-id="gender"]').getAttribute('data-stage');
                                $.get("/admin/api/get_users",
                                    {eventId: eventId, numberSetId: numberSetId, categoryId: categoryId, gender: gender, stage: stage},
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
                                var eventId = document.querySelector('[data-gender-id="gender"]').getAttribute('data-event-id');
                                var stage = document.querySelector('[data-gender-id="gender"]').getAttribute('data-stage');
                                $.get("/admin/api/get_users",
                                    {eventId: eventId, numberSetId: numberSetId, categoryId: categoryId, stage: stage},
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
                           $(document).ready(function() {
                                const inputField = document.getElementById('all_attempts');
                                const plusButton = inputField.parentNode.querySelector('.btn-success');
                                const minusButton = inputField.parentNode.querySelector('.btn-primary');
                                plusButton.classList.remove('btn-success');
                                plusButton.classList.add('btn-warning');
                                const attemptLabel = document.createElement('span');
                                attemptLabel.className = 'input-group-text'; // Добавить класс для стилей
                                if (plusButton) {
                                    plusButton.textContent = 'Попытка';
                                    plusButton.setAttribute('id' ,'increment-try-btn');// Установите желаемый текст
                                }
                                if(minusButton){
                                    minusButton.setAttribute('id' ,'decrement-try-btn');// Установите желаемый текст
                                }
                                plusButton.parentNode.appendChild(attemptLabel);
                            });

                           $(document).ready(function() {
                                const inputField = document.getElementById('amount_try_zone');
                                const plusButton = inputField.parentNode.querySelector('.btn-success');
                                const minusButton = inputField.parentNode.querySelector('.btn-primary');
                                const attemptLabel = document.createElement('span');
                                attemptLabel.className = 'input-group-text'; // Добавить класс для стилей
                                if (plusButton) {
                                    plusButton.textContent = 'ЗОНА';
                                    plusButton.setAttribute('id' ,'increment-zone-btn');// Установите желаемый текст
                                }
                                if(minusButton){
                                    minusButton.setAttribute('id' ,'decrement-zone-btn');// Установите желаемый текст
                                }
                                plusButton.parentNode.appendChild(attemptLabel);
                            });
                            $(document).ready(function() {
                                const inputField = document.getElementById('amount_try_top');
                                const plusButton = inputField.parentNode.querySelector('.btn-success');
                                const minusButton = inputField.parentNode.querySelector('.btn-primary');
                                const attemptLabel = document.createElement('span');
                                attemptLabel.className = 'input-group-text'; // Добавить класс для стилей
                                if (plusButton) {
                                    plusButton.textContent = 'ТОП'; // Установите желаемый текст
                                    plusButton.setAttribute('id' ,'increment-top-btn'); // Установите желаемый текст
                                }
                                if(minusButton){
                                    minusButton.setAttribute('id' ,'decrement-top-btn');// Установите желаемый текст
                                }
                                plusButton.parentNode.appendChild(attemptLabel);
                            });
                            $(document).ready(function() {
                                const increment_zone_btn = document.querySelector('#increment-zone-btn')
                                const decrement_zone_btn = document.querySelector('#decrement-zone-btn')
                                const increment_top_btn = document.querySelector('#increment-top-btn')
                                const decrement_top_btn = document.querySelector('#decrement-top-btn')
                                const increment_try_btn = document.querySelector('#increment-try-btn')
                                const decrement_try_btn = document.querySelector('#decrement-try-btn')
                                increment_zone_btn.addEventListener('click', function () {
                                    let attempt = $('[data-all-attempts-id=all-attempts]').val()
                                    $('[id=amount_try_zone]').val(attempt)
                                    set_attempt()
                                });
                                decrement_zone_btn.addEventListener('click', function () {
                                    set_attempt()
                                });
                                increment_top_btn.addEventListener('click', function () {
                                    let attempt = $('[data-all-attempts-id=all-attempts]').val()
                                    $('[id=amount_try_top]').val(attempt)
                                    set_attempt()
                                });
                                decrement_top_btn.addEventListener('click', function () {
                                    set_attempt()
                                });
                                increment_try_btn.addEventListener('click', function () {
                                    let attempt = $('[data-all-attempts-id=all-attempts]').val()
                                    if(Number(attempt) === 0){
                                        $('[data-all-attempts-id=all-attempts]').val(1)
                                    }
                                    set_attempt()
                                });
                                decrement_try_btn.addEventListener('click', function () {
                                    set_attempt()
                                });

                            });

                    EOT;
        Admin::script($script_custom);
        return $form;
    }
}
