<header id="header" class="header fixed-top d-flex align-items-center">

        <div class="d-flex align-items-center justify-content-between">
            <a href="{{route('main')}}" class="logo d-flex align-items-center">
                <img src="{{asset('storage/img/logo.png')}}" alt="">
                <span class="d-none d-lg-block">Climb Events</span>
            </a>
{{--            <i class="bi bi-list toggle-sidebar-btn"></i>--}}
        </div><!-- End Logo -->

    <nav class="header-nav ms-auto">
        <ul class="d-flex align-items-center">
            @auth
            <li class="nav-item dropdown pe-3">

                <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
                    @if(Auth()->user()->avatar)
                        <img src="{{Auth()->user()->avatar}}" alt="Profile" class="rounded-circle">
                    @else
                        <img src="https://eu.ui-avatars.com/api/?name={{Auth()->user()->middlename}}&background=random&color=050202&font-size=0.33&size=150" alt="Profile" class="rounded-circle">
                    @endif
                    <span class="d-none d-md-block dropdown-toggle ps-2">{{Auth()->user()->middlename}}</span>
                </a><!-- End Profile Iamge Icon -->

                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
                    <li class="dropdown-header">
                        <h6>{{Auth()->user()->middlename}}</h6>
                    </li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>

                    <li>
                        <a class="dropdown-item d-flex align-items-center" href="{{route('profile')}}">
                            <i class="bi bi-person"></i>
                            <span>Мой профиль</span>
                        </a>
                    </li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center" href="#">
                            <i class="bi bi-question-circle"></i>
                            <span>Нужна помощь?</span>
                        </a>
                    </li>
                    <li>
                    @if(Auth::check())
                            <i class="fa fa-user"></i>
                            <a class="dropdown-item d-flex align-items-center" href="{{ route('logout') }}" onclick="event.preventDefault();document.getElementById('frm-logout').submit();">Выйти</a>
                            <form id="frm-logout" action="{{ route('logout') }}" method="POST" style="display: none;">
                                {{ csrf_field() }}
                            </form>
                    @endif
                    </li>

                </ul><!-- End Profile Dropdown Items -->
            </li><!-- End Profile Nav -->
            @endauth
            @guest
                <li class="nav-item dropdown pe-3">

                    <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
                        <span class="d-none d-md-block dropdown-toggle ps-2">Войти</span>
                    </a><!-- End Profile Iamge Icon -->

                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">

                        <li>
                            <a class="dropdown-item d-flex align-items-center" href="{{route('register')}}">
                                <i class="bi bi-question-circle"></i>
                                <span>Регистрация</span>
                            </a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>

                        <li>
                            <a class="dropdown-item d-flex align-items-center" href="{{route('login')}}">
                                <i class="bi bi-box-arrow-right"></i>
                                <span>Войти</span>
                            </a>
                        </li>

                    </ul><!-- End Profile Dropdown Items -->
                </li><!-- End Profile Nav -->
            @endguest
        </ul>
    </nav><!-- End Icons Navigation -->

</header><!-- End Header -->
