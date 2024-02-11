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
    @if(!$user->telegram_id)
    <div class="row">
        <div class="col-lg-3 col-md-4 label">Email</div>
        <div class="col-lg-9 col-md-8">{{$user->email}}</div>
    </div>
    @endif
</div>
