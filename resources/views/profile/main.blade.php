@extends('layouts.main_page.app')
@section('content')
    <link href="https://cdn.jsdelivr.net/npm/suggestions-jquery@20.3.0/dist/css/suggestions.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/suggestions-jquery@20.3.0/dist/js/jquery.suggestions.min.js"></script>
    <main id="main" class="main">
        <section class="section profile">
            <div class="container">
                <div class="row">
                    <div class="col-lg-4">
                        <div class="profile-card-4 z-depth-3">
                            <div class="card">
                                <div class="card-body text-center bg-primary rounded-top">
                                    <div class="user-box">
                                    @if ($user->avatar === null)
                                            <img src="https://eu.ui-avatars.com/api/?name={{ $user->middlename }}&background=random&color=050202&font-size=0.33"
                                                 alt="Profile" class="img-fluid rounded-circle">
                                    @else
                                        <img src="{{$user->avatar}}" class="img-fluid rounded-circle" alt="user avatar">
                                    @endif
                                    </div>
                                    <h5 class="mb-1 text-white">{{$user->middlename}}</h5>
                                </div>
                                <div class="card-body">
                                    <ul class="list-group shadow-none">
                                        <li class="list-group-item">
                                            <div class="list-icon">
                                                <i class="fa fa-building"></i>
                                            </div>
                                            <div class="list-details">
                                                <span>{{$user->city ?? ''}}</span>
                                                <small>Город</small>
                                            </div>
                                        </li>
                                        <li class="list-group-item">
                                            <div class="list-icon">
                                                <i class="fa fa-envelope"></i>
                                            </div>
                                            <div class="list-details">
                                                <span>{{$user->email ?? ''}}</span>
                                                <small>Email</small>
                                            </div>
                                        </li>
{{--                                        <li class="list-group-item">--}}
{{--                                            <div class="list-icon">--}}
{{--                                                <i class="fa fa-globe"></i>--}}
{{--                                            </div>--}}
{{--                                            <div class="list-details">--}}
{{--                                                <span>www.example.com</span>--}}
{{--                                                <small>Website Address</small>--}}
{{--                                            </div>--}}
{{--                                        </li>--}}
                                    </ul>
                                    <div class="row text-center mt-4">
                                        <div class="col p-2">
                                            <h4 class="mb-1 line-height-5">{{$state_user['flash']}}</h4>
                                            <small class="mb-0 font-weight-bold">% Flash</small>
                                        </div>
                                        <div class="col p-2">
                                            <h4 class="mb-1 line-height-5">{{$state_user['redpoint']}}</h4>
                                            <small class="mb-0 font-weight-bold">% Redpoint </small>
                                        </div>
                                        <div class="col p-2">
                                            <h4 class="mb-1 line-height-5"> {{$state_user['all']}}</h4>
                                            <small class="mb-0 font-weight-bold">Всего трасс </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-8">
                        <div class="card z-depth-3">
                            <div class="card-body">

                                <ul class="nav nav-pills nav-pills-primary nav-justified">
                                    <li class="nav-item">
                                        <button id="overview" data-target="#profile-overview" data-toggle="pill"
                                                class="nav-link active"><i class="icon-user"></i> <span
                                                class="hidden-xs">Профиль</span></button>
                                    </li>
                                    <li class="nav-item">
                                        <button id="events" data-target="#profile-events" data-toggle="pill"
                                                class="nav-link"><i class="icon-envelope-open"></i> <span
                                                class="hidden-xs">Соревнования</span></button>
                                    </li>
                                    <li class="nav-item">
                                        <button id="setting" data-target="#profile-settings" data-toggle="pill"
                                                class="nav-link"><i class="icon-note"></i> <span class="hidden-xs">Пароль</span>
                                        </button>
                                    </li>
                                    <li class="nav-item">
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
    </main><!-- End #main -->

@endsection
