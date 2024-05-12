$('#tab-overview').addClass('show active');
getProfile('Overview');
$(document).on('click', '#overview', function () {
    deactivateAllTabs();
    $('#overview').addClass('show active');
    getProfile('Overview');
});
$(document).on('click', '#edit', function () {
    deactivateAllTabs();
    $('#edit').addClass('show active');
    getProfile('Edit');
});
$(document).on('click', '#events', function () {
    deactivateAllTabs();
    $('#events').addClass('show active');
    getProfile('Events');
});
$(document).on('click', '#setting', function () {
    deactivateAllTabs();
    $('#setting').addClass('active');
    getProfile('Setting');
});
function deactivateAllTabs() {
    $('#overview, #edit, #setting, #events').removeClass('show active');
}
function getProfile(tab, id='#tabContent') {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $.ajax({
        type: 'GET',
        url: 'getProfile' + tab,
        success: function (data) {
            $(id).html(data);
        },
    });
}
//
$(document).ready(function () {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $(document).on('click', '#saveChanges', function (e) {
        let btn_saveChanges = $('#saveChanges')
        let data = $("#editForm").serialize();
        e.preventDefault();
        let tab = 'Edit';
        $.ajax({
            type: 'POST',
            url: 'editChanges',
            data: data,
            success: function (data) {
                btn_saveChanges.removeClass('btn-save-change')
                btn_saveChanges.addClass('btn-edit-change')
                btn_saveChanges.text('').append('<i id="spinner" style="margin-left: -12px;\n' +
                    '    margin-right: 8px;" class="fa fa-spinner fa-spin"></i> Обработка...')
                setTimeout(function () {
                    btn_saveChanges.text(data.message)
                }, 3000);
                setTimeout(function () {
                    getProfile('Card', '#profileCard');
                    getProfile(tab);
                }, 4000);
            },
            error: function (xhr, status, error) {
                btn_saveChanges.text('').append('<i id="spinner" style="margin-left: -12px;\n' +
                    '    margin-right: 8px;" class="fa fa-spinner fa-spin"></i> Обработка...')
                setTimeout(function () {
                    btn_saveChanges.removeClass('btn-save-change')
                    btn_saveChanges.addClass('btn-failed-change')
                    btn_saveChanges.text(xhr.responseJSON.message[0])
                }, 3000);
                setTimeout(function () {
                    btn_saveChanges.removeClass('btn-failed-change')
                    btn_saveChanges.addClass('btn-save-change')
                    btn_saveChanges.text('Cохранить')
                }, 6000);
            }
        });
    });
});
$(document).on('click', '#changePassword', function(e){
    let btn_change_password = $('#changePassword')

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    var new_password = $('#new-password').val();
    var old_password = $('#current-password').val();
    var password_confirmation = $('#password_confirmation').val();

    e.preventDefault();
    var currentPassword = document.getElementById('current-password').value;
    var newPassword = document.getElementById('new-password').value;
    var passwordConfirmation = document.getElementById('password_confirmation').value;

    // Validation checks
    if (!currentPassword || !newPassword || !passwordConfirmation) {
        displayError("Пожалуйста заполните все поля");
        return;
    }

    if (newPassword !== passwordConfirmation) {
        displayError("Пароли не совпадают");
        return;
    }
    clearError();

    $.ajax({
        type: 'post',
        url: "/change-password",
        data:{
            old_password:old_password,
            new_password:new_password,
            password_confirmation:password_confirmation
        },
        cache: false,
        success: function (data){
            btn_change_password.removeClass('btn-save-change')
            btn_change_password.addClass('btn-edit-change')
            btn_change_password.text('').append('<i id="spinner" style="margin-left: -12px;\n' +
                '    margin-right: 8px;" class="fa fa-spinner fa-spin"></i> Обработка...')
            setTimeout(function () {
                btn_change_password.text(data.message)
            }, 3000);
            setTimeout(function () {
                btn_change_password.removeClass('btn-edit-change')
                btn_change_password.text('Cохранить')
                getProfile('Setting');
            }, 6000);

        }, error: function (xhr, status, error){
            btn_change_password.text('').append('<i id="spinner" style="margin-left: -12px;\n' +
                '    margin-right: 8px;" class="fa fa-spinner fa-spin"></i> Обработка...')
            setTimeout(function () {
                btn_change_password.removeClass('btn-save-change')
                btn_change_password.addClass('btn-failed-change')
                btn_change_password.text(xhr.responseJSON.message)
            }, 3000);
            setTimeout(function () {
                btn_change_password.removeClass('btn-failed-change')
                btn_change_password.addClass('btn-save-change')
                btn_change_password.text('Cохранить')
            }, 6000);
        }
    });
})
let pass_confirm = document.getElementById("password_confirmation")
if(pass_confirm){
    pass_confirm.addEventListener("input", (ev) => {
        var value_n = document.getElementById("new-password").value;
        var value_c = document.getElementById("password_confirmation").value;
        if(value_n !== value_c){
            displayError("Пароли не совпадают")
        } else {
            clearError()
        }
    });
}
let pass_new = document.getElementById("new-password")
if(pass_new){
    pass_new.addEventListener("input", (ev) => {
        var value_n = document.getElementById("new-password").value;
        var value_c = document.getElementById("password_confirmation").value;
        if(value_n !== value_c){
            displayError("Пароли не совпадают")
        } else {
            clearError()
        }
    });
}

// Function to display error message
function displayError(message) {
    var errorMessageContainer = document.getElementById('ajax-alert');
    errorMessageContainer.textContent = message;
}

// Function to clear error message
function clearError() {
    var errorMessageContainer = document.getElementById('ajax-alert');
    errorMessageContainer.textContent = '';
}
let current_pass = document.getElementById('current-password')
// Listen for changes in input fields to clear error message if input is present
if(current_pass){
    current_pass.addEventListener('input', clearError);
}

// document.getElementById('new-password').addEventListener('input', clearError);
