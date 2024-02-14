<div class="tab-pane fade show active profile-overview" id="tab-overview">
    <h5 class="card-title">Мой профиль</h5>
    <div class="row">
        <div class="col-lg-3 col-md-4 label ">Имя и Фамилия</div>
        <div class="col-lg-9 col-md-8">{{$user->middlename}}</div>
    </div>
    <div class="row">
        <div class="col-lg-3 col-md-4 label">Команда</div>
        <div class="col-lg-9 col-md-8">{{$user->team}}</div>
    </div>
    <div class="row">
        <div class="col-lg-3 col-md-4 label">Город</div>
        <div class="col-lg-9 col-md-8">{{$user->city}}</div>
    </div>
    @if($user->gender ?? null)
    <div class="row">
        <div class="col-lg-3 col-md-4 label">Пол</div>
        <div class="col-lg-9 col-md-8">@lang('somewords.'.$user->gender)</div>
    </div>
    @endif
    @if($user->is_alert_needs_show_email_and_password)
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            Вход был выполнен через сервисы и email сгенерировался автоматически в качестве логина
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            *Необходимо установить пароль в разделе Найстройки чтобы входить по логину и паролю
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    <div class="row">
        <div class="col-lg-3 col-md-4 label">Email(Логин)</div>
        <div class="col-lg-9 col-md-8">{{$user->email}}</div>
    </div>
</div>
