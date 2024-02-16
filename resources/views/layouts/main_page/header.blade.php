<header id="header" class="d-flex align-items-center">
    <div class="container d-flex align-items-center justify-content-between">

        <h1 class="logo"><a href="{{route('main')}}">Climbing Events<span>.</span></a></h1>
        <!-- Uncomment below if you prefer to use an image logo -->
        <!-- <a href="index.html" class="logo"><img src="assets/img/logo.png" alt=""></a>-->

        <nav id="navbar" class="navbar">
            <ul>
                <li><a class="nav-link scrollto active" href="{{route('main')}}">Главная</a></li>
{{--                <li><a class="nav-link scrollto" href="#about">About</a></li>--}}
{{--                <li><a class="nav-link scrollto" href="#services">Services</a></li>--}}
{{--                <li><a class="nav-link scrollto " href="#portfolio">Portfolio</a></li>--}}
{{--                <li><a class="nav-link scrollto" href="#team">Team</a></li>--}}
{{--                <li><a class="nav-link scrollto" href="#contact">Contact</a></li>--}}
{{--                <li class="dropdown"><a href="#"><span>Drop Down</span> <i class="bi bi-chevron-down"></i></a>--}}
{{--                    <ul>--}}
{{--                        <li><a href="#">Drop Down 1</a></li>--}}
{{--                        <li class="dropdown"><a href="#"><span>Deep Drop Down</span> <i class="bi bi-chevron-right"></i></a>--}}
{{--                            <ul>--}}
{{--                                <li><a href="#">Deep Drop Down 1</a></li>--}}
{{--                                <li><a href="#">Deep Drop Down 2</a></li>--}}
{{--                                <li><a href="#">Deep Drop Down 3</a></li>--}}
{{--                                <li><a href="#">Deep Drop Down 4</a></li>--}}
{{--                                <li><a href="#">Deep Drop Down 5</a></li>--}}
{{--                            </ul>--}}
{{--                        </li>--}}
{{--                        <li><a href="#">Drop Down 2</a></li>--}}
{{--                        <li><a href="#">Drop Down 3</a></li>--}}
{{--                        <li><a href="#">Drop Down 4</a></li>--}}
{{--                    </ul>--}}
{{--                </li>--}}
                @guest
                    <li class="dropdown"><a href="#"><span>Войти</span> <i class="bi bi-chevron-down"></i></a>
                        <ul>
                            <li><a href="{{route('register')}}">Регистрация</a></li>
                            <li><a href="{{route('login')}}">Войти</a></li>
                        </ul>
                    </li>
                @endguest
                @auth

                    <li class="dropdown"><a class="nav-link nav-profile d-flex align-items-center pe-0" href="{{route('profile')}}" data-bs-toggle="dropdown">
                            @if(Auth()->user()->avatar)
                                <img src="https://eu.ui-avatars.com/api/?name={{Auth()->user()->middlename}}&background=random&color=050202&font-size=0.33&size=15" alt="Profile" class="img-fluid rounded-circle">
{{--                                <div class="container">--}}
{{--                                    <img src="{{Auth()->user()->avatar}}" alt="Profile" class="img-fluid rounded-circle">--}}
{{--                                </div>--}}
                            @else
                                <img src="https://eu.ui-avatars.com/api/?name={{Auth()->user()->middlename}}&background=random&color=050202&font-size=0.33&size=15" alt="Profile" class="img-fluid rounded-circle">
                            @endif
                            <span class="d-none d-md-block dropdown-toggle ps-2">{{Auth()->user()->middlename}}</span>
                        </a>
                        <ul>
                            <li><a href="{{route('profile')}}">Мой профиль</a></li>
                            @if(Auth::check())
                                <li><a href="{{ route('logout') }}" onclick="event.preventDefault();document.getElementById('frm-logout').submit();">Выйти</a>
                                <form id="frm-logout" action="{{ route('logout') }}" method="POST" style="display: none;">
                                    {{ csrf_field() }}
                                </form>
                            @endif

                        </ul>
                    </li>
                @endauth

            </ul>
            <i class="bi bi-list mobile-nav-toggle"></i>
        </nav><!-- .navbar -->

    </div>
</header><!-- End Header -->
