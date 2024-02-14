<div class="tab-pane fade active show pt-3" id="tab-settings">
    @foreach($user->types_auth as $auth)
        <div class="card">
            <div class="card-body pt-4">
                Вход через был {!! $auth['icon_auth'] !!} <span class="badge bg-primary">{{strtoupper($auth['title_auth'])}}</span> - {{$user->updated_at}}
            </div>
        </div>
    @endforeach
    <form>
        <div class="row mb-3">
            <div class="col-md-8 col-lg-9">
                <div class="form-check">
                    <label for="current-password" class="form-label">Старый пароль</label>
                    <input type="password" autocomplete="current-password" name="current-password" class="form-control"
                           id="current-password" required>
                </div>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-8 col-lg-9">
                <div class="form-check">
                    <label for="new-password" class="form-label">Новый пароль</label>
                    <input type="password" autocomplete="new-password" name="new-password" class="form-control"
                           id="new-password" required>
                </div>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-8 col-lg-9">
                <div class="form-check">
                    <label for="password_confirmation" class="form-label">Подтверждение
                        пароля</label>
                    <input type="password" autocomplete="new-password" name="password_confirmation" class="form-control"
                           id="password_confirmation" required>
                </div>
            </div>
        </div>
        <div class="text-center">
            <button type="submit" id="changePassword" class="btn btn-primary btn-save-change">Сохранить</button>
            <div id="ajax-alert" class="pt-2 alert-danger" style="display:none; color: red"></div>
        </div>
    </form>
</div>
<script>
    document.querySelector('#changePassword').setAttribute('disabled', 'disabled');
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
    });
    document.getElementById("current-password").addEventListener("input", (ev) => {
        var value_o = document.getElementById("current-password").value;
        var value_n = document.getElementById("new-password").value;
        var value_c = document.getElementById("password_confirmation").value;
        if (value_o !== "" && value_n !== "" && value_c !== ""){
            var changePassword = document.querySelector('#changePassword');
            changePassword.removeAttribute('disabled','disabled');
        }
    });
    document.getElementById("new-password").addEventListener("input", (ev) => {
        var value_o = document.getElementById("current-password").value;
        var value_n = document.getElementById("new-password").value;
        var value_c = document.getElementById("password_confirmation").value;
        if (value_o !== "" && value_n !== "" && value_c !== ""){
            if(value_n === value_c){
                var changePassword = document.querySelector('#changePassword');
                changePassword.removeAttribute('disabled','disabled');
                document.querySelector('#ajax-alert').style.display = 'None';
            } else {
                var alert = document.querySelector('#ajax-alert');
                alert.style.display = 'block'
                alert.textContent = "Пароли не совпадают"
                alert.color = 'red'
            }
        }
    });
    document.getElementById("password_confirmation").addEventListener("input", (ev) => {
        var value_o = document.getElementById("current-password").value;
        var value_n = document.getElementById("new-password").value;
        var value_c = document.getElementById("password_confirmation").value;
        if (value_o !== "" && value_n !== "" && value_c !== ""){
            if(value_n === value_c){
                var changePassword = document.querySelector('#changePassword');
                changePassword.removeAttribute('disabled','disabled');
                document.querySelector('#ajax-alert').style.display = 'None';
            } else {
                var alert = document.querySelector('#ajax-alert');
                alert.style.display = 'block'
                alert.textContent = "Пароли не совпадают"
                alert.color = 'red'
            }
        }
    });
</script>
