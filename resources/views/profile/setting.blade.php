<div class="tab-pane fade active show pt-3" id="tab-settings">
    @if($user->is_alert_for_needs_set_password)
        <div class="row mb-3">
            <div class="col-md-8 col-lg-9">
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <label for="" class="form-label">Вход был выполнен через сервисы рекомендуется заполнить пароль</label>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    <div class="row mb-3">
        <div class="col-md-8 col-lg-9">
            <ol class="list-group">
                <li class="list-group-item d-flex justify-content-between align-items-start">
                    <div class="ms-2 me-auto">
                        <div class="fw-bold">Способы входа в аккаунт</div>
                            @foreach($user->types_auth as $auth)
                                Доступен вход по {!! $auth['icon_auth'] !!}
                                <span class="badge bg-primary">{{strtoupper($auth['title_auth'])}}</span><br>
                             @endforeach
                            </div>
                        </li>
                    </ol>
                    </div>
                </div>
            </div>

    <form id="passwordChangeForm">
        @if($user->is_show_old_password)
        <div class="row mb-3">
            <div class="col-md-8 col-lg-9">
                <div class="form-floating mb-3">
                    <input type="password" placeholder="Укажите старый пароль" autocomplete="current-password" name="current-password" class="form-control"
                           id="current-password" required>
                    <label for="current-password" class="form-label">Старый пароль</label>
                </div>
            </div>
        </div>
            @else
            <div class="row mb-3" style="display: none">
                <div class="col-md-8 col-lg-9">
                    <div class="form-floating mb-3">
                        <input type="password" placeholder="Укажите старый пароль" autocomplete="current-password" name="current-password" class="form-control"
                               id="current-password" value="1">
                        <label for="current-password" class="form-label">Старый пароль</label>
                    </div>
                </div>
            </div>
        @endif
        <div class="row mb-3">
            <div class="col-md-8 col-lg-9">
                <div class="form-floating mb-3">
                    <input type="password" class="form-control" placeholder="Новый пароль" name="new-password" id="new-password" required>
                    <label for="new-password">Новый пароль</label>
                </div>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-8 col-lg-9">
                <div class="form-floating mb-3">
                    <input type="password" placeholder="Подтверждение пароля" autocomplete="new-password" name="password_confirmation" class="form-control"
                           id="password_confirmation" required>
                    <label for="password_confirmation">Подтверждение пароля</label>
                </div>
            </div>
        </div>
        <div class="text-center">
            <button type="submit" id="changePassword" class="btn btn-primary btn-save-change">Сохранить</button>
            <div id="ajax-alert" class="pt-2 alert-danger" style="color: red"></div>
        </div>
    </form>
</div>
<script>
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
    document.getElementById("password_confirmation").addEventListener("input", (ev) => {
        var value_n = document.getElementById("new-password").value;
        var value_c = document.getElementById("password_confirmation").value;
        if(value_n !== value_c){
            displayError("Пароли не совпадают")
        } else {
            clearError()
        }
    });
    document.getElementById("new-password").addEventListener("input", (ev) => {
        var value_n = document.getElementById("new-password").value;
        var value_c = document.getElementById("password_confirmation").value;
        if(value_n !== value_c){
            displayError("Пароли не совпадают")
        } else {
            clearError()
        }
    });

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

    // Listen for changes in input fields to clear error message if input is present
    document.getElementById('current-password').addEventListener('input', clearError);
    // document.getElementById('new-password').addEventListener('input', clearError);
</script>
