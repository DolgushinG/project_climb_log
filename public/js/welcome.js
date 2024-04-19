/******/ (() => { // webpackBootstrap
var __webpack_exports__ = {};
/*!*********************************!*\
  !*** ./resources/js/welcome.js ***!
  \*********************************/
$(document).on('click', '#btn-participant', function (e) {
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });
  var value = document.getElementById("floatingSelect").value;
  var category_value = document.getElementById("floatingSelectCategory").value;
  var gender_value = document.getElementById("floatingSelectGender").value;
  if (document.getElementById("floatingSelectSportCategory")) {
    var sport_category_value = document.getElementById("floatingSelectSportCategory").value;
  }
  if (document.getElementById("birthday")) {
    var birthday_value = document.getElementById("birthday").value;
  }
  var button = $('#btn-participant');
  var event_id = document.getElementById('btn-participant').getAttribute('data-id');
  var link = document.getElementById('btn-participant').getAttribute('data-link');
  var is_qualification_counting_like_final = document.getElementById('btn-participant').getAttribute('data-format');
  var user_id = document.getElementById('btn-participant').getAttribute('data-user-id');
  e.preventDefault();
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
      'birthday': birthdayValue
    },
    success: function success(xhr, status, error) {
      button.text('').append('<i id="spinner" style="margin-left: -12px;\n' + '    margin-right: 8px;" class="fa fa-spinner fa-spin"></i> Обработка...');
      setTimeout(function () {
        button.text(xhr.message);
      }, 3000);
      setTimeout(function () {
        button.text('Оплатить');
        button.removeClass('btn btn-dark rounded-pill');
        button.attr('id', '#btn');
        button.addClass('btn btn-warning rounded-pill');
        button.attr('data-bs-toggle', 'modal');
        button.attr('data-bs-target', '#payModal');
      }, 6000);
    },
    error: function error(xhr, status, _error) {
      button.text('').append('<i id="spinner" style="margin-left: -12px;\n' + '    margin-right: 8px;" class="fa fa-spinner fa-spin"></i> Обработка...');
      setTimeout(function () {
        button.removeClass('btn-save-change');
        button.addClass('btn-failed-change');
        button.text(xhr.message);
      }, 3000);
      setTimeout(function () {
        button.removeClass('btn-failed-change');
        button.addClass('btn-save-change');
        button.text('Участвовать');
      }, 6000);
    }
  });
});
$(document).on('click', '#btn-participant-change-set', function (e) {
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });
  var value = document.getElementById("floatingSelectChangeSet").value;
  var button = $('#btn-participant-change-set');
  var event_id = document.getElementById('btn-participant-change-set').getAttribute('data-id');
  var user_id = document.getElementById('btn-participant-change-set').getAttribute('data-user-id');
  e.preventDefault();
  $.ajax({
    type: 'POST',
    url: '/changeSet',
    data: {
      'number_set': value,
      'event_id': event_id,
      'user_id': user_id
    },
    success: function success(xhr, status, error) {
      button.text('').append('<i id="spinner" style="margin-left: -12px;\n' + '    margin-right: 8px;" class="fa fa-spinner fa-spin"></i> Обработка...');
      setTimeout(function () {
        button.text(xhr.message);
      }, 3000);
      setTimeout(function () {
        button.text('Сет изменен');
      }, 6000);
      setTimeout(function () {
        button.text('Изменить сет');
      }, 6000);
    },
    error: function error(xhr, status, _error2) {
      button.text('').append('<i id="spinner" style="margin-left: -12px;\n' + '    margin-right: 8px;" class="fa fa-spinner fa-spin"></i> Обработка...');
      setTimeout(function () {
        button.removeClass('btn-save-change');
        button.addClass('btn-failed-change');
        button.text(xhr.message);
      }, 3000);
      setTimeout(function () {
        button.removeClass('btn-failed-change');
        button.addClass('btn-save-change');
        button.text('Изменить сет');
      }, 6000);
    }
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
/******/ })()
;