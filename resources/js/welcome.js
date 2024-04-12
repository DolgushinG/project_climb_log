$(document).on('click','#btn-participant', function(e) {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    let sets = document.getElementById("floatingSelect");
    let category = document.getElementById("floatingSelectCategory");
    let sport_category = document.getElementById("floatingSelectSportCategory");
    let birthday = document.getElementById("birthday");
    let gender = document.getElementById("floatingSelectGender");

    // Проверяем, что каждый селект присутствует на странице
    if (sets) {
        // Получаем значение выбранного элемента
        var setsValue = sets.value.trim();

        // Проверяем, что значение не пустое
        if (setsValue === "") {
            // Выводим сообщение об ошибке
            document.getElementById("error-message").innerText = "Пожалуйста выберите сет для участия";
            return; // Прерываем выполнение функции
        }
    }

    if (birthday) {
        // Получаем значение выбранного элемента
        var birthdayValue = sets.value.trim();

        // Проверяем, что значение не пустое
        if (birthdayValue === "") {
            // Выводим сообщение об ошибке
            document.getElementById("error-message").innerText = "Пожалуйста установить дата рождения для участия";
            return; // Прерываем выполнение функции
        }
    }

    if (category) {
        // Получаем значение выбранного элемента
        var categoryValue = category.value.trim();

        // Проверяем, что значение не пустое
        if (categoryValue === "") {
            // Выводим сообщение об ошибке
            document.getElementById("error-message").innerText = "Пожалуйста выберите категорию для участия";
            return; // Прерываем выполнение функции
        }
    }

    if (sport_category) {
        // Получаем значение выбранного элемента
        var sportCategoryValue = sport_category.value.trim();

        // Проверяем, что значение не пустое
        if (sportCategoryValue === "") {
            // Выводим сообщение об ошибке
            document.getElementById("error-message").innerText = "Пожалуйста выберите разряд для участия";
            return; // Прерываем выполнение функции
        }
    }
    if (gender) {
        // Получаем значение выбранного элемента
        var genderValue = gender.value.trim();

        // Проверяем, что значение не пустое
        if (genderValue === "") {
            // Выводим сообщение об ошибке
            document.getElementById("error-message").innerText = "Пожалуйста выберите разряд для участия";
            return; // Прерываем выполнение функции
        }
    }
    // Если все селекты заполнены, очищаем сообщение об ошибке
    document.getElementById("error-message").innerText = "";

    let button = $('#btn-participant')
    let event_id = document.getElementById('btn-participant').getAttribute('data-id')
    let event_title = document.getElementById('btn-participant').getAttribute('data-title')
    let is_qualification_counting_like_final = document.getElementById('btn-participant').getAttribute('data-format')

    let user_id = document.getElementById('btn-participant').getAttribute('data-user-id')
    e.preventDefault()
    $.ajax({
        type: 'POST',
        url: '/takePart',
        data: {
            'number_set': setsValue,
            'event_id': event_id,
            'user_id': user_id,
            'category': categoryValue,
            'gender': genderValue,
            'sport_category': sportCategoryValue,
            'birthday': birthdayValue,
        },
        success: function(xhr, status, error) {
            button.text('').append('<i id="spinner" style="margin-left: -12px;\n' +
                '    margin-right: 8px;" class="fa fa-spinner fa-spin"></i> Обработка...')

            setTimeout(function () {
                button.text(xhr.message)
            }, 3000);
            setTimeout(function () {
                if(!Number(is_qualification_counting_like_final)){
                    button.text('Внести результат')
                    button.removeClass('btn btn-dark rounded-pill')
                    button.addClass('btn btn-success rounded-pill')
                    button.attr("id", "listRoutesEvent");
                    document.getElementById("listRoutesEvent").onclick = function () {
                        location.href = "/routes/event/"+event_title+"/list-routes-event";
                    };
                } else {
                    button.text('Вы принимаете участие')
                    button.removeClass('btn btn-dark rounded-pill')
                    button.addClass('btn btn-secondary rounded-pill')
                    button.attr('disabled', 'disabled')
                }
            }, 6000);
        },
        error: function(xhr, status, error) {

            button.text('').append('<i id="spinner" style="margin-left: -12px;\n' +
                '    margin-right: 8px;" class="fa fa-spinner fa-spin"></i> Обработка...')
            setTimeout(function () {
                button.removeClass('btn-save-change')
                button.addClass('btn-failed-change')
                button.text(xhr.message)
            }, 3000);
            setTimeout(function () {
                button.removeClass('btn-failed-change')
                button.addClass('btn-save-change')
                button.text('Участвовать')
            }, 6000);

        },

    });
});


$(document).on('click','#btn-participant-change-set', function(e) {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    var value = document.getElementById("floatingSelectChangeSet").value
    let button = $('#btn-participant-change-set')
    let event_id = document.getElementById('btn-participant-change-set').getAttribute('data-id')
    let user_id = document.getElementById('btn-participant-change-set').getAttribute('data-user-id')
    e.preventDefault()
    $.ajax({
        type: 'POST',
        url: '/changeSet',
        data: {
            'number_set': value,
            'event_id': event_id,
            'user_id': user_id,
        },
        success: function(xhr, status, error) {
            button.text('').append('<i id="spinner" style="margin-left: -12px;\n' +
                '    margin-right: 8px;" class="fa fa-spinner fa-spin"></i> Обработка...')

            setTimeout(function () {
                button.text(xhr.message)
            }, 3000);
            setTimeout(function () {
                button.text('Сет изменен')
            }, 6000);
            setTimeout(function () {
                button.text('Изменить сет')
            }, 6000);
        },
        error: function(xhr, status, error) {
            button.text('').append('<i id="spinner" style="margin-left: -12px;\n' +
                '    margin-right: 8px;" class="fa fa-spinner fa-spin"></i> Обработка...')
            setTimeout(function () {
                button.removeClass('btn-save-change')
                button.addClass('btn-failed-change')
                button.text(xhr.message)
            }, 3000);
            setTimeout(function () {
                button.removeClass('btn-failed-change')
                button.addClass('btn-save-change')
                button.text('Изменить сет')
            }, 6000);

        },

    });
});
// let sets = document.getElementById("floatingSelect");
// let category = document.getElementById("floatingSelectCategory");
// let sport_category = document.getElementById("floatingSelectSportCategory");
// if(sets){
//     document.getElementById("floatingSelect").addEventListener("change", (ev) => {
//         var value = document.getElementById("floatingSelect").value;
//         var c_value = document.getElementById("floatingSelectCategory").value;
//         if (value !== "" && c_value !== ""){
//             var button_paticipant = document.querySelector('#btn-participant');
//             button_paticipant.style.display = 'block';
//         }
//     });
// }
// if(category){
//     document.getElementById("floatingSelectCategory").addEventListener("change", (ev) => {
//         var select = document.getElementById("floatingSelect");
//         let is_input_set = document.getElementById('btn-participant').getAttribute('data-sets')
//         if(is_input_set === 1){
//             select.value = 'show_button'
//         }
//         var c_value = document.getElementById("floatingSelectCategory").value;
//         if (select !== "" && c_value !== ""){
//             var button_paticipant = document.querySelector('#btn-participant');
//             button_paticipant.style.display = 'block';
//         }
//     });
// }
// if(sport_category){
//     document.getElementById("floatingSelectSportCategory").addEventListener("change", (ev) => {
//         var value = document.getElementById("floatingSelect").value;
//         var c_value = document.getElementById("floatingSelectCategory").value;
//         var c_value_sport = document.getElementById("floatingSelectSportCategory").value;
//         if (value !== "" && c_value !== "" && c_value_sport !== ""){
//             var button_paticipant = document.querySelector('#btn-participant');
//             button_paticipant.style.display = 'block';
//         }
//     });
// }





