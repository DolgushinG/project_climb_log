@extends('layouts.main_page.app')
@section('content')

    <!-- Slider Start -->
    <main id="main" class="main">
        <section class="section contact">
            <div class="row gy-4">
                <div class="col-xl-6">
                    <div class="card p-4">
                        <div class="container">
                            <h4 class="text-center">{{$event->title}}</h4>
                            <img class="img-fluid rounded py-4" src="{{asset('storage/'.$event->image)}}">
                            <div class="d-grid gap-2 mt-3">
                                @guest
                                    <a href="{{route('login')}}" class="btn btn-dark rounded-pill">Войти для участия</a>
                                @endguest
                                @auth

                                    <div class="form-floating mb-3">
                                        <select class="form-select" id="floatingSelectChangeSet"
                                                aria-label="Floating label select example" required>
                                            @foreach($sets as $set)
                                                @if($set->number_set == \App\Models\Participant::participant_number_set(Auth()->user()->id, $event->id))
                                                    <option selected value="{{\App\Models\Participant::participant_number_set(Auth()->user()->id, $event->id)}}">Сет {{\App\Models\Participant::participant_number_set(Auth()->user()->id, $event->id)}} (@lang('somewords.'.$set->day_of_week)) {{$set->time}} (еще
                                                        мест {{$set->free}})</option>
                                                @else
                                                    @if($set->free != 0)
                                                        <option value="{{$set->number_set}}">Сет {{$set->number_set}}
                                                            (@lang('somewords.'.$set->day_of_week)) {{$set->time}} (еще
                                                            мест {{$set->free}})
                                                        </option>
                                                    @endif
                                                @endif
                                            @endforeach
                                        </select>
                                        <label for="floatingSelectChangeSet">Выбрать время для сета</label>
                                    </div>
                                    <button id="btn-participant-change-set" data-id="{{$event->id}}"
                                            data-title="{{$event->title_eng}}" data-user-id="{{Auth()->user()->id}}"
                                            class="btn btn-dark rounded-pill">Изменить сет</button>
                                    @if(\App\Models\User::user_participant($event->id))
                                        <button href="{{route('takePart')}}" disabled
                                                class="btn border-t-neutral-500 rounded-pill">Вы принимаете участие
                                        </button>
                                        @if(!$event->is_qualification_counting_like_final)
                                            @if(\App\Models\ResultParticipant::participant_with_result(Auth()->user()->id, $event->id))
                                                <button href="#" class="btn border-t-neutral-500 rounded-pill" disabled>Вы
                                                    внесли результаты
                                                </button>
                                            @else
                                                <a href="{{route('listRoutesEvent', $event->title_eng)}}"
                                                   class="btn btn-success rounded-pill">Внести результаты</a>
                                            @endif
                                        @endif

                                    @else

                                        @if($event->is_input_birthday)
                                            @if(!Auth::user()->birthday)
                                            <label for="inputDate" class="col col-form-label">Укажите дату рождения</label>
                                            <div class="col-sm-10">
                                                <input name="birthday" id="birthday" type="date" class="form-control">
                                            </div>
                                            @else
                                                <div class="col-sm-10" style="display: none">
                                                    <input name="birthday" id="birthday" type="date" class="form-control">
                                                </div>
                                            @endif
                                        @endif
                                            @if($event->is_need_sport_category)
                                                @if(!Auth::user()->sport_category)
                                                    <div class="form-floating mb-3">
                                                        <select class="form-select" id="floatingSelectSportCategory"
                                                                aria-label="Floating label select example" required>
                                                            <option selected disabled value="">Открыть для выбора разряда
                                                            </option>
                                                            @foreach ($sport_categories as $category)
                                                                <option value="{{$category}}">{{$category}}</option>
                                                            @endforeach
                                                        </select>
                                                        <label for="floatingSelectSportCategory">Требуется указать разряд</label>
                                                    </div>
                                                @else
                                                    <select class="form-select" id="floatingSelectSportCategory"
                                                            aria-label="Floating label select example" required style="display: none">
                                                        <option selected disabled value="">Открыть для выбора разряда
                                                        </option>
                                                        @foreach ($sport_categories as $category)
                                                            <option value="{{$category}}">{{$category}}</option>
                                                        @endforeach
                                                    </select>
                                                @endif
                                            @endif
                                        @if(!Auth::user()->gender)
                                            <div class="form-floating mb-3">
                                                <select class="form-select" id="floatingSelectGender"
                                                        aria-label="Floating label select example" required>
                                                    <option selected disabled value="">Отметить пол
                                                    </option>
                                                        <option value="male">M</option>
                                                        <option value="female">Ж</option>
                                                </select>
                                                <label for="floatingSelectGender">Отметить пол</label>
                                            </div>
                                            @else
                                                <div class="form-floating mb-3" style="display: none">
                                                    <select class="form-select" id="floatingSelectGender"
                                                            aria-label="Floating label select example" required>
                                                        @if(Auth::user()->gender == 'male')
                                                            <option disabled value="">Отметить пол</option>
                                                            <option selected value="male">M</option>
                                                            <option value="female">Ж</option>
                                                        @else
                                                            <option disabled value="">Отметить пол</option>
                                                            <option value="male">M</option>
                                                            <option selected value="female">Ж</option>
                                                        @endif
                                                    </select>
                                                    <label for="floatingSelectGender">Отметить пол</label>
                                                </div>
                                        @endif
                                        <div class="form-floating mb-3">
                                            <select class="form-select" id="floatingSelect"
                                                    aria-label="Floating label select example" required>
                                                <option selected disabled value="">Открыть для выбора сета</option>
                                                @foreach($sets as $set)
                                                    @if($set->free != 0)
                                                        <option value="{{$set->number_set}}">Сет {{$set->number_set}}
                                                            (@lang('somewords.'.$set->day_of_week)) {{$set->time}} (еще
                                                            мест {{$set->free}})
                                                        </option>
                                                    @endif
                                                @endforeach
                                            </select>
                                            <label for="floatingSelect">Выбрать время для сета</label>
                                        </div>
                                        <div class="form-floating mb-3">
                                            <select class="form-select" id="floatingSelectCategory"
                                                    aria-label="Floating label select example" required>
                                                <option selected disabled value="">Открыть для выбора категории
                                                </option>
                                                @foreach($event->categories as $category)
                                                    <option
                                                        value="{{$category}}">{{$category}}</option>
                                                @endforeach
                                            </select>
                                            <label for="floatingSelectCategory">Выбрать категорию</label>
                                        </div>
                                        <button id="btn-participant" data-id="{{$event->id}}"
                                           data-title="{{$event->title_eng}}" data-format="{{$event->is_qualification_counting_like_final}}" data-user-id="{{Auth()->user()->id}}"
                                           class="btn btn-dark rounded-pill" style="display: none">Участвовать</button>

                                    @endif
                                @endauth
                                <a href="{{route('participants', [$event->climbing_gym_name_eng, $event->title_eng])}}"
                                   class="btn btn-primary rounded-pill">Список участников</a>

                                @if(!$event->is_qualification_counting_like_final)
                                <a href="{{route('final_results',[$event->climbing_gym_name_eng, $event->title_eng])}}"
                                   class="btn btn-primary rounded-pill">Предворительные результаты</a>
                                @endif
                            </div>
                        </div>
                    </div>

                </div>
                <div class="col-xl-6">


                    <div class="card">
                        <div class="card-body">
                            <!-- Bordered Tabs Justified -->
                            <ul class="nav nav-pills nav-tabs-bordered d-flex" id="borderedTabJustified" role="tablist">
                                <li class="nav-item flex-fill" role="presentation">
                                    <button class="nav-link w-100 active" id="info-tab" data-bs-toggle="tab"
                                            data-bs-target="#bordered-justified-info" type="button" role="tab"
                                            aria-controls="info" aria-selected="true">Общая информация
                                    </button>
                                </li>
                                <li class="nav-item flex-fill" role="presentation">
                                    <button class="nav-link w-100" id="home-tab" data-bs-toggle="tab"
                                            data-bs-target="#bordered-justified-home" type="button" role="tab"
                                            aria-controls="home" aria-selected="true">Сеты
                                    </button>
                                </li>
                                <li class="nav-item flex-fill" role="presentation">
                                    <button class="nav-link w-100" id="profile-tab" data-bs-toggle="tab"
                                            data-bs-target="#bordered-justified-profile" type="button" role="tab"
                                            aria-controls="profile" aria-selected="false">Положение
                                    </button>
                                </li>
                                <li class="nav-item flex-fill" role="presentation">
                                    <button class="nav-link w-100" id="contact-tab" data-bs-toggle="tab"
                                            data-bs-target="#bordered-justified-contact" type="button" role="tab"
                                            aria-controls="contact" aria-selected="false">Стартовый взнос
                                    </button>
                                </li>
                            </ul>
                            <div class="tab-content pt-2" id="borderedTabJustifiedContent">
                                <div class="tab-pane fade show" id="bordered-justified-home" role="tabpanel"
                                     aria-labelledby="home-tab">
                                    <div class="info-box card z-depth-3">
                                        <div class="container">
                                            <div class="row">
                                                <h5 class="card-title">Заполняемость сетов</h5>
                                                @foreach($sets as $set)
                                                    @if($set->free != 0)
                                                        <label>Сет {{$set->number_set}}-{{$set->time}}
                                                            (@lang('somewords.'.$set->day_of_week))(Свободно
                                                            - {{100 - $set->procent}}%)</label>
                                                    @else
                                                        <label>Сет {{$set->number_set}}-{{$set->time}}
                                                            (@lang('somewords.'.$set->day_of_week)) (Полностью забит)</label>
                                                    @endif
                                                        <div class="container">
                                                            <div class="progress mt-1 pl-3">
                                                                <div class="progress-bar" role="progressbar"
                                                                     style="width: {{$set->procent}}%" aria-valuenow="{{$set->free}}"
                                                                     aria-valuemin="0" aria-valuemax="{{$set->max_participants}}"></div>
                                                            </div>
                                                        </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane fade show active" id="bordered-justified-info" role="tabpanel"
                                     aria-labelledby="info-tab">
                                    <section id="services" class="services">
                                        <div class="container" data-aos="fade-up">

                                            <div class="section-title">
                                                <h2>Адрес</h2>
                                                <p>{{$event->city}}</p>
                                                <p>{{$event->address}}</p>
                                                <p>{{$event->climbing_gym_name}}</p>
                                            </div>
                                            <div class="section-title">
                                                <h2>Контакты</h2>
                                                <p>{{$event->contact}}</p>
                                            </div>
                                        </div>
                                    </section>
                                </div>
                                <div class="tab-pane fade" id="bordered-justified-profile" role="tabpanel"
                                     aria-labelledby="profile-tab">
                                    <p>{!! $event->description !!}</p>
                                </div>
                                <div class="tab-pane fade" id="bordered-justified-contact" role="tabpanel"
                                     aria-labelledby="contact-tab">
                                    <div class="container">
                                        <div class="row">
                                            <div class="container text-center pt-2 pb-2">
                                                 <label> Сумма к оплате: </label>
                                                <span class="badge bg-success" style="font-size: 22px"> {{$event->amount_start_price}} руб </span><br>
                                            </div>
                                            @if($event->info_payment)
                                                <p>{!! $event->info_payment !!}</p>
                                            @endif
                                            @if($event->link_payment)
                                            <div class="container text-center pt-2 pb-2">
                                                <a class="btn btn-primary" style="font-size: 22px" href="{{$event->link_payment}}">Оплатить</a><br>
                                            </div>
                                            @endif
                                            @if($event->img_payment)
                                                <img class="img-fluid img-responsive" src="{{asset('storage/'.$event->img_payment)}}" alt="qr">
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div><!-- End Bordered Tabs Justified -->

                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main><!-- End #main -->
    <script src="{{asset('js/welcome.js')}}"></script>
@endsection
