function set_attempt(){
    var routeId = $('[data-category-route-id=route_id]').val(); // ID выбранного маршрута
    var userId = $('[data-category-user-id="user_id"]').select2('val')
    var eventId = $('[data-category-event-id=event_id]').val(); // ID выбранного участника
    var attempt = $('[data-all-attempts-id=all-attempts]').val();
    var amount_try_top = $('[id=amount_try_top_category]').val();
    var amount_try_zone = $('[id=amount_try_zone_category]').val();
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
    // Проверяем, существуют ли уже кнопки
    const incrementBtn = document.getElementById('increment-btn');
    const decrementBtn = document.getElementById('decrement-btn');
    const zoneBtn = document.getElementById('zone-btn');
    const topBtn = document.getElementById('top-btn');
    if (!incrementBtn && !decrementBtn && !zoneBtn && !topBtn) {
        // Создаем элемент для группы ввода
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
        newZoneBtn.id = 'zone-btn';
        const newTopBtn = document.createElement('button');
        newTopBtn.type = 'button';
        newTopBtn.className = 'btn btn-success';
        newTopBtn.id = 'zone-btn';
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
            $('[id=amount_try_zone_category]').val(currentValue);
            set_attempt()
        });
        newTopBtn.addEventListener('click', function () {
            let currentValue = parseInt(allAttemptsInput.value) || 0;
            $('[id=amount_try_top_category]').val(currentValue);
            set_attempt()
        });
        // Обработчик клика на кнопку увеличения
        newIncrementBtn.addEventListener('click', function () {
            let currentValue = parseInt(allAttemptsInput.value) || 0;
            allAttemptsInput.value = currentValue + 1;
            set_attempt()
        });

        // Обработчик клика на кнопку удаления
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

    // Выполняем AJAX-запрос к эндпоинту для получения данных о попытках
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
