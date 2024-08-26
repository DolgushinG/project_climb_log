@extends('layouts.main_page.app')
@section('content')
    <section class="section contact d-flex align-items-center">
        <div class="row w-100 m-1 mt-5">
            @if($message_for_participants)
                @if($message_for_participants->is_show)
                    <div class="container d-flex align-items-center justify-content-center" data-aos="zoom-out"
                         data-aos-delay="100">
                        <div id="message_for_participant"
                             class="alert alert-{{$message_for_participants->type}} alert-dismissible fade show"
                             role="alert">
                            <p>{!!$message_for_participants->text!!}</p>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                @endif
            @endif
            <div class="col-md-1"></div>
            <div class="col-md-4">
                <div class="card p-4 z-depth-3">
                    <div class="container">
                        <h4 class="text-center">{{$event->title}}</h4>
                        <img class="img img-responsive" src="{{asset('storage/'.$event->image)}}" alt="">
                        <div class="d-grid gap-2 mt-3">
                            @guest
                                @if(\App\Models\Event::event_is_open($event))
                                    @if($event->is_registration_state)
                                        <a href="{{route('login')}}" class="btn btn-dark rounded-pill">Войти для
                                            участия</a>
                                    @else
                                        @include('event.buttons.reg-close')
                                    @endif
                                @else
                                    @include('event.buttons.event-close')
                                @endif
                            @endguest
                            @auth
                                @if(\App\Helpers\Helpers::valid_email(\Illuminate\Support\Facades\Auth::user()->email))
                                    @if(\App\Models\Event::event_is_open($event))
                                        @if(\App\Models\User::user_participant($event->id))
                                            {{--                                            Открыто/Закрыто изменение участия в сетах--}}
                                            @if(!$event->is_input_set && $event->is_registration_state && !\App\Models\ResultRouteQualificationClassic::participant_with_result(Auth()->user()->id, $event->id))
                                                @include('event.selects.sets_participant')
                                            @endif
                                            @if(\App\Models\ResultRouteQualificationClassic::participant_with_result(Auth()->user()->id, $event->id))
                                                @if($event->is_france_system_qualification)
                                                    @include('event.buttons.participant_already')
                                                @else
                                                    @if($event->is_access_user_edit_result)
                                                        @include('event.buttons.send_result')
                                                        @include('event.buttons.participant_already')
                                                        @include('event.buttons.results_have_been_sent_already')
                                                    @else
                                                        @include('event.buttons.show_sent_result')
                                                        @include('event.buttons.participant_already')
                                                        @include('event.buttons.results_have_been_sent_already')
                                                    @endif
                                                @endif
                                            @else
                                                {{--Нужна оплата?--}}
                                                @if($event->is_need_pay_for_reg)
                                                    @if(\App\Models\ResultRouteQualificationClassic::is_pay_participant(Auth()->user()->id, $event->id))
                                                        @if($event->is_france_system_qualification)
                                                            @include('event.buttons.participant_already')
                                                        @else
                                                            @if($event->is_send_result_state)
                                                                @include('event.buttons.send_result')
                                                            @endif
                                                        @endif
                                                    @else
                                                        @if($event->is_registration_state)
                                                            @include('event.buttons.cancel_take_part')
                                                            @include('event.buttons.pay')
                                                        @endif
                                                    @endif
                                                @else
                                                    {{-- Француская система в ней результаты вносят судьи--}}
                                                    @if($event->is_france_system_qualification)
                                                        @include('event.buttons.cancel_take_part')
                                                        @include('event.buttons.participant_already')
                                                    @else
                                                        @if($event->is_send_result_state)
                                                            {{-- Фестивальная система вносят результаты сами участники--}}
                                                            @include('event.buttons.send_result')
                                                        @else
                                                            @include('event.buttons.send_result_is_close')
                                                        @endif
                                                        {{--                                                 Регистрация открыта/закрыта--}}
                                                        @if($event->is_registration_state)
                                                            @include('event.buttons.cancel_take_part')
                                                        @endif
                                                    @endif
                                                @endif
                                            @endif
                                        @else
                                            @if($event->is_registration_state)
                                                @include('event.selects.birthday')
                                                @include('event.selects.genders')
                                                @include('event.selects.sport_categories')
                                                @include('event.selects.categories')
                                                @if(!$event->is_input_set)
                                                    @include('event.selects.sets_take_part')
                                                @endif
                                                @include('event.buttons.take_part')
                                            @else
                                                @include('event.buttons.reg-close')
                                            @endif
                                            <div style="display:none;" id="error-message"
                                                 class="alert alert-danger alert-dismissible fade show" role="alert">
                                                <i class="bi bi-exclamation-triangle me-1"></i>
                                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                                        aria-label="Close"></button>
                                            </div>
                                            <div style="display:none;" id="warning-message"
                                                 class="alert alert-warning alert-dismissible fade show" role="alert">
                                                <i class="bi bi-exclamation-triangle me-1"></i>
                                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                                        aria-label="Close"></button>
                                            </div>
                                        @endif
                                        <div class="modal fade" id="scrollingModal" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                                aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body" id="payment">
                                                        @include('event.tab.payment_without_bill')
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        @include('event.buttons.event-close')
                                        @if(\App\Models\User::user_participant($event->id) && \App\Models\ResultRouteQualificationClassic::participant_with_result(Auth()->user()->id, $event->id))
                                            @include('event.buttons.show_sent_result')
                                        @endif
                                    @endif
                                @else
                                    <a href="{{route('profile')}}" class="btn btn-secondary rounded-pill">Заполните
                                        ваш Email в профиле</a>
                                @endif

                            @endif
                            <a href="{{route('participants', [$event->start_date, $event->climbing_gym_name_eng, $event->title_eng])}}"
                               class="btn btn-primary rounded-pill">Список участников</a>

                            @if(!$event->is_france_system_qualification)
                                <a href="{{route('get_qualification_classic_results',[$event->start_date, $event->climbing_gym_name_eng, $event->title_eng])}}"
                                   class="btn btn-primary rounded-pill">Предварительные результаты</a>
                                @if($event->is_open_main_rating)
                                    <a href="{{route('get_qualification_classic_global_results',[$event->start_date, $event->climbing_gym_name_eng, $event->title_eng])}}"
                                       class="btn btn-dark rounded-pill">Совмещенные результаты</a>
                                @endif
                            @else
                                <a href="{{route('get_qualification_france_system_results',[$event->start_date, $event->climbing_gym_name_eng, $event->title_eng])}}"
                                   class="btn btn-primary rounded-pill">Предварительные результаты</a>
                            @endif
                            @if($event->is_semifinal && $is_show_button_semifinal)
                                <a href="{{route('get_semifinal_france_system_results',[$event->start_date, $event->climbing_gym_name_eng, $event->title_eng])}}"
                                   class="btn btn-success rounded-pill">Результаты полуфинала</a>
                            @endif
                            @if($is_show_button_final)
                                <a href="{{route('get_final_france_system_results',[$event->start_date, $event->climbing_gym_name_eng, $event->title_eng])}}"
                                   class="btn btn-success rounded-pill">Результаты финала</a>
                            @endif
                            @if(!$event->is_send_result_state && $event->is_open_send_result_state)
                                @include('event.get_all_result_to_email')
                            @endif
                            @if(!$event->is_send_result_state && $event->is_open_public_analytics)
                                <a href="{{route('index_analytics', [$event->start_date, $event->climbing_gym_name_eng, $event->title_eng])}}"
                                   class="btn btn-warning rounded-pill">Статистика</a>
                            @endif
                        </div>
                    </div>
                    @if($count_participants)
                        <div class="welcome-counts" data-aos="fade-up">
                            <div class="row">
                                <div class="col">
                                    <div class="count-box">
                                        <i class="bi bi-people-fill"></i>
                                        <span data-purecounter-start="0" data-purecounter-end="{{$count_participants}}"
                                              data-purecounter-duration="1" class="purecounter"></span>
                                        <p>Количество участников</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            <div class="col-md-6">


                <div class="card z-depth-3">
                    <div class="card-body">
                        <!-- Bordered Tabs Justified -->
                        <ul class="nav nav-pills nav-tabs-bordered d-flex" id="borderedTabJustified" role="tablist">
                            <li class="nav-item flex-fill" role="presentation" style="margin-right: 8px!important;">
                                <button class="nav-link w-100 active" id="info-tab" data-bs-toggle="tab"
                                        data-bs-target="#bordered-justified-info" type="button" role="tab"
                                        aria-controls="info" aria-selected="true">Общая информация
                                </button>
                            </li>
                            <li class="nav-item flex-fill" role="presentation" style="margin-right: 8px!important;">
                                <button class="nav-link w-100" id="profile-tab" data-bs-toggle="tab"
                                        data-bs-target="#bordered-justified-profile" type="button" role="tab"
                                        aria-controls="profile" aria-selected="false">Положение
                                </button>
                            </li>
                            <li class="nav-item flex-fill" role="presentation"
                                style="margin-right: 8px!important;">
                                <button class="nav-link w-100" id="contact-tab" data-bs-toggle="tab"
                                        data-bs-target="#bordered-justified-contact" type="button" role="tab"
                                        aria-controls="contact" aria-selected="false">Стартовый взнос
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content pt-2" id="borderedTabJustifiedContent">
                            <div class="tab-pane fade show active" id="bordered-justified-info" role="tabpanel"
                                 aria-labelledby="info-tab">
                                <section id="services" class="services">
                                    <div class="container" data-aos="fade-up">
                                        @if($event->contact_link)
                                            <div class="section-title">
                                                <h2>Скалодром</h2>
                                                <p>
                                                    <img src="{{asset('storage/'.$event->climbing_gym_name_image)}}"
                                                         alt="climbing_gym_name_image" class="profile-user-img-header">
                                                    <span
                                                        class="d-md-block ps-2 align-items-center ">{{$event->climbing_gym_name}}</span>
                                                </p>
                                            </div>
                                        @endif
                                        <div class="row">
                                            <div class="col-lg-4">
                                                <div class="section-title">
                                                    <h2>Адрес</h2>
                                                    <p>{{$event->city}}, {{$event->address}}</p>
                                                </div>
                                            </div>
                                            <div class="col-lg-4">
                                                <div class="section-title">
                                                    <h2>Контакты</h2>
                                                    <p>{{$event->contact}}</p>
                                                </div>
                                            </div>
                                            <div class="col-lg-4">
                                                <div class="section-title">
                                                    @if($event->contact_link)
                                                        <div class="section-title">
                                                            <h2>Соц-сеть</h2>
                                                            @if(str_contains($event->contact_link, 'vk'))
                                                                <p><a href="{{$event->contact_link}}"
                                                                      class="btn btn-primary" type="button">
                                                                        <i class="fa fa-vk" aria-hidden="true"></i>
                                                                    </a>
                                                                </p>
                                                            @elseif(str_contains($event->contact_link, 'telegram'))
                                                                <p><a href="{{$event->contact_link}}"
                                                                      class="btn btn-primary" type="button">
                                                                        <i class="fa fa-telegram"
                                                                           aria-hidden="true"></i>
                                                                    </a>
                                                                </p>
                                                            @else
                                                                <p><a href="{{$event->contact_link}}">Ссылка на
                                                                        соц-сеть</a></p>
                                                            @endif
                                                        </div>
                                                    @else
                                                        <div class="section-title">
                                                            <h2>Организатор</h2>
                                                            <p>
                                                                <img
                                                                    src="{{asset('storage/'.$event->climbing_gym_name_image)}}"
                                                                    alt="climbing_gym_name_image"
                                                                    class="climbing-gym-img-title">
                                                                <span
                                                                    class="d-md-block ps-2 align-items-center ">{{$event->climbing_gym_name}}</span>
                                                            </p>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @if(env('GOOGLE_MAPS_ADDRESS') && env('APP_ENV') == "prod")
                                        <div class="row">
                                            <div class="col">
                                                <iframe src="{{$google_iframe}}" frameborder="0"
                                                        style="border:0; width: 100%; height: 270px;" allowfullscreen=""
                                                        loading="lazy"
                                                        referrerpolicy="no-referrer-when-downgrade"></iframe>
                                            </div>
                                        </div>
                                    @endif
                                </section>
                            </div>
                            <div class="tab-pane fade" id="bordered-justified-profile" role="tabpanel"
                                 aria-labelledby="profile-tab">
                                <p>{!! $event->description !!}</p>
                            </div>
                            <div class="tab-pane fade" id="bordered-justified-contact" role="tabpanel"
                                 aria-labelledby="contact-tab">
                                <div class="container">
                                    <div class="row" id="paymentTab">
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
    <script src="{{asset('js/welcome.js')}}"></script>
@endsection
