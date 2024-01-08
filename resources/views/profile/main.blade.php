@extends('layouts.main_page.app')
@section('content')

    <main id="main" class="main">

        <div class="pagetitle">
            <h1>Профиль</h1>
        </div><!-- End Page Title -->

        <section class="section profile">
            <div class="row">
                <div class="col-xl-4">

                    <div class="card">
                        <div class="card-body profile-card pt-4 d-flex flex-column align-items-center">
                            @if ($user->photo === null)
                                <img
                                    src="https://eu.ui-avatars.com/api/?name={{ $user->middlename }}&background=random&color=050202&font-size=0.33&size=150"
                                    alt="Profile" class="rounded-circle">
                            @else
                                <img src="storage/{{$user->photo}}" alt="user avatar">
                            @endif
                            <h2>{{$user->middlename}}</h2>
                            <h3>{{$user->city}}</h3>
                            @if ($user->team === null)
                                <h3>Команда не указана</h3>
                            @else
                                <h3>{{$user->team}}</h3>
                            @endif
                            <div class="social-links mt-2">
                                <a href="#" class="twitter"><i class="bi bi-twitter"></i></a>
                                <a href="#" class="facebook"><i class="bi bi-facebook"></i></a>
                                <a href="#" class="instagram"><i class="bi bi-instagram"></i></a>
                                <a href="#" class="linkedin"><i class="bi bi-linkedin"></i></a>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="col-xl-8">

                    <div class="card">
                        <div class="card-body pt-3">
                            <!-- Bordered Tabs -->
                            <ul class="nav nav-tabs nav-tabs-bordered">

                                <li class="nav-item">
                                    <button id="overview" class="nav-link active" data-bs-toggle="tab"
                                            data-bs-target="#profile-overview">Информация
                                    </button>
                                </li>

                                <li class="nav-item">
                                    <button id="edit" class="nav-link" data-bs-toggle="tab"
                                            data-bs-target="#profile-edit">Изменить профиль
                                    </button>
                                </li>

                                <li class="nav-item">
                                    <button id="setting" class="nav-link" data-bs-toggle="tab"
                                            data-bs-target="#profile-settings">Настройка
                                    </button>
                                </li>

                                <li class="nav-item">
                                    <button id="events" class="nav-link" data-bs-toggle="tab"
                                            data-bs-target="#profile-events">Соревнования
                                    </button>
                                </li>

                            </ul>
                            <div id="tabContent" class="tab-content pt-2">

                                @include('profile.overview')

                            </div><!-- End Bordered Tabs -->
                            @include('message.message')
                        </div>
                    </div>

                </div>
            </div>
        </section>
        <script type="text/javascript" src="{{ asset('js/profile.js') }}"></script>
    </main><!-- End #main -->
@endsection
