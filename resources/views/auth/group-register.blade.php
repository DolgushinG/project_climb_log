@extends('layouts.main_page.app')
@section('content')
    <link href="{{asset('vendor/helpers/css_suggestions.css')}}" rel="stylesheet" />
    <script src="{{asset('vendor/helpers/jquery.suggestions.js')}}"></script>
    <section class="d-flex align-items-center">
        <div class="container" data-aos="zoom-out" data-aos-delay="100">
        </div>
    </section><!-- End Hero -->
    <main id="main">
        <div class="container">
            <section class="section register min-vh-100 d-flex flex-column align-items-center justify-content-center py-4">
                <div class="container">
                    <div class="row justify-content-center">
                        <section class="section-title">
                            <h1> Оформление заявки для группы</h1>
                        </section>
                            @auth
                            <form method="POST" action="{{route('group_registration', [$event->id])}}">
                                @csrf
                                <h3>Данные заявителя </h3>
                                <div class="row" style="border: 1px solid #ddd; background-color: #f9f9f9; box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); padding: 15px; margin-bottom: 20px;">
                                    <!-- Фамилия -->
                                    <div class="form-group col-md-4 col-12">
                                        <label for="firstname">Фамилия</label>
                                        <input type="text" class="form-control" id="firstname" value="{{ auth()->user()->firstname }}" readonly>
                                    </div>
                                    <!-- Имя -->
                                    <div class="form-group col-md-4 col-12">
                                        <label for="lastname">Имя</label>
                                        <input type="text" class="form-control" id="lastname" value="{{ auth()->user()->lastname }}" readonly>
                                    </div>

                                    <!-- Email -->
                                    <div class="form-group col-md-4 col-12">
                                        <label for="email">Email</label>
                                        <input type="email" class="form-control" id="email" value="{{ auth()->user()->email }}" readonly>
                                    </div>
                                </div>
                                <div id="participants">
                                </div>
                                <button type="button" id="add-participant" class="btn btn-primary m-3">Добавить участника</button>
                                <button type="submit" class="btn btn-success">Отправить</button>
                            </form>
                            @endauth
                    </div>
                </div>
        </section>
        </div>
    </main>
    <script>

        document.getElementById('add-participant').addEventListener('click', function () {
            const participantCount = document.querySelectorAll('.participant-form').length + 1 ;

            // Структура формы с использованием Bootstrap Grid
            let participantForm = `
        <div class="participant-form row" style="border: 1px solid #ddd; background-color: #f9f9f9; box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); padding: 15px; margin-bottom: 20px;">
            <h5 class="col-12">Участник ${participantCount}</h5>

            <div class="form-group col-md-3 col-12 m-1">
                  <label for="firstname">Фамилия</label>
                <input type="text" class="form-control" name="participants[${participantCount - 1}][firstname]" required>
            </div>

            <div class="form-group col-md-3 col-12 m-1">
                <label for="lastname">Имя</label>
                <input type="text" class="form-control" name="participants[${participantCount - 1}][lastname]" required>
            </div>
            <div class="form-group col-md-3 col-12 m-1">
                <label for="dob">Дата рождения (опционально)</label>
                <input type="date" class="form-control" name="participants[${participantCount - 1}][dob]">
            </div>
            <div class="form-group col-md-3 col-12 m-1">
              <label for="gender" class="control-label">Пол</label>
                <select class="form-select" name="participants[${participantCount - 1}][gender]" id="gender" required>
                    <option selected disabled value="">Укажите пол...</option>
                    <option id="male" value="male">
                        М
                    </option>
                    <option id="female" value="female">
                        Ж
                    </option>
                </select>
            </div>
            <div class="form-group col-md-3 col-12 m-1">
                <label for="sport_categories">Разряд</label>
                <select class="form-select" name="participants[${participantCount - 1}][sport_categories]" id="sport_categories"
                        aria-label="Floating label select example" autocomplete="off" required>
                    <option selected disabled value="">Открыть для выбора разряда
                    </option>
                    @foreach ($sport_categories as $category)
                <option value="{{$category}}">{{$category}}</option>
                    @endforeach
                </select>

            </div>
            <div class="form-group col-md-3 col-12 m-1">
                <label for="team">Команда/Тренер (опционально)</label>
                <input type="text" class="form-control" name="participants[${participantCount - 1}][team]">
            </div>
            <div class="form-group col-md-3 col-12 m-1">
                    <label for="sets">Выбрать время для сета</label>
                  <select class="form-select" id="sets" name="participants[${participantCount - 1}][sets]"
                aria-label="Floating label select example" required>
            <option selected disabled value="">Открыть для выбора сета</option>
            @foreach($sets as $set)
                @if($set->free > 0)
                    <option data-free="{{$set->free}}" value="{{$set->number_set}}">Сет {{$set->number_set}}
                @lang('somewords.'.$set->day_of_week)
                @isset($set->date[$set->day_of_week])
                    {{$set->date[$set->day_of_week]}}
                @endisset
                {{$set->time}} (еще мест {{$set->free}})
                    </option>
                @else
                @if($set->free > 0)
                    <option data-free="{{$set->free}}" value="{{$set->number_set}}">Сет {{$set->number_set}}
                @lang('somewords.'.$set->day_of_week)
                @isset($set->date[$set->day_of_week])
                    {{$set->date[$set->day_of_week]}}
                @endisset
                {{$set->time}} (еще мест {{$set->free}})
                    </option>
                        @else
                    <option disabled data-free="{{$set->free}}" value="{{$set->number_set}}">Сет {{$set->number_set}}
                @lang('somewords.'.$set->day_of_week)
                @isset($set->date[$set->day_of_week])
                    {{$set->date[$set->day_of_week]}}
                @endisset
                {{$set->time}} (мест нет)  </option>
                        @endif
                @endif
            @endforeach
            </select>

            </div>
            <div class="form-group col-md-3 col-12 m-1">
                <label for="email">Email (опционально)</label>
                <input type="email" class="form-control" name="participants[${participantCount - 1}][email]">
            </div>
            <div class="form-group col-12 m-1">
                <button type="button" class="btn btn-danger remove-participant mt-2">Удалить участника</button>
            </div>
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
