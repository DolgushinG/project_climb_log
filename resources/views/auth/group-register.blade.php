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
                                @auth
                                <form method="POST" action="#">
                                    @csrf
                                    <!-- Creator Information (Read-Only) -->
                                    <div class="form-group">
                                        <label for="surname">Имя (Кто оформляет)</label>
                                        <input type="text" class="form-control" id="surname" value="{{ auth()->user()->firstname }}" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label for="name">Фамилия (Кто оформляет)</label>
                                        <input type="text" class="form-control" id="name" value="{{ auth()->user()->lastname }}" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label for="email">Email (Кто оформляет)</label>
                                        <input type="email" class="form-control" id="email" value="{{ auth()->user()->email }}" readonly>
                                    </div>

                                    <div id="participants">
                                        <div class="participant-form">
                                            <h5>Участник 1</h5>
                                            <div class="form-group">
                                                <label for="surname">Фамилия</label>
                                                <input type="text" class="form-control" name="participants[0][surname]" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="name">Имя</label>
                                                <input type="text" class="form-control" name="participants[0][name]" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="middle_name">Отчество</label>
                                                <input type="text" class="form-control" name="participants[0][middle_name]">
                                            </div>
                                            <div class="form-group">
                                                <label for="dob">Дата рождения (опционально)</label>
                                                <input type="date" class="form-control" name="participants[0][dob]">
                                            </div>
                                            <div class="form-group">
                                                <label for="rank">Разряд (опционально)</label>
                                                <input type="text" class="form-control" name="participants[0][rank]">
                                            </div>
                                            <div class="form-group">
                                                <label for="team">Команда/Тренер (опционально)</label>
                                                <input type="text" class="form-control" name="participants[0][team]">
                                            </div>
                                            <div class="form-group">
                                                <label for="set">Сет (опционально)</label>
                                                <input type="text" class="form-control" name="participants[0][set]">
                                            </div>
                                            <div class="form-group">
                                                <label for="email">Email (опционально)</label>
                                                <input type="email" class="form-control" name="participants[0][email]">
                                            </div>
                                            <!-- Кнопка удаления -->
                                            <button type="button" class="btn btn-danger remove-participant">Удалить участника</button>
                                        </div>
                                    </div>
                                    <button type="button" id="add-participant" class="btn btn-primary">Добавить участника</button>
                                    <button type="submit" class="btn btn-success">Отправить</button>
                                </form>
                                @endauth
                            </div>
                        </div>
                    </div>
            </section>
            </div>
        </main>
    @endguest
    <script>
        let participantCount = 1;

        document.getElementById('add-participant').addEventListener('click', function () {
            participantCount++;

            let participantForm = `
        <div class="participant-form">
            <h5>Участник ${participantCount}</h5>
            <div class="form-group">
                <label for="surname">Фамилия</label>
                <input type="text" class="form-control" name="participants[${participantCount - 1}][surname]" required>
            </div>
            <div class="form-group">
                <label for="name">Имя</label>
                <input type="text" class="form-control" name="participants[${participantCount - 1}][name]" required>
            </div>
            <div class="form-group">
                <label for="middle_name">Отчество</label>
                <input type="text" class="form-control" name="participants[${participantCount - 1}][middle_name]">
            </div>
            <div class="form-group">
                <label for="dob">Дата рождения (опционально)</label>
                <input type="date" class="form-control" name="participants[${participantCount - 1}][dob]">
            </div>
            <div class="form-group">
                <label for="rank">Разряд (опционально)</label>
                <input type="text" class="form-control" name="participants[${participantCount - 1}][rank]">
            </div>
            <div class="form-group">
                <label for="team">Команда/Тренер (опционально)</label>
                <input type="text" class="form-control" name="participants[${participantCount - 1}][team]">
            </div>
            <div class="form-group">
                <label for="set">Сет (опционально)</label>
                <input type="text" class="form-control" name="participants[${participantCount - 1}][set]">
            </div>
            <div class="form-group">
                <label for="email">Email (опционально)</label>
                <input type="email" class="form-control" name="participants[${participantCount - 1}][email]">
            </div>
            <button type="button" class="btn btn-danger remove-participant">Удалить участника</button>
        </div>
    `;

            document.getElementById('participants').insertAdjacentHTML('beforeend', participantForm);
        });

        // Обработчик для удаления форм участников
        document.getElementById('participants').addEventListener('click', function (event) {
            if (event.target.classList.contains('remove-participant')) {
                event.target.closest('.participant-form').remove();
            }
        });

    </script>
    <script type="text/javascript" src="{{ asset('js/ddata.js') }}"></script>
@endsection
