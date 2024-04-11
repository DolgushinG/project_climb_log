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
                                        <p class="text-center small">Восстановление пароля</p>
                                        <form method="POST" class="row g-3 needs-validation" action="{{ route('password.email') }}">
                                            @csrf
                                            <p class="text-center small">Введите ваш email</p>
                                            <div class="col-12">
                                                <label for="email" class="form-label">Email</label>
                                                <div class="input-group has-validation">
                                                    <span class="input-group-text" id="inputGroupPrepend">@</span>
                                                    <input type="email" id="email" name="email" class="form-control"
                                                           value="{{old('email')}}" required autofocus>
                                                    <div class="invalid-feedback">Пожалуйста введите email</div>
                                                </div>
                                            </div>
                                            @if($errors->any())
                                                @foreach($errors->all() as $error)
                                                    <span class="text-uppercase text-danger d-block mb-3" data-aos="fade-left" data-aos-delay="300">{{ $error }}</span>
                                                @endforeach
                                            @endif
                                            <div class="col-12">
                                                <button class="btn btn-primary w-100" type="submit">
                                                    {{ __('Отправить письмо о сбросе пароля') }}
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
