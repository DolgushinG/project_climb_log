@extends('layouts.main_page.app')
@section('content')
    <link href="{{asset('vendor/helpers/css_suggestions.css')}}" rel="stylesheet" />
    <script src="{{asset('vendor/helpers/jquery.suggestions.js')}}"></script>

    <section class="section profile">

        <div class="container">
            <div class="row mt-3">
                @if(str_contains($user->email, 'telegram'))
                    <div class="container d-flex align-items-center" data-aos="zoom-out" data-aos-delay="100">
                        <div class="alert alert-warning  alert-dismissible fade show w-100" role="alert">
                            <h4 class="alert-heading">Добро пожаловать {{$user->middlename}}! </h4>
                            <p>У вас был вход через Telegram, вам необходимо заполнить ваш email для участия в соревнованиях</p>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <h4></h4>
                    </div>
                @endif
                    @if(!$user->contact)
                        <div class="alert alert-warning  alert-dismissible fade show w-100" role="alert">
                            <p> Рекомендуется добавить номер телефона, телеграмм или другой способ</p>
                            <p> Чтобы организаторы в случае каких то вопросов могли быстро уведомить вас индивидуально</p>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                <div class="col-lg-4" id="profileCard">
                    @include('profile.card')
                </div>
                <div class="col-lg-8">
                    <div class="card z-depth-3">
                        <div class="card-body">

                            <ul class="nav nav-pills nav-pills-primary nav-justified">
                                <li class="nav-item" style="margin-right: 8px!important;">
                                    <button id="overview" data-target="#profile-overview" data-toggle="pill"
                                            class="nav-link active"><i class="icon-user"></i> <span
                                            class="hidden-xs">Профиль</span></button>
                                </li>
                                <li class="nav-item" style="margin-right: 8px!important;">
                                    <button id="events" data-target="#profile-events" data-toggle="pill"
                                            class="nav-link"><i class="icon-envelope-open"></i> <span
                                            class="hidden-xs">Соревнования</span></button>
                                </li>
                                <li class="nav-item" style="margin-right: 8px!important;">
                                    <button id="setting" data-target="#profile-settings" data-toggle="pill"
                                            class="nav-link"><i class="icon-note"></i> <span class="hidden-xs">Пароль</span>
                                    </button>
                                </li>
                                <li class="nav-item" style="margin-right: 8px!important;">
                                    <button id="edit" data-target="#profile-edit" data-toggle="pill"
                                            class="nav-link"><i class="icon-note"></i> <span class="hidden-xs">Изменить</span>
                                    </button>
                                </li>
                            </ul>
                            <div id="tabContent" class="tab-content p-3">
                                @include('profile.overview')
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>
    <script type="text/javascript" src="{{ asset('js/profile.js') }}"></script>
@endsection
