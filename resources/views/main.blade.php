@extends('layouts.main_page.app')
@section('content')
    <!-- Slider Start -->
    <main>
        <link href="https://cdn.jsdelivr.net/npm/suggestions-jquery@20.3.0/dist/css/suggestions.min.css"
              rel="stylesheet"/>
        <script src="https://cdn.jsdelivr.net/npm/suggestions-jquery@20.3.0/dist/js/jquery.suggestions.min.js"></script>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.1/dist/css/bootstrap.min.css">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.1.1/dist/js/bootstrap.bundle.min.js"></script>
        <section class="section error-404 min-vh-100 d-flex flex-column align-items-center justify-content-center">
            <h1>Climbing Events</h1>
            <h2>Регистрация и подсчет скалолазных соревнований</h2>

            <div class="row">
                <div class="col-lg-6">
                    @if(!empty($events))
                        @foreach($events as $event)
                            <!-- start event block -->
                            <div class="row align-items-center event-block no-gutters margin-40px-bottom">
                                <div class="col-lg-5 col-sm-12">
                                    <div class="position-relative">
                                        <img src="storage/{{$event->image}}" alt="">
                                        <div class="events-date">
                                            <div class="font-size28">10</div>
                                            <div class="font-size14">Mar</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-7 col-sm-12">
                                    <div class="padding-60px-lr md-padding-50px-lr sm-padding-30px-all xs-padding-25px-all">
                                        <h5 class="margin-15px-bottom md-margin-10px-bottom font-size22 md-font-size20 xs-font-size18 font-weight-500"><a href="event/{{$event->climbing_gym_name_eng}}/{{$event->title_eng}}" class="text-theme-color">{{$event->title}}</a></h5>
                                        <ul class="event-time margin-10px-bottom md-margin-5px-bottom">
                                            <li><i class="far fa-clock margin-10px-right"></i> 01:30 PM - 04:30 PM</li>
                                            <li><i class="fas fa-user margin-5px-right"></i>{{$event->climbing_gym_name}}</li>
                                        </ul>
                                        <a class="butn small margin-10px-top md-no-margin-top" href="event/{{$event->climbing_gym_name_eng}}/{{$event->title_eng}}">Подбробнее <i class="fas fa-long-arrow-alt-right margin-10px-left"></i></a>
                                    </div>
                                </div>
                            </div>
                            <!-- end event block -->
                        @endforeach
                    @endif


                </div>
            </div>
            <div class="container">
                @if(!empty($events))
                    @foreach($events as $event)
                        <!-- start event block -->
                        <div class="row align-items-center event-block no-gutters margin-40px-bottom">
                            <div class="col-lg-5 col-sm-12">
                                <div class="position-relative">
                                    <img src="storage/{{$event->image}}" alt="">
                                    <div class="events-date">
                                        <div class="font-size28">10</div>
                                        <div class="font-size14">Mar</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-7 col-sm-12">
                                <div class="padding-60px-lr md-padding-50px-lr sm-padding-30px-all xs-padding-25px-all">
                                    <h5 class="margin-15px-bottom md-margin-10px-bottom font-size22 md-font-size20 xs-font-size18 font-weight-500"><a href="event/{{$event->climbing_gym_name_eng}}/{{$event->title_eng}}" class="text-theme-color">{{$event->title}}</a></h5>
                                    <ul class="event-time margin-10px-bottom md-margin-5px-bottom">
                                        <li><i class="far fa-clock margin-10px-right"></i> 01:30 PM - 04:30 PM</li>
                                        <li><i class="fas fa-user margin-5px-right"></i>{{$event->climbing_gym_name}}</li>
                                    </ul>
                                    <a class="butn small margin-10px-top md-no-margin-top" href="event/{{$event->climbing_gym_name_eng}}/{{$event->title_eng}}">Подбробнее <i class="fas fa-long-arrow-alt-right margin-10px-left"></i></a>
                                </div>
                            </div>
                        </div>
                        <!-- end event block -->
                    @endforeach
                @endif
            </div>
        </section>
    </main><!-- End #main -->
        <style>
            .events-date {
                text-align: center;
                position: absolute;
                right: 30px;
                top: 30px;
                background-color: rgba(25, 47, 89, 0.9);
                color: #fff;
                padding: 12px 20px 8px 20px;
                text-transform: uppercase
            }

            .event-time li {
                display: inline-block;
                margin-right: 20px
            }

            .event-time li:last-child {
                margin-right: 0
            }

            .event-time li i {
                color: #59c17a
            }

            .event-block {
                box-shadow: 0px 0px 16px 0px rgba(187, 187, 187, 0.48)
            }

            .event-block ul li i {
                color: #59c17a
            }

            @media screen and (max-width: 1199px) {
                .event-date {
                    padding: 10px 18px 6px 18px
                }
            }

            @media screen and (max-width: 575px) {
                .event-date {
                    padding: 8px 15px 4px 15px
                }
                .events-date {
                    padding: 10px 15px 6px 15px
                }
            }

            .position-relative {
                position: relative !important;
            }

            .margin-40px-bottom {
                margin-bottom: 40px;
            }
            .padding-60px-lr {
                padding-left: 60px;
                padding-right: 60px;
            }

            .margin-15px-bottom {
                margin-bottom: 15px;
            }
            .font-weight-500 {
                font-weight: 500;
            }
            .font-size22 {
                font-size: 22px;
            }
            .text-theme-color {
                color: #192f59;
            }
            .margin-10px-bottom {
                margin-bottom: 10px;
            }
            .margin-10px-right {
                margin-right: 10px;
            }
        </style>
@endsection('content')
