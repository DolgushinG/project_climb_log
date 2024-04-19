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
                                        @if(\App\Models\ResultParticipant::participant_with_result(Auth()->user()->id, $event->id))
                                                @if($event->is_qualification_counting_like_final)
                                                    @include('event.selects.sets_participant')
                                                    @include('event.buttons.participant_already')
                                                @else
                                                    @include('event.selects.sets_participant')
                                                    @include('event.buttons.participant_already')
                                                    @include('event.buttons.results_have_been_sent_already')
                                                @endif
                                        @else
                                            @include('event.selects.sets_participant')
                                            {{--Нужна оплата?--}}
                                            @if($event->is_need_pay_for_reg)
                                                @if(\App\Models\ResultParticipant::is_pay_participant(Auth()->user()->id, $event->id))
                                                        @if($event->is_qualification_counting_like_final)
                                                            @include('event.buttons.participant_already')
                                                        @else
                                                            @include('event.buttons.send_result')
                                                        @endif
                                                @else
                                                        @include('event.buttons.pay')
                                                @endif
                                            @else
                                                {{-- Француская система в ней результаты вносят судьи--}}
                                                @if($event->is_qualification_counting_like_final)
                                                        @include('event.buttons.participant_already')
                                                @else
                                                    {{-- Фестивальная система вносят результаты сами участники--}}
                                                        @include('event.buttons.send_result')
                                                @endif
                                            @endif

                                        @endif
                                    @else
                                        @include('event.selects.birthday')
                                        @include('event.selects.genders')
                                        @include('event.selects.categories')
                                        @include('event.selects.sets_take_part')
                                        @include('event.buttons.take_part')
                                        <div id="error-message" class="text-danger"></div>
                                    @endif
                                @endauth
                                <a href="{{route('participants', [$event->start_date, $event->climbing_gym_name_eng, $event->title_eng])}}"
                                   class="btn btn-primary rounded-pill">Список участников</a>
                                @if(!$event->is_qualification_counting_like_final)
                                    <a href="{{route('final_results',[$event->start_date, $event->climbing_gym_name_eng, $event->title_eng])}}"
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
                                <li class="nav-item flex-fill" role="presentation" style="margin-right: 8px!important;">
                                    <button class="nav-link w-100 active" id="info-tab" data-bs-toggle="tab"
                                            data-bs-target="#bordered-justified-info" type="button" role="tab"
                                            aria-controls="info" aria-selected="true">Общая информация
                                    </button>
                                </li>
{{--                                <li class="nav-item flex-fill" role="presentation">--}}
{{--                                    <button class="nav-link w-100" id="home-tab" data-bs-toggle="tab"--}}
{{--                                            data-bs-target="#bordered-justified-home" type="button" role="tab"--}}
{{--                                            aria-controls="home" aria-selected="true">Сеты--}}
{{--                                    </button>--}}
{{--                                </li>--}}
                                <li class="nav-item flex-fill" role="presentation" style="margin-right: 8px!important;">
                                    <button class="nav-link w-100" id="profile-tab" data-bs-toggle="tab"
                                            data-bs-target="#bordered-justified-profile" type="button" role="tab"
                                            aria-controls="profile" aria-selected="false">Положение
                                    </button>
                                </li>
                                <li class="nav-item flex-fill" role="presentation" style="margin-right: 8px!important;">
                                    <button class="nav-link w-100" id="contact-tab" data-bs-toggle="tab"
                                            data-bs-target="#bordered-justified-contact" type="button" role="tab"
                                            aria-controls="contact" aria-selected="false">Стартовый взнос
                                    </button>
                                </li>
                            </ul>
                            <div class="tab-content pt-2" id="borderedTabJustifiedContent">
{{--                                <div class="tab-pane fade show" id="bordered-justified-home" role="tabpanel"--}}
{{--                                     aria-labelledby="home-tab">--}}
{{--                                    <div class="info-box card z-depth-3">--}}
{{--                                        <div class="container">--}}
{{--                                            <div class="row">--}}
{{--                                                <h5 class="card-title">Заполняемость сетов</h5>--}}
{{--                                                @foreach($sets as $set)--}}
{{--                                                    @if($set->free != 0)--}}
{{--                                                        <label>Сет {{$set->number_set}}-{{$set->time}}--}}
{{--                                                            <span class="badge bg-success text-white">@lang('somewords.'.$set->day_of_week)</span>--}}
{{--                                                            @isset($set->date[$set->day_of_week])--}}
{{--                                                            <span class="badge bg-info text-dark">{{$set->date[$set->day_of_week]}}</span>--}}
{{--                                                            @endisset--}}
{{--                                                            (Свободно - {{100 - $set->procent}}%)</label>--}}
{{--                                                    @else--}}
{{--                                                        <label>Сет {{$set->number_set}}-{{$set->time}}--}}
{{--                                                            <span class="badge bg-success text-white">@lang('somewords.'.$set->day_of_week)</span>--}}
{{--                                                            @isset($set->date[$set->day_of_week])--}}
{{--                                                            <span class="badge bg-info text-dark">{{$set->date[$set->day_of_week]}}</span>--}}
{{--                                                            @endisset--}}
{{--                                                             (Полностью забит)</label>--}}
{{--                                                    @endif--}}
{{--                                                        <div class="container">--}}
{{--                                                            <div class="progress mt-1 pl-3">--}}
{{--                                                                <div class="progress-bar" role="progressbar"--}}
{{--                                                                     style="width: {{$set->procent}}%" aria-valuenow="{{$set->free}}"--}}
{{--                                                                     aria-valuemin="0" aria-valuemax="{{$set->max_participants}}"></div>--}}
{{--                                                            </div>--}}
{{--                                                        </div>--}}
{{--                                                @endforeach--}}
{{--                                            </div>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                </div>--}}
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
                                            @include('event.tab.payment')
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
