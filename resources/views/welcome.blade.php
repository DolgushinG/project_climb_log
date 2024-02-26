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
                                    @if(\App\Models\User::user_participant($event->id))
                                        <button href="{{route('takePart')}}" disabled
                                                class="btn border-t-neutral-500 rounded-pill">Вы принимаете участие
                                        </button>
                                        @if(\App\Models\ResultParticipant::participant_with_result(Auth()->user()->id, $event->id))
                                            <button href="#" class="btn border-t-neutral-500 rounded-pill" disabled>Вы
                                                внесли результаты
                                            </button>
                                        @else
                                            <a href="{{route('listRoutesEvent', $event->title_eng)}}"
                                               class="btn btn-success rounded-pill">Внести результаты</a>
                                        @endif

                                    @else
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
                                                @foreach($event->categories as $index => $category)
                                                    <option
                                                        value="{{$category}}">{{$category}}</option>
                                                @endforeach
                                            </select>
                                            <label for="floatingSelectCategory">Выбрать категорию</label>
                                        </div>
                                        <button id="btn-participant" data-id="{{$event->id}}"
                                           data-title="{{$event->title_eng}}" data-user-id="{{Auth()->user()->id}}"
                                           class="btn btn-dark rounded-pill" style="display: none">Участвовать</button>

                                    @endif
                                @endauth
                                <a href="{{route('participants', [$event->climbing_gym_name_eng, $event->title_eng])}}"
                                   class="btn btn-primary rounded-pill">Список участников</a>
                                <a href="{{route('final_results',[$event->climbing_gym_name_eng, $event->title_eng])}}"
                                   class="btn btn-primary rounded-pill">Предворительные результаты</a>

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
                                    <div class="row">
                                        <div class="col-lg-6">
                                            <div class="info-box card z-depth-3">
                                                <i class="bi bi-geo-alt"></i>
                                                <h3>Адрес</h3>
                                                <p>{{$event->city}}</p>
                                                <p>{{$event->address}}</p>
                                                <p>{{$event->climbing_gym_name}}</p>
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="info-box card z-depth-3">
                                                <i class="bi bi-telephone"></i>
                                                <h3>Связь с организатором</h3>
                                                <p>{{$event->contact}}</p>
                                            </div>
                                        </div>
                                        <div class="col-lg-6 pt-4">
                                            <div class="info-box card z-depth-3">
                                                <i class="bi bi-clock"></i>
                                                <h3>Старт</h3>
                                                <p>{{$event->start_date}} {{$event->start_time}} -
                                                    <br>{{$event->end_date}} {{$event->end_time}}</p>
                                            </div>
                                        </div>
                                    </div>
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
                                                <span class="badge bg-success" style="font-size: 22px"> 4500 руб </span><br>
                                            </div>
                                            <div class="container text-center pt-2 pb-2">
                                                <a class="btn btn-primary" style="font-size: 22px" href="{{$event->link_payment}}">Оплатить</a><br>
                                            </div>
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
