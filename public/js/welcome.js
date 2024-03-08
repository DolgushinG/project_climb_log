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
  var event_title = document.getElementById('btn-participant').getAttribute('data-title');
  var user_id = document.getElementById('btn-participant').getAttribute('data-user-id');
  e.preventDefault();
  $.ajax({
    type: 'POST',
    url: '/takePart',
    data: {
      'number_set': value,
      'event_id': event_id,
      'user_id': user_id,
      'category': category_value,
      'gender': gender_value,
      'sport_category': sport_category_value,
      'birthday': birthday_value
    },
    success: function success(xhr, status, error) {
      button.text('').append('<i id="spinner" style="margin-left: -12px;\n' + '    margin-right: 8px;" class="fa fa-spinner fa-spin"></i> Обработка...');
      setTimeout(function () {
        button.text(xhr.message);
      }, 3000);
      setTimeout(function () {
        button.text('Внести результат');
        button.removeClass('btn btn-dark rounded-pill');
        button.addClass('btn btn-success rounded-pill');
        button.attr("id", "listRoutesEvent");
        document.getElementById("listRoutesEvent").onclick = function () {
          location.href = "/routes/event/" + event_title + "/list-routes-event";
        };
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
document.getElementById("floatingSelect").addEventListener("change", function (ev) {
  var value = document.getElementById("floatingSelect").value;
  var c_value = document.getElementById("floatingSelectCategory").value;
  if (value !== "" && c_value !== "") {
    var button_paticipant = document.querySelector('#btn-participant');
    button_paticipant.style.display = 'block';
  }
});
document.getElementById("floatingSelectCategory").addEventListener("change", function (ev) {
  var value = document.getElementById("floatingSelect").value;
  var c_value = document.getElementById("floatingSelectCategory").value;
  if (value !== "" && c_value !== "") {
    var button_paticipant = document.querySelector('#btn-participant');
    button_paticipant.style.display = 'block';
  }
});
/******/ })()
;