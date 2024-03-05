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
                                        <form class="row g-3 needs-validation" method="POST"
                                              action="{{ route('password.update') }}">
                                            @csrf
{{--                                            <input type="hidden" name="token" value="{{ $request->route('token')}}">--}}
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
                                                <input type="password" name="password" value="{{old('password')}}" class="form-control"
                                                       id="password" required>
                                                <div class="invalid-feedback">Введите пароль</div>
                                            </div>
                                            <div class="col-12">
                                                <label for="password_confirmation" class="form-label">Подтверждение
                                                    пароля</label>
                                                <input type="password" name="password_confirmation" class="form-control"
                                                       id="password_confirmation" required>
                                                <div class="invalid-feedback">Введите пароль</div>
                                            </div>
                                            <div class="col-12">
                                                <button class="btn btn-primary w-100" type="submit">
                                                    {{ __('Подтвердить') }}
                                                </button>
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
