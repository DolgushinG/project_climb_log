
<div class="tab-pane fade show active profile-overview" id="tab-overview">
        <div class="row">
            <div class="col-md-6">
                <h6>Команда</h6>
                <p>
                    {{$user->team  ?? ''}}
                </p>
                <h6>Пол</h6>
                <p>
                    @lang('somewords.'.$user->gender  ?? '')
                </p>
            </div>
            <div class="col-md-6">
                <h6>Recent badges</h6>
                <span class="badge bg-primary"><i class="fa fa-user"></i> 900 Участие в соревнованиях</span>
                <span class="badge bg-success"><i class="fa fa-cog"></i> 43 Маскимальная Категория</span>
                <span class="badge bg-danger"><i class="fa fa-eye"></i> 245 Лучшие место</span>
            </div>
            <div class="col-md-12">
                <h5 class="mt-2 mb-3"><span class="fa fa-clock-o ion-clock float-right"></span> Недавняя активность</h5>
                <table class="table table-hover table-striped">
                    <tbody>
                    @foreach($activities as $activity)
                        <tr>
                            <td>
                                <strong>{{$activity->description}} - {{$activity->updated_at}}</strong>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <!--/row-->
{{--    <h5 class="card-title">Мой профиль</h5>--}}
{{--    <div class="row">--}}
{{--        <div class="col-lg-3 col-md-4 label ">Имя и Фамилия</div>--}}
{{--        <div class="col-lg-9 col-md-8">{{$user->middlename}}</div>--}}
{{--    </div>--}}
{{--    <div class="row">--}}
{{--        <div class="col-lg-3 col-md-4 label">Команда</div>--}}
{{--        <div class="col-lg-9 col-md-8">{{$user->team}}</div>--}}
{{--    </div>--}}
{{--    <div class="row">--}}
{{--        <div class="col-lg-3 col-md-4 label">Город</div>--}}
{{--        <div class="col-lg-9 col-md-8">{{$user->city}}</div>--}}
{{--    </div>--}}
{{--    @if($user->gender ?? null)--}}
{{--    <div class="row">--}}
{{--        <div class="col-lg-3 col-md-4 label">Пол</div>--}}
{{--        <div class="col-lg-9 col-md-8">@lang('somewords.'.$user->gender)</div>--}}
{{--    </div>--}}
{{--    @endif--}}
{{--    @if($user->is_alert_needs_show_email_and_password)--}}
{{--        <div class="alert alert-warning alert-dismissible fade show" role="alert">--}}
{{--            Вход был выполнен через сервисы и email сгенерировался автоматически в качестве логина--}}
{{--            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>--}}
{{--        </div>--}}
{{--        <div class="alert alert-warning alert-dismissible fade show" role="alert">--}}
{{--            *Необходимо установить пароль в разделе Найстройки чтобы входить по логину и паролю--}}
{{--            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>--}}
{{--        </div>--}}
{{--    @endif--}}
{{--    <div class="row">--}}
{{--        <div class="col-lg-3 col-md-4 label">Email(Логин)</div>--}}
{{--        <div class="col-lg-9 col-md-8">{{$user->email}}</div>--}}
{{--    </div>--}}
</div>
