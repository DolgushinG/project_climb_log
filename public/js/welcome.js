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
  var sets = document.getElementById("floatingSelect");
  var category = document.getElementById("floatingSelectCategory");
  var sport_category = document.getElementById("floatingSelectSportCategory");
  var birthday = document.getElementById("birthday");
  var gender = document.getElementById("floatingSelectGender");

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
      button.text('').append('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Обработка...');
      setTimeout(function () {
        button.text(xhr.message);
      }, 3000);
      setTimeout(function () {
        window.location.reload();
        // getInfoPayment(event_id, '#payment')
        // getInfoPaymentBll(event_id, '#paymentTab')
        // button.text('Оплатить')
        // button.removeClass('btn btn-dark rounded-pill')
        // button.attr('id', '#btn')
        // button.addClass('btn btn-warning rounded-pill')
        // button.attr('data-bs-toggle', 'modal')
        // button.attr('data-bs-target', '#scrollingModal')
      }, 6000);
    },
    error: function error(xhr, status, _error) {
      button.text('').append('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Обработка...');
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
function getInfoPayment(event_id, id) {
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });
  $.ajax({
    type: 'GET',
    url: 'getInfoPayment/' + event_id,
    success: function success(data) {
      $(id).html(data);
    }
  });
}
function getInfoPaymentBll(event_id, id) {
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });
  $.ajax({
    type: 'GET',
    url: 'getInfoPaymentBill/' + event_id,
    success: function success(data) {
      $(id).html(data);
    }
  });
}
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
$(document).on('click', '#send-all-result', function (e) {
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });
  var button = $('#send-all-result');
  var email = document.getElementById('allResultFloatingEmail').value;
  var event_id = document.getElementById('allResultFloatingEmail').getAttribute('data-event-id');
  e.preventDefault();
  $.ajax({
    type: 'POST',
    url: 'sendAllResult',
    data: {
      'event_id': event_id,
      'email': email
    },
    success: function success(xhr, status, error) {
      button.text('').append('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Обработка...');
      button.attr("disabled", true);
      setTimeout(function () {
        button.text(xhr.message);
        button.attr("disabled", true);
      }, 3000);
      setTimeout(function () {
        button.text('Отправлено');
        button.attr("disabled", true);
      }, 6000);
      setTimeout(function () {
        button.text('Отравить');
        button.removeAttr("disabled");
      }, 6000);
    },
    error: function error(xhr, status, _error3) {
      button.text('').append('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Обработка...');
      button.attr("disabled", true);
      setTimeout(function () {
        button.text(xhr.message);
      }, 3000);
      setTimeout(function () {
        button.text('Отправить');
        button.removeAttr("disabled");
      }, 6000);
    }
  });
});
var $modal = $('#modal');
var image = document.getElementById('image');
var cropper;
$("body").on("change", ".image", function (e) {
  var files = e.target.files;
  var done = function done(url) {
    image.src = url;
    $modal.modal('show');
  };
  var reader;
  var file;
  var url;
  if (files && files.length > 0) {
    file = files[0];
    if (URL) {
      done(URL.createObjectURL(file));
    } else if (FileReader) {
      reader = new FileReader();
      reader.onload = function (e) {
        done(reader.result);
      };
      reader.readAsDataURL(file);
    }
  }
});
$modal.on('shown.bs.modal', function () {
  cropper = new Cropper(image, {
    autoCrop: true,
    autoCropArea: 1,
    minContainerHeight: 400,
    minContainerWidth: 400,
    minCanvasWidth: 400,
    minCanvasHeight: 400,
    aspectRatio: 500 / 660,
    minCropBoxWidth: 500,
    minCropBoxHeight: 660,
    viewMode: 2,
    preview: '.preview'
  });
}).on('hidden.bs.modal', function () {
  cropper.destroy();
  cropper = null;
});
$("#crop").click(function () {
  canvas = cropper.getCroppedCanvas({
    width: 500,
    height: 600
  });
  canvas.toBlob(function (blob) {
    url = URL.createObjectURL(blob);
    var reader = new FileReader();
    reader.readAsDataURL(blob);
    reader.onloadend = function () {
      var base64data = reader.result;
      var block_attach_bill = document.getElementById('attachBill');
      var event_id = document.getElementById('attachBill').getAttribute('data-event-id');
      var block_checking_bill = document.getElementById('checkingBill');
      var button_pay = $('#btn-payment');
      $.ajax({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        type: "POST",
        dataType: "json",
        url: "/cropimageupload",
        data: {
          'image': base64data,
          'event_id': event_id
        },
        success: function success(data) {
          $modal.modal('hide');
          getInfoPaymentBll(event_id, '#paymentTab');
          button_pay.text('Чек отправлен (На проверке..)');
          button_pay.attr('disabled', 'disabled');
          block_attach_bill.style.display = 'none';
          setTimeout(function () {
            block_checking_bill.style.display = 'block';
          }, 1000);
        },
        error: function error(xhr, status, _error4) {
          $modal.modal('hide');
        }
      });
    };
  });
});
$(document).ready(function () {
  $(document).on('click', '#modalclose', function (e) {
    $modal.modal('hide');
  });
});
/******/ })()
;