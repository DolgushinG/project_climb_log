@extends('layouts.main_page.app')
@section('content')
    <link href="{{asset('vendor/helpers/css_suggestions.css')}}" rel="stylesheet" />
    <script src="{{asset('vendor/helpers/jquery.suggestions.js')}}"></script>
    @guest
        <section class="d-flex align-items-center">
            <div class="container" data-aos="zoom-out" data-aos-delay="100">
            </div>
        </section><!-- End Hero -->
        <main id="main">
            <div class="container">
                <section class="section register min-vh-100 d-flex flex-column align-items-center justify-content-center py-4">
                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="col-lg-4 col-md-6 d-flex flex-column align-items-center justify-content-center">

                                <div class="card mb-3">

                                    <div class="card-body">
                                        @if($errors->any())
                                            @foreach($errors->all() as $error)
                                                <span class="text-uppercase text-danger d-block mb-3" data-aos="fade-left" data-aos-delay="300">{{ $error }}</span>
                                            @endforeach
                                        @endif
                                        <div class="pt-4 pb-2">
                                            <h5 class="card-title text-center pb-0 fs-4">Регистрация аккаунта</h5>
                                            <p class="text-center small">Введите данные для регистрации</p>
                                        </div>

                                        <form method="POST" action="{{ route('register') }}"
                                              class="row g-3 needs-validation">
                                            @csrf

                                            <!-- Name -->
                                            <div class="col-12 form-group required">
                                                <label for="firstname" class="control-label">Ваше Имя</label>
                                                <input type="text" name="firstname" placeholder="Имя" class="form-control" id="firstname"
                                                       value="{{old('firstname')}}" required autofocus>
                                                <div class="invalid-feedback">Пожалуйста введите ваше имя</div>
                                            </div>
                                            <div class="col-12 form-group required">
                                                <label for="lastname" class="control-label">Ваша Фамилия</label>
                                                <input type="text" name="lastname" placeholder="Фамилия" class="form-control" id="lastname"
                                                       value="{{old('lastname')}}" required autofocus>
                                                <div class="invalid-feedback">Пожалуйста введите вашу фамилию</div>
                                            </div>
                                            <div class="col-12 form-group required">
                                                <label for="gender" class="control-label">Пол</label>
                                                <select class="form-select" name="gender" id="gender" required>
                                                    <option selected disabled value="">Укажите пол...</option>
                                                    <option id="male" value="male">
                                                        М
                                                    </option>
                                                    <option id="female" value="female">
                                                        Ж
                                                    </option>
                                                </select>
                                            </div>
                                            <div class="col-12 form-group required">
                                                <label for="email" class="control-label">Email</label>
                                                <input type="text" name="email" placeholder="Почта" class="form-control" id="email"
                                                       value="{{old('email')}}" required autocomplete="off">
                                                <div class="invalid-feedback">Введите email</div>
                                            </div>

                                            <div class="col-12 form-group required">
                                                <label for="password" class="control-label">Пароль</label>
                                                <input type="password" name="password" placeholder="Минимальная длина 8 символов" class="form-control"
                                                       id="password" required>
                                                <div class="invalid-feedback">Введите пароль</div>
                                            </div>
                                            <div class="col-12 form-group required">
                                                <label for="password_confirmation" class="control-label">Подтверждение
                                                    пароля</label>
                                                <input type="password" name="password_confirmation" placeholder="Подтверждение пароля" class="form-control"
                                                       id="password_confirmation" required>
                                                <div class="invalid-feedback">Введите пароль</div>
                                            </div>
                                            <div class="col-12">
                                                <div class="form-check form-group required">
                                                    <input class="form-check-input" name="terms" type="checkbox" value="" id="acceptTerms" required>
                                                    <label class="form-check-label control-label" for="acceptTerms">Я даю согласие на обработку моих <br> персональных данных
                                                        в порядке и на условиях, указанных в <a href="{{route('privacyconf')}}">согласии.</a></label>
                                                    <div class="invalid-feedback">Для регистрации требуется согласиться на обработку персональных данных.</div>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <button id="submit" class="btn btn-primary w-100" type="submit">Создать аккаунт
                                                </button>
                                            </div>
                                            <div class="col-12">
                                                <p class="small mb-0">Уже есть аккаунт <a href="{{ route('login') }}">Войти</a>
                                                </p>
                                            </div>
                                        </form>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </main>
    @endguest

    <script type="text/javascript" src="{{ asset('js/ddata.js') }}"></script>
@endsection

