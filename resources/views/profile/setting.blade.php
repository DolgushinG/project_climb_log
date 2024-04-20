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

</script>
