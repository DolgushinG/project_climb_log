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
            document.getElementById("error-message").innerText = "Пожалуйста выберите сет для участия, если сеты заняты добавьтесь в лист ожидания";
            document.getElementById("error-message").style.display = "block";
            return; // Прерываем выполнение функции
        }
    }

    if (birthday) {
        // Получаем значение выбранного элемента
        var birthdayValue = birthday.value.trim();

        // Проверяем, что значение не пустое
        if (birthdayValue === "") {
            // Выводим сообщение об ошибке
            document.getElementById("error-message").innerText = "Пожалуйста укажите дату рождения для участия";
            document.getElementById("error-message").style.display = "block";
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
            document.getElementById("error-message").style.display = "block";
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
            document.getElementById("error-message").style.display = "block";
            return; // Прерываем выполнение функции
        }
    }
    if (gender) {
        // Получаем значение выбранного элемента
        var genderValue = gender.value.trim();

        // Проверяем, что значение не пустое
        if (genderValue === "") {
            // Выводим сообщение об ошибке
            document.getElementById("error-message").innerText = "Пожалуйста укажите ваш пол для участия";
            document.getElementById("error-message").style.display = "block";
            return; // Прерываем выполнение функции
        }
    }
    // Если все селекты заполнены, очищаем сообщение об ошибке
    document.getElementById("error-message").innerText = "";
    document.getElementById("error-message").style.display = "none";

    let button = $('#btn-participant')
    let event_id = document.getElementById('btn-participant').getAttribute('data-id')

    let link = document.getElementById('btn-participant').getAttribute('data-link')
    let is_france_system_qualification = document.getElementById('btn-participant').getAttribute('data-format')
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
            button.text('').append('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Обработка...')

            setTimeout(function () {
                button.text(xhr.message)
            }, 1500);
            setTimeout(function () {
                window.location.reload();
            }, 3000);
        },
        error: function(xhr, status, error) {

            button.text('').append('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Обработка...')
            setTimeout(function () {
                button.addClass('btn-failed-change')
                button.text(xhr.responseJSON.message)
            }, 1500);
            setTimeout(function () {
                button.removeClass('btn-failed-change')
                button.text('Участвовать')
            }, 3000);

        },

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
        success: function (data) {
            $(id).html(data);
        },
    });
}
function getInfoPay(event_id, id) {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $.ajax({
        type: 'GET',
        url: 'getInfoPay/' + event_id,
        success: function (data) {
            $(id).html(data);
        },
    });
}
function getInfoPaymentDocument(event_id, id) {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $.ajax({
        type: 'GET',
        url: 'getInfoPaymentDocument/' + event_id,
        success: function (data) {
            $(id).html(data);
        },
    });
}
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
            }, 1000);
            setTimeout(function () {
                button.text('Сет изменен')
            }, 2000);
            setTimeout(function () {
                window.location.reload();
            }, 3000);
        },
        error: function(xhr, status, error) {
            button.text('').append('<i id="spinner" class="fa fa-spinner fa-spin"></i> Обработка...')
            setTimeout(function () {
                button.text(xhr.responseJSON.message)
            }, 3000);
            setTimeout(function () {
                button.text('Изменить сет')
            }, 6000);

        },

    });
});
$(document).on('click','#save_products_discount', function(e) {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    let products = Array();
    let event_id = document.getElementById('save_products_discount').getAttribute('data-id')
    let amount_start_price = document.getElementById('price-value').textContent
    let button = $('#save_products_discount')
    let user_id = document.getElementById('save_products_discount').getAttribute('data-user_id')
    const productsContainer = document.getElementById('products');
    const discountsContainer = document.getElementById('discounts');
    const productCheckboxes = productsContainer.querySelectorAll('input[type="checkbox"]');
    productCheckboxes.forEach(checkbox => {
        if (checkbox.checked) {
            products.push(checkbox.getAttribute('data-name'))
        }
    });
    e.preventDefault()
    $.ajax({
        type: 'POST',
        url: '/sendProductsAndDiscount',
        data: {
            'event_id': event_id,
            'user_id': user_id,
            'amount_start_price': amount_start_price,
            'discount': discountsContainer.options[discountsContainer.selectedIndex].getAttribute('data-name'),
            'products': products,
        },
        success: function(xhr, status, error) {
            button.attr('disabled','disabled');
            button.text('').append('<i id="spinner" class="fa fa-spinner fa-spin"></i> Обработка...')
            setTimeout(function () {
                button.text(xhr.message)
                button.attr('disabled','disabled');
            }, 1000);
            setTimeout(function () {
                button.text('Сохранить выбранное')
            }, 2000);
            setTimeout(function () {
                button.removeAttr('disabled','disabled');
            }, 3000);

        },
        error: function(xhr, status, error) {
            button.text('').append('<i id="spinner" class="fa fa-spinner fa-spin"></i> Обработка...')
            setTimeout(function () {
                button.text(xhr.responseJSON.message)
            }, 3000);
            setTimeout(function () {
                button.text('Сохранить выбранное')
            }, 6000);

        },

    });
});
$(document).on('click','#add-to-list-pending', function(e) {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    let category = document.getElementById("floatingSelectCategoryModal");
    let sport_category = document.getElementById("floatingSelectSportCategoryModal");
    let birthday = document.getElementById("birthdayModal");
    let gender = document.getElementById("floatingSelectGenderModal");

    if (category) {
        var categoryValue = category.value.trim();
        if (categoryValue === "") {
            document.getElementById("error-message-modal").innerText = "Пожалуйста выберите категорию для добавление в лист ожидания";
            document.getElementById("error-message-modal").style.display = "block";
            return; // Прерываем выполнение функции
        }
    }
    let checkboxes = [...document.querySelectorAll('#list_pending .form-check-input:checked')].map(e => e.checked);
    if (checkboxes.length === 0) {
        document.getElementById("error-message-modal").innerText = "Пожалуйста выберите сет";
        document.getElementById("error-message-modal").style.display = "block";
        return; // Прерываем выполнение функции
    }
    console.log(birthday)
    if (birthday) {
        // Получаем значение выбранного элемента
        var birthdayValue = birthday.value.trim();

        // Проверяем, что значение не пустое
        if (birthdayValue === "") {
            // Выводим сообщение об ошибке
            document.getElementById("error-message-modal").innerText = "Пожалуйста укажите дату рождения для участия";
            document.getElementById("error-message-modal").style.display = "block";
            return; // Прерываем выполнение функции
        }
    }

    if (sport_category) {
        // Получаем значение выбранного элемента
        var sportCategoryValue = sport_category.value.trim();

        // Проверяем, что значение не пустое
        if (sportCategoryValue === "") {
            // Выводим сообщение об ошибке
            document.getElementById("error-message-modal").innerText = "Пожалуйста выберите разряд для участия";
            document.getElementById("error-message-modal").style.display = "block";
            return; // Прерываем выполнение функции
        }
    }
    if (gender) {
        // Получаем значение выбранного элемента
        var genderValue = gender.value.trim();

        // Проверяем, что значение не пустое
        if (genderValue === "") {
            // Выводим сообщение об ошибке
            document.getElementById("error-message-modal").innerText = "Пожалуйста укажите ваш пол для участия";
            document.getElementById("error-message-modal").style.display = "block";
            return; // Прерываем выполнение функции
        }
    }
    const data = [...document.querySelectorAll('.form-check-input:checked')].map(e => e.value);
    let button = $('#add-to-list-pending')
    button.attr("disabled", true);
    let button_close = $('#list-pending-close')
    let event_id = document.getElementById('add-to-list-pending').getAttribute('data-event-id')
    let user_id = document.getElementById('add-to-list-pending').getAttribute('data-user-id')
    e.preventDefault()
    $.ajax({
        type: 'POST',
        url: '/addToListPending',
        data: {
            'category': categoryValue,
            'number_sets': data,
            'event_id': event_id,
            'user_id': user_id,
            'gender': genderValue,
            'sport_category': sportCategoryValue,
            'birthday': birthdayValue,
        },
        success: function(xhr, status, error) {
            document.getElementById("error-message-modal").style.display = "none";
            button.text('').append('<i id="spinner" class="fa fa-spinner fa-spin ml-2"></i> Обработка...')
            setTimeout(function () {
                button.text(xhr.message)
            }, 3000);
            setTimeout(function () {
                window.location.reload();
            }, 6000);
        },
        error: function(xhr, status, error) {
            button.text('').append('<i id="spinner" class="fa fa-spinner fa-spin"></i> Обработка...')
            setTimeout(function () {
                button.removeClass('btn-save-change')
                button.addClass('btn-failed-change')
                button.text(xhr.responseJSON.message)
            }, 3000);
            setTimeout(function () {
                button.removeClass('btn-failed-change')
                button.addClass('btn-save-change')
                button.text('Добавить')
            }, 6000);

        },

    });
});
$(document).on('click','#btn_cancel_take_part_participant', function(e) {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    let btn_cancel_take_part_participant =  document.getElementById('btn_cancel_take_part_participant')
    let qbtn_cancel_take_part_participant = $('#btn_cancel_take_part_participant')
    let event_id = btn_cancel_take_part_participant.getAttribute('data-event-id')
    let user_id = btn_cancel_take_part_participant.getAttribute('data-user-id')
    e.preventDefault()
    $.ajax({
        type: 'POST',
        url: '/cancelTakePartParticipant',
        data: {
            'event_id': event_id,
            'user_id': user_id
        },
        success: function(xhr, status, error) {
            qbtn_cancel_take_part_participant.text('').append('<i id="spinner" class="fa fa-spinner fa-spin ml-2"></i> Обработка...')
            setTimeout(function () {
                qbtn_cancel_take_part_participant.text(xhr.message)
            }, 2000);
            setTimeout(function () {
                qbtn_cancel_take_part_participant.attr("disabled", false);
                window.location.reload();
            }, 3000);
        },
        error: function(xhr, status, error) {
            qbtn_cancel_take_part_participant.text('').append('<i id="spinner" class="fa fa-spinner fa-spin"></i> Обработка...')
            setTimeout(function () {
                qbtn_cancel_take_part_participant.text(xhr.responseJSON.message)
            }, 3000);
            setTimeout(function () {
                qbtn_cancel_take_part_participant.text('Отменить регистрацию')
            }, 6000);

        },

    });
});
$(document).on('click','#add-to-list-pending-remove', function(e) {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    let button_close = $('#list-pending-close')
    let button_remove = $('#add-to-list-pending-remove')
    let event_id = document.getElementById('add-to-list-pending').getAttribute('data-event-id')
    let user_id = document.getElementById('add-to-list-pending').getAttribute('data-user-id')
    e.preventDefault()
    $.ajax({
        type: 'POST',
        url: '/removeFromListPending',
        data: {
            'event_id': event_id,
            'user_id': user_id
        },
        success: function(xhr, status, error) {
            document.getElementById("error-message-modal").style.display = "none";
            button_remove.text('').append('<i id="spinner" class="fa fa-spinner fa-spin ml-2"></i> Обработка...')
            setTimeout(function () {
                button_remove.text(xhr.message)
            }, 2000);
            setTimeout(function () {
                button_remove.attr("disabled", false);
                window.location.reload();
            }, 3000);
        },
        error: function(xhr, status, error) {
            button.text('').append('<i id="spinner" class="fa fa-spinner fa-spin"></i> Обработка...')
            setTimeout(function () {
                button.text(xhr.responseJSON.message)
            }, 3000);
            setTimeout(function () {
                button.text('Убрать из листа ожидания')
            }, 6000);

        },

    });
});
$(document).on('click','#send-all-result', function(e) {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    let button = $('#send-all-result')
    let button_close = $('#send-all-result-close')
    let email = document.getElementById('allResultFloatingEmail').value
    let event_id = document.getElementById('allResultFloatingEmail').getAttribute('data-event-id')
    e.preventDefault()
    $.ajax({
        type: 'POST',
        url: 'sendAllResult',
        data: {
            'event_id': event_id,
            'email': email,
        },
        success: function(xhr, status, error) {
            button.text('').append('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Обработка...')
            button.attr("disabled", true);
            setTimeout(function () {
                button.text(xhr.message)
                button.attr("disabled", true);
            }, 3000);
            setTimeout(function () {
                button.text('Отправлено')
                button.attr("disabled", true);
                button_close.click()
            }, 6000);

        },
        error: function(xhr, status, error) {
            button.text('').append('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Обработка...')
            button.attr("disabled", true);
            setTimeout(function () {
                button.text(xhr.message)
            }, 3000);
            setTimeout(function () {
                button.text('Отправить')
                button.removeAttr("disabled");
            }, 6000);

        },

    });
});

var $modal = $('#modal');
var $modal_document = $('#modal-document');
var image = document.getElementById('image');
var image_document = document.getElementById('image-document');
var cropper;
$("body").on("change", ".imageBill", function (e) {
    var files = e.target.files;
    var done = function (url) {
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
$("body").on("change", ".imageDocument", function (e) {
    var files = e.target.files;
    var done = function (url) {
        image_document.src = url;
        $modal_document.modal('show');
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
$modal_document.on('shown.bs.modal', function () {
    cropper = new Cropper(image_document, {
        autoCrop: true,
        autoCropArea: 1,
        minContainerHeight  : 400,
        minContainerWidth   : 400,
        minCanvasWidth      : 400,
        minCanvasHeight     : 400,
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
$modal.on('shown.bs.modal', function () {
    cropper = new Cropper(image, {
        autoCrop: true,
        autoCropArea: 1,
        minContainerHeight  : 400,
        minContainerWidth   : 400,
        minCanvasWidth      : 400,
        minCanvasHeight     : 400,
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
        height: 600,
    });
    canvas.toBlob(function (blob) {
        url = URL.createObjectURL(blob);
        var reader = new FileReader();
        reader.readAsDataURL(blob);
        reader.onloadend = function () {
            var base64data = reader.result;
            var block_attach_bill = document.getElementById('attachBill')
            var btn_cancel_take_part = document.getElementById('btn_cancel_take_part')
            var event_id = document.getElementById('attachBill').getAttribute('data-event-id')
            let button_pay = $('#btn-payment')
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: "POST",
                dataType: "json",
                url: "/cropimageupload",
                data: {'image': base64data , 'event_id': event_id},
                success: function (data) {
                    $modal.modal('hide');
                    window.location.reload();
                    // getInfoPay(event_id, '#paymentTab')
                    // button_pay.text('Чек отправлен (На проверке..)')
                    // button_pay.attr('disabled', 'disabled')
                    // if(btn_cancel_take_part){
                    //     btn_cancel_take_part.style.display = 'none';
                    // }
                    // block_attach_bill.style.display = 'none';
                    // document.querySelector('#bill').style.display = 'None'
                    // document.getElementById('checkingBillTab').style.display = 'block';
                },
                error: function (xhr, status, error) {
                    $modal.modal('hide');
                }
            });
        }
    });
})

$("#crop-document").click(function () {
    canvas = cropper.getCroppedCanvas({
        width: 500,
        height: 600,
    });
    canvas.toBlob(function (blob) {
        url = URL.createObjectURL(blob);
        var reader = new FileReader();
        reader.readAsDataURL(blob);
        reader.onloadend = function () {
            var base64data = reader.result;
            var block_attach_document = document.getElementById('attachDocument')
            var btn_cancel_take_part = document.getElementById('btn_cancel_take_part')
            var event_id = document.getElementById('attachDocument').getAttribute('data-event-id')
            var block_checking_document = document.getElementById('checkingDocument')
            let button_pay = $('#btn-payment')
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: "POST",
                dataType: "json",
                url: "/cropdocumentupload",
                data: {'image': base64data , 'event_id': event_id},
                success: function (data) {
                    $modal_document.modal('hide');
                    window.location.reload();

                },
                error: function (xhr, status, error) {
                    $modal_document.modal('hide');
                }
            });
        }
    });
})
$(document).ready(function () {
    $(document).on('click', '#modalclose', function (e) {
        $modal.modal('hide');
    })
    $(document).on('change', '#floatingSelect', function (e) {
        let free = this.options[this.selectedIndex].getAttribute('data-free')
        if (free > 0) {
            let btn_participant = $('#btn-participant')
            btn_participant.attr('disabled', false)
            btn_participant.text('Участвовать')
        }
    })
    $(document).on('change', '#floatingSelectChangeSet', function (e) {
        let free = this.options[this.selectedIndex].getAttribute('data-free')
        let data_set = this.options[this.selectedIndex].getAttribute('data-set')
        if(data_set === "current"){
            let btn_participant_change_set = $('#btn-participant-change-set')
            btn_participant_change_set.attr('disabled', true)
            btn_participant_change_set.text('Вы уже в этом сете')
        } else {
            if(free == null){
                let btn_participant_change_set = $('#btn-participant-change-set')
                btn_participant_change_set.attr('disabled', false)
                btn_participant_change_set.text('Изменить сет')
            }
            if(free > 0) {
                let btn_participant_change_set = $('#btn-participant-change-set')
                btn_participant_change_set.attr('disabled', false)
                btn_participant_change_set.text('Изменить сет')
            } else {
                let btn_participant_change_set = $('#btn-participant-change-set')
                btn_participant_change_set.attr('disabled', true)
                btn_participant_change_set.text('Этот сет заполнен')
            }
        }

    })
});





