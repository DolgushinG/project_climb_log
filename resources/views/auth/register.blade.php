@extends('layouts.main_page.app')
@section('content')
    <link href="https://cdn.jsdelivr.net/npm/suggestions-jquery@20.3.0/dist/css/suggestions.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/suggestions-jquery@20.3.0/dist/js/jquery.suggestions.min.js"></script>
    <!--добавил библиотеки jQuery UI -->

    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
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
                                            <div class="col-12">
                                                <label for="firstname" class="form-label">Ваше Имя</label>
                                                <input type="text" name="firstname" placeholder="Имя" class="form-control" id="firstname"
                                                       value="{{old('firstname')}}" required autofocus>
                                                <div class="invalid-feedback">Пожалуйста введите ваше имя</div>
                                            </div>
                                            <div class="col-12">
                                                <label for="lastname" class="form-label">Ваша Фамилия</label>
                                                <input type="text" name="lastname" placeholder="Фамилия" class="form-control" id="lastname"
                                                       value="{{old('lastname')}}" required autofocus>
                                                <div class="invalid-feedback">Пожалуйста введите вашу фамилию</div>
                                            </div>
                                            <div class="col-12">
                                                <label for="gender" class="form-label">Пол</label>
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
                                            <div class="col-12">
                                                <label for="birthday" class="form-label">Дата рождения</label>
                                                <div class="input-group has-validation">
                                                    <input type="date" name="birthday" placeholder="гг-мм-дд" class="form-control" id="birthday"
                                                           value="{{old('birthday')}}" required autofocus>
                                                    <div class="invalid-feedback">Введите дату рождения</div>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <label for="city" class="form-label">Город</label>
                                                <div class="input-group has-validation">
                                                    <input type="text" name="city" placeholder="Город" class="form-control" id="city"
                                                           value="{{old('city')}}" required autofocus>
                                                    <div class="invalid-feedback">Введите город</div>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <label for="team" class="form-label">Команда</label>
                                                <div class="input-group has-validation">
                                                    <input type="text" name="team" placeholder="Команда" class="form-control" id="team"
                                                           value="{{old('team')}}" required autofocus>
                                                    <div class="invalid-feedback">Введите команда</div>
                                                </div>
                                            </div>
                                            <!-- Email Address -->
                                            <div class="col-12">
                                                <label for="email" class="form-label">Email</label>
                                                <input type="email" name="email" placeholder="Почта" class="form-control" id="email"
                                                       value="{{old('email')}}" required autofocus>
                                                <div class="invalid-feedback">Введите email</div>
                                            </div>

                                            <div class="col-12">
                                                <label for="password" class="form-label">Пароль</label>
                                                <input type="password" name="password" placeholder="Пароль" class="form-control"
                                                       id="password" required>
                                                <div class="invalid-feedback">Введите пароль</div>
                                            </div>
                                            <div class="col-12">
                                                <label for="password_confirmation" class="form-label">Подтверждение
                                                    пароля</label>
                                                <input type="password" name="password_confirmation" placeholder="Подтверждение пароля" class="form-control"
                                                       id="password_confirmation" required>
                                                <div class="invalid-feedback">Введите пароль</div>
                                            </div>
                                            {{--                                    <div class="col-12">--}}
                                            {{--                                        <div class="form-check">--}}
                                            {{--                                            <input class="form-check-input" name="terms" type="checkbox" value="" id="acceptTerms" required>--}}
                                            {{--                                            <label class="form-check-label" for="acceptTerms">Я подтверждаю с ознакомлением <a href="#">моих </a></label>--}}
                                            {{--                                            <div class="invalid-feedback">You must agree before submitting.</div>--}}
                                            {{--                                        </div>--}}
                                            {{--                                    </div>--}}
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
        <script>
            // $( function() {
            //     $( "#birthday" ).datepicker({
            //         dateFormat: 'yy-mm-dd' // Формат даты, который вы хотите использовать
            //     });
            // });
        </script>
    @endguest<!-- End #main -->
    <!-- подключил календарь -->

{{--    <script src="{{ asset('js/datepicker.js') }}"></script>--}}
@endsection
