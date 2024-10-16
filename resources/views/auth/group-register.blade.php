@extends('layouts.main_page.app')
@section('content')
    <link href="{{asset('vendor/helpers/css_suggestions.css')}}" rel="stylesheet"/>
    <script src="{{asset('vendor/helpers/jquery.suggestions.js')}}"></script>
    <section class="d-flex align-items-center">
        <div class="container" data-aos="zoom-out" data-aos-delay="100">
        </div>
    </section><!-- End Hero -->
    <main id="main">
        <div class="container">
            <section
                class="section register min-vh-100 d-flex flex-column align-items-center justify-content-center py-4">
                <div class="container">
                    <div class="row justify-content-center">
                        <section class="section-title">
                            <h1> Оформление заявки для группы</h1>
                        </section>
                        @auth
                            <h3>Данные заявителя</h3>
                            <div class="row"
                                 style="border: 1px solid #ddd; background-color: #f9f9f9; box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); padding: 15px; margin-bottom: 20px;">
                                <!-- Фамилия -->
                                <div class="form-group col-md-4 col-12">
                                    <label for="firstname">Фамилия</label>
                                    <p>{{ auth()->user()->firstname }}</p>
                                </div>
                                <!-- Имя -->
                                <div class="form-group col-md-4 col-12">
                                    <label for="lastname">Имя</label>
                                    <p>{{ auth()->user()->lastname }}</p>
                                </div>
                                <!-- Email -->
                                <div class="form-group col-md-4 col-12">
                                    <label for="email">Email</label>
                                    <p>{{ auth()->user()->email }}</p>
                                </div>
                                <div class="form-group col-md-4 col-12">
                                    <label for="contact">Контактные данные</label>
                                    <p>{{ auth()->user()->contact ?? "Не заполнены, заполните у себя в личном кабинете" }} </p>
                                </div>
                                <p> Если у участников не заполнены поля Email, то они будут сгенерированы
                                    автоматически </p>
                                <p> Контактные данные участников будут заполнены контактными данными заявителя </p>
                                <p> В письме после отправки будут все данные для входа в личный кабинет заявленных
                                    участников</p>
                            </div>
                            <form id="group-registration-form" method="POST"
                                  action="{{ route('group_registration', [$event->id]) }}">
                                @csrf
                                <div id="participants">
                                    <!-- Чекбоксы, если есть данные в $related_users -->
                                    @if(!empty($related_users))
                                        <label>Ранее заявленные участники:</label>
                                        @foreach($related_users as $user)
                                            <div class="row"
                                                 style="border: 1px solid #ddd; background-color: #f9f9f9; box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); padding: 15px; margin-bottom: 20px;">
                                                <div class="form-group col-md-4 col-12">
                                                    <input type="checkbox" class="btn-check"
                                                           name="related_users[{{ $user->id }}][user_id]"
                                                           id="user-{{ $user->id }}" value="{{ $user->id }}"
                                                           autocomplete="off">
                                                    <label class="btn btn-outline-primary" for="user-{{ $user->id }}">
                                                        {{ $user->middlename }}
                                                    </label>
                                                </div>

                                                <div class="participant-details" id="details-{{ $user->id }}"
                                                     style="display: none;">
                                                    @if($event->is_need_sport_category)
                                                        <div class="form-group col-md-3 col-12 m-1">
                                                            <label for="sport_categories">Разряд</label>
                                                            <select class="form-select"
                                                                    name="related_users[{{ $user->id }}][sport_categories]"
                                                                    id="sport_categories_{{ $user->id }}"
                                                                    autocomplete="off" disabled>
                                                                <option selected disabled value="">Открыть для выбора
                                                                    разряда
                                                                </option>
                                                                @foreach($sport_categories as $category)
                                                                    <option value="{{$category}}">{{$category}}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    @endif
                                                    @if(!$event->is_auto_categories)
                                                        <div class="form-group col-md-3 col-12 m-1">
                                                            <label for="category_id">Категория участника</label>
                                                            <select class="form-select" id="category_{{ $user->id }}"
                                                                    name="related_users[{{ $user->id }}][category]"
                                                                    autocomplete="off" required disabled>
                                                                <option selected disabled value="">Открыть для выбора
                                                                    категории
                                                                </option>
                                                                @foreach($event->categories as $category)
                                                                    <option value="{{$category}}">{{$category}}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    @endif
                                                    @if(!$event->is_input_set)
                                                        <div class="form-group col-md-3 col-12 m-1">
                                                            <label for="sets">Выбрать время для сета</label>
                                                            <select class="form-select" id="sets_{{ $user->id }}"
                                                                    name="related_users[{{ $user->id }}][sets]"
                                                                    aria-label="Floating label select example" autocomplete="off" disabled required>
                                                                    @foreach($sets as $set)
                                                                        @if($event->is_input_birthday)
                                                                            @if(App\Helpers\Helpers::is_valid_year_for_event($event->id, $set->number_set, $user->birthday))
                                                                                @if($set->free > 0)
                                                                                    <option data-set=""
                                                                                            data-free="{{$set->free}}"
                                                                                            value="{{$set->number_set}}">
                                                                                        Сет {{$set->number_set}}
                                                                                        @lang('somewords.'.$set->day_of_week)
                                                                                        @isset($set->date[$set->day_of_week])
                                                                                            {{$set->date[$set->day_of_week]}}
                                                                                        @endisset
                                                                                        {{$set->time}} (еще
                                                                                        мест {{$set->free}})
                                                                                    </option>
                                                                                @else
                                                                                    <option data-set=""
                                                                                            data-free="{{$set->free}}"
                                                                                            value="{{$set->number_set}}">
                                                                                        Сет {{$set->number_set}}
                                                                                        @lang('somewords.'.$set->day_of_week)
                                                                                        @isset($set->date[$set->day_of_week])
                                                                                            {{$set->date[$set->day_of_week]}}
                                                                                        @endisset
                                                                                        {{$set->time}} (мест нет)
                                                                                    </option>
                                                                                @endif
                                                                            @endif
                                                                        @else
                                                                            @if($set->free > 0)
                                                                                <option data-free="{{$set->free}}"
                                                                                        value="{{$set->number_set}}">
                                                                                    Сет {{$set->number_set}}
                                                                                    @lang('somewords.'.$set->day_of_week)
                                                                                    @isset($set->date[$set->day_of_week])
                                                                                        {{$set->date[$set->day_of_week]}}
                                                                                    @endisset
                                                                                    {{$set->time}} (еще
                                                                                    мест {{$set->free}})
                                                                                </option>
                                                                            @else
                                                                                <option disabled data-free="{{$set->free}}"
                                                                                        value="{{$set->number_set}}">
                                                                                    Сет {{$set->number_set}}
                                                                                    @lang('somewords.'.$set->day_of_week)
                                                                                    @isset($set->date[$set->day_of_week])
                                                                                        {{$set->date[$set->day_of_week]}}
                                                                                    @endisset
                                                                                    {{$set->time}} (мест нет)
                                                                                </option>
                                                                            @endif
                                                                        @endif
                                                                    @endforeach
                                                            </select>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                                @if(\Illuminate\Support\Facades\Auth::user()->contact)
                                    <button type="button" id="add-participant" class="btn btn-primary m-3">Добавить
                                        участника
                                    </button>
                                    <button type="submit" id="btn-send" class="btn btn-success" disabled>Отправить
                                    </button>
                                @else
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        <p> Для регистрации группы на соревнование, необходимы контакты для быстрой
                                            связи</p>
                                        <p> Добавьте контактные данные для быстрой связи в вашем <a
                                                href="{{ route('profile') }}">профиле</a></p>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"
                                                aria-label="Close"></button>
                                    </div>
                                @endif
                            </form>
                        @endauth
                    </div>
                </div>
            </section>
        </div>
    </main>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.btn-check').forEach(function (checkbox) {
                checkbox.addEventListener('change', function () {
                    var userId = this.value;
                    var categorySelect = document.getElementById('category_' + userId);
                    var sportCategoriesSelect = document.getElementById('sport_categories_' + userId);
                    var setsSelect = document.getElementById('sets_' + userId);
                    if (this.checked) {
                        // Активируем селекты, если чекбокс выбран
                        if (categorySelect) categorySelect.removeAttribute('disabled');
                        if (sportCategoriesSelect) sportCategoriesSelect.removeAttribute('disabled');
                        if (setsSelect) setsSelect.removeAttribute('disabled');
                    } else {
                        // Отключаем селекты, если чекбокс не выбран
                        if (categorySelect) categorySelect.setAttribute('disabled', 'disabled');
                        if (sportCategoriesSelect) sportCategoriesSelect.setAttribute('disabled', 'disabled');
                        if (setsSelect) setsSelect.setAttribute('disabled', 'disabled');
                    }
                });
            });
        });
        document.querySelectorAll('#participants .btn-check').forEach(function (checkbox) {
            checkbox.addEventListener('change', function () {
                let details = document.getElementById('details-' + this.value);
                if (this.checked) {
                    details.style.display = 'block';
                } else {
                    details.style.display = 'none';
                }
            });
        });
        document.addEventListener('DOMContentLoaded', function () {
            const checkboxes = document.querySelectorAll('#participants .btn-check');
            const sendButton = document.getElementById('btn-send');

            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function () {
                    const anyChecked = Array.from(checkboxes).some(checkbox => checkbox.checked);
                    sendButton.disabled = !anyChecked;
                });
            });
        });
        document.getElementById('add-participant').addEventListener('click', function () {
            const participantCount = document.querySelectorAll('.participant-form').length + 1;
            if (participantCount >= 1) {
                document.getElementById('btn-send').disabled = false
            }
            let participantForm = `
        <div class="participant-form row" style="border: 1px solid #ddd; background-color: #f9f9f9; box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); padding: 15px; margin-bottom: 20px;">
            <h5 class="col-12">Участник ${participantCount}</h5>

            <div class="form-group col-md-3 col-12 m-1">
                  <label for="firstname">Фамилия</label>
                <input type="text" class="form-control russian-only" name="participants[${participantCount}][firstname]" required>
                <div class="invalid-feedback">Введите только русские буквы.</div>
            </div>

            <div class="form-group col-md-3 col-12 m-1">
                <label for="lastname">Имя</label>
                <input type="text" class="form-control russian-only" name="participants[${participantCount}][lastname]" required>
                <div class="invalid-feedback">Введите только русские буквы.</div>
            </div>
            <div class="form-group col-md-3 col-12 m-1">
                <label for="dob">Дата рождения</label>
                @if($event->is_input_birthday)
            <input type="date" id="dob${participantCount}" data-event-id${participantCount}="{{$event->id}}" class="form-control" name="participants[${participantCount}][dob]" required>
                @else
            <input type="date" data-event-id${participantCount}="{{$event->id}}" class="form-control" name="participants[${participantCount}][dob]" required>
                @endif
            </div>
            <div class="form-group col-md-3 col-12 m-1">
              <label for="gender" class="control-label">Пол</label>
                <select class="form-select" name="participants[${participantCount}][gender]" id="gender" required>
                    <option selected disabled value="">Укажите пол...</option>
                    <option id="male" value="male">
                        М
                    </option>
                    <option id="female" value="female">
                        Ж
                    </option>
                </select>
            </div>
            @if(!$event->is_auto_categories)
            <div class="form-group col-md-3 col-12 m-1">
                 <label for="category_id">Категория участника</label>
                 <select class="form-select" id="category_id"
                         aria-label="Floating label select" name="participants[${participantCount}][category_id]" autocomplete="off" required>
                     <option selected disabled value="">Открыть для выбора категории
                     </option>
                    @foreach($event->categories as $category)
            <option value="{{$category}}">{{$category}}</option>
                    @endforeach
            </select>
        </div>
@endif
            @if($event->is_need_sport_category)
            <div class="form-group col-md-3 col-12 m-1">
                <label for="sport_categories">Разряд</label>
                <select class="form-select" name="participants[${participantCount}][sport_categories]" id="sport_categories"
                        aria-label="Floating label select example" autocomplete="off" required>
                    <option selected disabled value="">Открыть для выбора разряда
                    </option>
                    @foreach ($sport_categories as $category)
            <option value="{{$category}}">{{$category}}</option>
                    @endforeach
            </select>
        </div>
@endif
            <div class="form-group col-md-3 col-12 m-1">
                <label for="team">Команда/Тренер (опционально)</label>
                <input type="text" class="form-control" name="participants[${participantCount}][team]">
            </div>
            @if(!$event->is_input_set)
            <div class="form-group col-md-3 col-12 m-1">
                    <label for="sets">Выбрать время для сета</label>
                <select class="form-select" id="sets${participantCount}" name="participants[${participantCount}][sets]"
                        aria-label="Floating label select example" required>
                        @if($event->is_input_birthday)
            <option selected disabled value="">Установите дату рождения</option>
@else
            <option selected disabled value="">Выберите сет</option>
@foreach($sets as $set)
            @if($set->free > 0)
            <option data-free="{{$set->free}}" value="{{$set->number_set}}">Сет {{$set->number_set}}
            @lang('somewords.'.$set->day_of_week)
            @isset($set->date[$set->day_of_week])
            {{$set->date[$set->day_of_week]}}
            @endisset
            {{$set->time}} (еще
                                        мест {{$set->free}})
                                       </option>
                                @else
            <option disabled data-free="{{$set->free}}" value="{{$set->number_set}}">Сет {{$set->number_set}}
            @lang('somewords.'.$set->day_of_week)
            @isset($set->date[$set->day_of_week])
            {{$set->date[$set->day_of_week]}}
            @endisset
            {{$set->time}} (мест нет)
                                                </option>
                                @endif
            @endforeach
            @endif
            </select>
        </div>
@endif
            <div class="form-group col-md-3 col-12 m-1">
                <label for="email">Email (опционально)</label>
                <input type="email" class="form-control" name="participants[${participantCount}][email]">
            </div>
            <div class="form-group col-12 m-1">
                <button type="button" class="btn btn-danger remove-participant mt-2">Удалить участника</button>
            </div>
        </div>
    `;
            document.querySelectorAll('.russian-only').forEach(function (inputField) {
                inputField.addEventListener('input', function () {
                    const russianRegex = /^[А-Яа-яЁё\s-]+$/;
                    if (!russianRegex.test(this.value)) {
                        this.classList.add('is-invalid');  // Добавляем класс ошибки
                    } else {
                        this.classList.remove('is-invalid');  // Убираем класс ошибки
                    }
                });
            });
            document.getElementById('participants').insertAdjacentHTML('beforeend', participantForm);
            let selector = '[id=dob' + participantCount + ']'
            const dob = document.querySelector(selector);

            let debounceTimeout;
            let lastController = null; // Для хранения текущего AbortController

            // Проверяем, есть ли элемент dob и не привязан ли уже обработчик
            if (dob && !dob.hasAttribute('data-listener-attached')) {
                // Устанавливаем атрибут, чтобы избежать множественного привязывания обработчика
                dob.setAttribute('data-listener-attached', 'true');

                dob.addEventListener('input', function () {
                    clearTimeout(debounceTimeout); // Сбросить предыдущий таймер

                    debounceTimeout = setTimeout(function () {
                        const dob_send = dob.value;
                        let event_sel = 'data-event-id' + participantCount
                        const eventId = dob.getAttribute(event_sel);
                        if (lastController) {
                            lastController.abort(); // Отменяем предыдущий запрос
                        }
                        lastController = new AbortController();
                        const signal = lastController.signal;

                        if (dob_send) {
                            fetch(`/get-available-sets?dob=${dob_send}&event_id=${eventId}`, {signal})
                                .then(response => {
                                    if (!response.ok) {
                                        throw new Error('Ошибка сети');
                                    }
                                    return response.json(); // Первый вызов .then()
                                })
                                .then(data => {
                                    let sets_id = '[id=sets' + participantCount + ']'
                                    const setsSelect = document.querySelector(sets_id);
                                    setsSelect.innerHTML = '<option value="">Выберите сет</option>';

                                    if (data.availableSets && data.availableSets.length > 0) {
                                        data.availableSets.forEach(set => {
                                            const option = document.createElement('option');
                                            option.value = set.number_set;
                                            option.textContent = set.time;
                                            setsSelect.appendChild(option);
                                        });
                                    } else {
                                        const option = document.createElement('option');
                                        option.value = '';
                                        option.textContent = 'Нет доступных сетов';
                                        setsSelect.appendChild(option);
                                    }
                                })
                                .catch(error => {
                                    console.error('Ошибка при запросе сетов:', error);
                                });
                        }
                    }, 500); // Задержка 500 мс перед выполнением запроса
                });
            }
        });

        // Обработчик для удаления форм участников
        document.getElementById('participants').addEventListener('click', function (event) {
            if (event.target.classList.contains('remove-participant')) {
                event.target.closest('.participant-form').remove();

                if (document.querySelectorAll('.participant-form').length < 1) {
                    document.getElementById('btn-send').disabled = true
                }
            }

        });
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('group-registration-form');

            form.addEventListener('submit', function (event) {
                let isValid = true;

                document.querySelectorAll('.russian-only').forEach(function (inputField) {
                    const russianRegex = /^[А-Яа-яЁё\s-]+$/;

                    if (!russianRegex.test(inputField.value)) {
                        inputField.classList.add('is-invalid');
                        isValid = false;
                    } else {
                        inputField.classList.remove('is-invalid');
                    }
                });

                if (!isValid) {
                    return // Предотвращаем отправку формы, если есть ошибки
                }
                event.preventDefault(); // Отменить стандартное поведение формы (перенаправление)

                const formData = new FormData(form);

                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest' // Чтобы Laravel определил, что это AJAX-запрос
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            let btn = $('#btn-send');
                            btn.text('').append('<i id="spinner" style="margin-left: 10px;\n' +
                                '    margin-right: 8px;" class="fa fa-spinner fa-spin"></i> Обработка...')
                            setTimeout(function () {
                                btn.text(data.message)
                            }, 2000);
                            setTimeout(function () {
                                btn.text('Отправить')
                                clear_form();
                            }, 5000);

                        } else {
                            // Если ошибка, показать соответствующее сообщение
                            let btn = $('#btn-send');
                            btn.text('').append('<i id="spinner" style="margin-left: 10px;\n' +
                                '    margin-right: 8px;" class="fa fa-spinner fa-spin"></i> Обработка...')
                            setTimeout(function () {
                                btn.text(data.message)
                            }, 3000);
                            setTimeout(function () {
                                btn.text('Отправить')
                            }, 4000);
                        }
                    })
                    .catch(error => {
                        let btn = $('#btn-send');
                        btn.text('').append('<i id="spinner" style="margin-left: -12px;\n' +
                            '    margin-right: 8px;" class="fa fa-spinner fa-spin"></i> Обработка...')
                        setTimeout(function () {
                            btn.text(error.message)
                        }, 3000);
                        setTimeout(function () {
                            btn.text('Отправить')
                        }, 4000);
                    });
            });
        });

    </script>
    <script type="text/javascript" src="{{ asset('js/ddata.js') }}"></script>
@endsection
