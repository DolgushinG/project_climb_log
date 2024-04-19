@extends('layouts.main_page.app')
@section('content')
    @guest
        <main id="main" class="main">
            <div class="container">
                <section
                    class="section register min-vh-100 d-flex flex-column justify-content-center py-4">
                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="col-lg-4 col-md-6 d-flex flex-column justify-content-center">

                                <div class="card mb-3">

                                    <div class="card-body">
                                        @if($errors->any())
                                            @foreach($errors->all() as $error)
                                                <span class="text-uppercase text-danger d-block mb-3" data-aos="fade-left" data-aos-delay="300">{{ $error }}</span>
                                            @endforeach
                                        @endif
                                        <div class="pt-4 pb-2">
                                            <h5 class="card-title text-center pb-0 fs-4">Войти в аккаунт</h5>
                                            <p class="text-center small">Каждый сервис это уникальный аккаунт </p>
                                        </div>
                                        <p class="text-center small">Введите ваш email и пароль чтобы войти</p>
                                        <form class="row g-3 needs-validation" method="POST"
                                              action="{{ route('login') }}">
                                            @csrf
                                            <!-- Email Address -->
                                            <div class="col-12">
                                                <label for="email" class="form-label">Email</label>
                                                <div class="input-group has-validation">
                                                    <span class="input-group-text" id="inputGroupPrepend">@</span>
                                                    <input type="email" id="email" name="email" class="form-control"
                                                           value="{{old('email')}}" required autofocus>
                                                    <div class="invalid-feedback">Пожалуйста введите email</div>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <label for="password" class="form-label">Пароль</label>
                                                <input type="password" name="password" class="form-control"
                                                       id="password" required autocomplete="current-password">
                                                <div class="invalid-feedback">Пожалуйста введите ваш пароль</div>
                                            </div>
                                            <div class="col-12">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="remember"
                                                           value="true" id="remember_me">
                                                    <label class="form-check-label" for="rememberMe">Запомнить
                                                        меня</label>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <button id="submit" class="btn btn-primary w-100" type="submit">
                                                    Войти
                                                </button>
                                            </div>

                                            <div class="pt-4 pb-2">
                                                <div class="col-12" style="text-align: center">
                                                    {{--                                                <a href="/auth/telegram/redirect" class="btn btn-primary w-100" type="button">--}}
                                                    {{--                                                    <i class="fa fa-telegram" aria-hidden="true"></i> Войти через Telegram--}}
                                                    {{--                                                </a>--}}
                                                    {!! Socialite::driver('telegram')->getButton() !!}
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <a href="/auth/vkontakte/redirect" class="btn btn-primary w-100" type="button">
                                                    <i class="fa fa-vk" aria-hidden="true"></i> Войти через VK
                                                </a>
                                            </div>

                                            <div class="col-12">
                                                <a href="/auth/yandex/redirect" class="w-100" type="button">
                                                    <svg width="100%" height="100%" viewBox="0 0 567 56" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M0 24C0 12.6863 0 7.02944 3.51472 3.51472C7.02944 0 12.6863 0 24 0H543C554.314 0 559.971 0 563.485 3.51472C567 7.02944 567 12.6863 567 24V32C567 43.3137 567 48.9706 563.485 52.4853C559.971 56 554.314 56 543 56H24C12.6863 56 7.02944 56 3.51472 52.4853C0 48.9706 0 43.3137 0 32V24Z" fill="black"></path>
                                                        <rect x="197.5" y="16" width="24" height="24" rx="12" fill="#FC3F1D"></rect>
                                                        <path d="M211.191 35.212H213.698V20.812H210.051C206.384 20.812 204.457 22.6975 204.457 25.4739C204.457 27.6909 205.514 28.9962 207.399 30.3429L204.126 35.212H206.84L210.487 29.7628L209.223 28.9133C207.689 27.8773 206.944 27.0693 206.944 25.3288C206.944 23.7956 208.021 22.7596 210.072 22.7596H211.191V35.212Z" fill="white"></path>
                                                        <path d="M234.896 21.528H238.8C240.165 21.528 241.205 21.7414 241.92 22.168C242.635 22.5947 242.992 23.3147 242.992 24.328C242.992 24.744 242.933 25.1067 242.816 25.416C242.699 25.7147 242.528 25.976 242.304 26.2C242.091 26.4134 241.829 26.5894 241.52 26.728C241.211 26.8667 240.869 26.9734 240.496 27.048C241.456 27.1654 242.181 27.4267 242.672 27.832C243.163 28.2374 243.408 28.856 243.408 29.688C243.408 30.2854 243.291 30.7974 243.056 31.224C242.821 31.64 242.496 31.9814 242.08 32.248C241.664 32.504 241.173 32.696 240.608 32.824C240.043 32.9414 239.429 33 238.768 33H234.896V21.528ZM236.832 23.208V26.264H238.896C239.536 26.264 240.053 26.1307 240.448 25.864C240.843 25.5867 241.04 25.1547 241.04 24.568C241.04 24.0347 240.859 23.6774 240.496 23.496C240.144 23.304 239.643 23.208 238.992 23.208H236.832ZM236.832 27.928V31.336H238.96C239.323 31.336 239.653 31.3094 239.952 31.256C240.251 31.192 240.507 31.096 240.72 30.968C240.933 30.8294 241.099 30.6534 241.216 30.44C241.333 30.216 241.392 29.944 241.392 29.624C241.392 28.9947 241.184 28.5574 240.768 28.312C240.363 28.056 239.707 27.928 238.8 27.928H236.832Z" fill="white"></path>
                                                        <path d="M248.773 33.16C248.186 33.16 247.642 33.064 247.141 32.872C246.639 32.68 246.202 32.4027 245.829 32.04C245.466 31.6667 245.178 31.2134 244.965 30.68C244.762 30.1467 244.661 29.5334 244.661 28.84C244.661 28.1467 244.762 27.5334 244.965 27C245.178 26.4667 245.466 26.0187 245.829 25.656C246.202 25.2827 246.639 25.0054 247.141 24.824C247.642 24.632 248.186 24.536 248.773 24.536C249.359 24.536 249.903 24.632 250.405 24.824C250.906 25.0054 251.343 25.2827 251.717 25.656C252.09 26.0187 252.383 26.4667 252.597 27C252.81 27.5334 252.917 28.1467 252.917 28.84C252.917 29.5334 252.81 30.1467 252.597 30.68C252.383 31.2134 252.09 31.6667 251.717 32.04C251.343 32.4027 250.906 32.68 250.405 32.872C249.903 33.064 249.359 33.16 248.773 33.16ZM248.773 31.608C249.413 31.608 249.941 31.384 250.357 30.936C250.783 30.488 250.997 29.7894 250.997 28.84C250.997 27.9014 250.783 27.208 250.357 26.76C249.941 26.3014 249.413 26.072 248.773 26.072C248.143 26.072 247.615 26.3014 247.189 26.76C246.773 27.208 246.565 27.9014 246.565 28.84C246.565 29.7894 246.773 30.488 247.189 30.936C247.615 31.384 248.143 31.608 248.773 31.608Z" fill="white"></path>
                                                        <path d="M258.214 23.704C257.766 23.704 257.376 23.6454 257.046 23.528C256.726 23.4107 256.454 23.2507 256.23 23.048C256.016 22.8347 255.856 22.5894 255.75 22.312C255.643 22.024 255.59 21.72 255.59 21.4H257.222C257.222 21.7734 257.307 22.0454 257.478 22.216C257.659 22.376 257.904 22.456 258.214 22.456C258.523 22.456 258.763 22.376 258.934 22.216C259.104 22.0454 259.19 21.7734 259.19 21.4H260.838C260.838 21.72 260.784 22.024 260.678 22.312C260.571 22.5894 260.406 22.8347 260.182 23.048C259.968 23.2507 259.696 23.4107 259.366 23.528C259.035 23.6454 258.651 23.704 258.214 23.704ZM256.31 30.488L260.15 24.696H261.974V33H260.134V27.24L256.326 33H254.47V24.696H256.31V30.488Z" fill="white"></path>
                                                        <path d="M267.775 26.2V33H265.919V26.2H263.407V24.696H270.335V26.2H267.775Z" fill="white"></path>
                                                        <path d="M273.591 30.488L277.431 24.696H279.255V33H277.415V27.24L273.607 33H271.751V24.696H273.591V30.488Z" fill="white"></path>
                                                        <path d="M288.684 33.16C288.012 33.16 287.41 33.064 286.876 32.872C286.343 32.6694 285.89 32.3867 285.516 32.024C285.143 31.6507 284.855 31.1974 284.652 30.664C284.45 30.1307 284.348 29.5227 284.348 28.84C284.348 28.168 284.45 27.5654 284.652 27.032C284.855 26.4987 285.143 26.0507 285.516 25.688C285.89 25.3147 286.348 25.032 286.892 24.84C287.436 24.6374 288.044 24.536 288.716 24.536C289.292 24.536 289.794 24.6 290.22 24.728C290.658 24.856 291.004 25.016 291.26 25.208V26.744C290.93 26.5307 290.567 26.3654 290.172 26.248C289.788 26.1307 289.34 26.072 288.828 26.072C287.111 26.072 286.252 26.9947 286.252 28.84C286.252 30.6854 287.095 31.608 288.78 31.608C289.324 31.608 289.788 31.5494 290.172 31.432C290.567 31.304 290.93 31.144 291.26 30.952V32.488C290.983 32.6694 290.636 32.8294 290.22 32.968C289.804 33.096 289.292 33.16 288.684 33.16Z" fill="white"></path>
                                                        <path d="M296.597 25.128C296.597 24.52 296.704 23.992 296.917 23.544C297.131 23.0854 297.429 22.712 297.813 22.424C298.197 22.1254 298.656 21.9014 299.189 21.752C299.733 21.6027 300.336 21.528 300.997 21.528H304.725V33H302.773V28.584H300.997L298.069 33H295.813L299.045 28.296C298.213 28.0934 297.595 27.7254 297.189 27.192C296.795 26.648 296.597 25.96 296.597 25.128ZM302.773 26.968V23.208H300.981C300.256 23.208 299.675 23.352 299.237 23.64C298.811 23.9174 298.597 24.3974 298.597 25.08C298.597 25.752 298.789 26.2374 299.173 26.536C299.557 26.824 300.101 26.968 300.805 26.968H302.773Z" fill="white"></path>
                                                        <path d="M312.345 29.496H308.841V33H306.985V24.696H308.841V27.992H312.345V24.696H314.201V33H312.345V29.496Z" fill="white"></path>
                                                        <path d="M316.058 31.496C316.303 31.2827 316.495 30.9894 316.634 30.616C316.783 30.2427 316.901 29.7787 316.986 29.224C317.071 28.6587 317.135 28.0027 317.178 27.256C317.221 26.5094 317.258 25.656 317.29 24.696H323.37V31.496H324.618V35.368H323.002L322.858 33H317.13L316.986 35.368H315.354V31.496H316.058ZM321.514 31.496V26.2H318.89C318.837 27.512 318.746 28.6 318.618 29.464C318.501 30.3174 318.303 30.9947 318.026 31.496H321.514Z" fill="white"></path>
                                                        <path d="M332.773 32.392C332.656 32.4667 332.512 32.552 332.341 32.648C332.17 32.7334 331.968 32.8134 331.733 32.888C331.498 32.9627 331.226 33.0267 330.917 33.08C330.608 33.1334 330.256 33.16 329.861 33.16C328.336 33.16 327.194 32.7814 326.437 32.024C325.69 31.2667 325.317 30.2054 325.317 28.84C325.317 28.168 325.418 27.5654 325.621 27.032C325.824 26.4987 326.106 26.0507 326.469 25.688C326.832 25.3147 327.264 25.032 327.765 24.84C328.266 24.6374 328.816 24.536 329.413 24.536C330.032 24.536 330.586 24.6374 331.077 24.84C331.578 25.0427 331.989 25.3467 332.309 25.752C332.629 26.1574 332.848 26.6587 332.965 27.256C333.093 27.8534 333.098 28.552 332.981 29.352H327.237C327.312 30.1094 327.562 30.68 327.989 31.064C328.416 31.4374 329.082 31.624 329.989 31.624C330.65 31.624 331.2 31.544 331.637 31.384C332.085 31.2134 332.464 31.0374 332.773 30.856V32.392ZM329.413 26.008C328.869 26.008 328.41 26.1734 328.037 26.504C327.664 26.8347 327.418 27.3254 327.301 27.976H331.205C331.226 27.304 331.077 26.808 330.757 26.488C330.437 26.168 329.989 26.008 329.413 26.008Z" fill="white"></path>
                                                        <path d="M337.28 29.624H336.56V33H334.704V24.696H336.56V28.12H337.344L339.984 24.696H341.936L338.8 28.696L342.064 33H339.872L337.28 29.624Z" fill="white"></path>
                                                        <path d="M346.731 33.16C346.059 33.16 345.456 33.064 344.923 32.872C344.39 32.6694 343.936 32.3867 343.563 32.024C343.19 31.6507 342.902 31.1974 342.699 30.664C342.496 30.1307 342.395 29.5227 342.395 28.84C342.395 28.168 342.496 27.5654 342.699 27.032C342.902 26.4987 343.19 26.0507 343.563 25.688C343.936 25.3147 344.395 25.032 344.939 24.84C345.483 24.6374 346.091 24.536 346.763 24.536C347.339 24.536 347.84 24.6 348.267 24.728C348.704 24.856 349.051 25.016 349.307 25.208V26.744C348.976 26.5307 348.614 26.3654 348.219 26.248C347.835 26.1307 347.387 26.072 346.875 26.072C345.158 26.072 344.299 26.9947 344.299 28.84C344.299 30.6854 345.142 31.608 346.827 31.608C347.371 31.608 347.835 31.5494 348.219 31.432C348.614 31.304 348.976 31.144 349.307 30.952V32.488C349.03 32.6694 348.683 32.8294 348.267 32.968C347.851 33.096 347.339 33.16 346.731 33.16Z" fill="white"></path>
                                                        <path d="M356.66 21.528V33H354.724V21.528H356.66Z" fill="white"></path>
                                                        <path d="M359.115 21.528H362.987C363.797 21.528 364.549 21.6187 365.243 21.8C365.947 21.9814 366.555 22.28 367.067 22.696C367.589 23.112 367.995 23.6614 368.283 24.344C368.581 25.0267 368.731 25.8694 368.731 26.872C368.731 27.8854 368.581 28.776 368.283 29.544C367.995 30.3014 367.584 30.936 367.051 31.448C366.528 31.96 365.904 32.3494 365.179 32.616C364.464 32.872 363.68 33 362.827 33H359.115V21.528ZM361.051 23.208V31.336H362.891C363.456 31.336 363.968 31.2507 364.427 31.08C364.896 30.9094 365.296 30.648 365.627 30.296C365.968 29.944 366.229 29.496 366.411 28.952C366.603 28.3974 366.699 27.7414 366.699 26.984C366.699 26.2374 366.608 25.624 366.427 25.144C366.245 24.6534 365.989 24.264 365.659 23.976C365.339 23.688 364.955 23.4907 364.507 23.384C364.059 23.2667 363.573 23.208 363.051 23.208H361.051Z" fill="white"></path>
                                                    </svg>
                                                </a>
                                            </div>
                                            <style>
                                                .svg{
                                                    width: 375px;
                                                    height: 50px;
                                                    font-size: 20px;
                                                }
                                            </style>
                                            <div class="col-12">
                                                @if (Route::has('password.request'))
                                                    <a href="{{ route('password.request') }}"
                                                       class="small mb-0">
                                                        {{ __('Забыли пароль?') }}
                                                    </a>
                                                @endif
                                            </div>
                                            <div class="col-12">
                                                <p class="small mb-0">У вас есть аккаунт? <a
                                                        href="{{ route('register') }}">Регистрация</a></p>
                                            </div>
                                        </form>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </main><!-- End #main -->
    @endguest
@endsection

