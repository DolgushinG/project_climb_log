@extends('layouts.main_page.app')
@section('content')
    @guest
        <main id="main" class="main">
            <div class="container">
                <section
                    class="section register min-vh-100 d-flex flex-column align-items-center justify-content-center py-4">
                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="col-lg-4 col-md-6 d-flex flex-column align-items-center justify-content-center">

                                <div class="card mb-3">

                                    <div class="card-body">

                                        <div class="pt-4 pb-2">
                                            <h5 class="card-title text-center pb-0 fs-4">Войти в аккаунт</h5>
                                            <p class="text-center small">Ввдите ваш email и пароль чтобы войти</p>
                                        </div>

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
                                                <button class="btn btn-primary w-100" type="submit">
                                                    Войти
                                                </button>
                                            </div>
                                            <div class="col-12">
                                                @if (Route::has('password.request'))
                                                    <a href="{{ route('password.request') }}"
                                                       class="btn btn-primary w-100">
                                                        {{ __('Забыл пароль?') }}
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
