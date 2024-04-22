@extends('layouts.main_page.app')
@section('content')
    @guest
        <section id="contact" class="d-flex align-items-center">
            <div class="container" data-aos="zoom-out" data-aos-delay="100">
            </div>
        </section><!-- End Hero -->
        <main id="main" class="main">
            <div class="container">
                <section class="hero" style="height: 43vh;">
                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="col-lg-6">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title">{{ __('Сброс пароля') }}</h5>
                                        <form method="POST" action="{{ route('password.update') }}">
                                            @csrf
                                            <input type="hidden" name="token" value="{{ $request->route('token')}}">
                                        <!-- Floating Labels Form -->
                                            <div class="col-md-6">
                                                <div class="form-floating mb-3">
                                                    <input id="email" placeholder="Ваш email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ $email ?? old('email') }}" required autocomplete="email" autofocus>
                                                    <label for="email">Ваш email</label>
                                                    @error('email')
                                                    <span class="invalid-feedback" role="alert">
                                                 <strong>{{ $message }}</strong>
                                                </span>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-floating mb-3">
                                                    <input id="floatingPassword" placeholder="Новый пароль" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">
                                                    <label for="floatingPassword">Новый пароль</label>
                                                    @error('password')
                                                    <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                    </span>
                                                    @enderror

                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-floating mb-3">
                                                    <input id="password-confirm" placeholder="Подтверждение пароля" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password">
                                                    <label for="password-confirm">Подтверждение пароля</label>
                                                </div>
                                            </div>
                                            <div class="text-center">
                                                <button type="submit" class="btn btn-primary">{{ __('Обновить пароль') }}</button>
                                            </div>
                                            @if (session('status'))
                                                <p class="alert alert-success">{{ session('status') }}</p>
                                            @endif
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
