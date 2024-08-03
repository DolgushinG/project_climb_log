@extends('layouts.main_page.app')
@section('content')
    <section id="hero" class="d-flex align-items-center">
        <div class="container" data-aos="zoom-out" data-aos-delay="100">
            <h1>Добро пожаловать в сервис <br>
                <span>Climbing Events</span></h1>
            <h2>Регистрация и подсчет результатов соревнований</h2>
            <div class="d-flex">
                <a href="#featured-services" class="btn-get-started scrollto">Подробнее</a>
{{--                <a href="https://www.youtube.com/watch?v=jDDaplaOz7Q" class="glightbox btn-watch-video"><i--}}
{{--                        class="bi bi-play-circle"></i><span>Watch Video</span></a>--}}
            </div>
        </div>
    </section><!-- End Hero -->
        <!-- ======= Featured Services Section ======= -->
    <section id="featured-services" class="featured-services">
            <div class="container" data-aos="fade-up">

                <div class="row">
                    <div class="col-md-6 col-lg-3 d-flex align-items-stretch mb-5 mb-lg-0">
                        <div class="icon-box" data-aos="fade-up" data-aos-delay="100">
                            <div class="icon"><i class="bx bxl-dribbble"></i></div>
                            <h4 class="title"><a href="">Удобная регистрация участников через соц. сети</a></h4>
                            <p class="description">
                                <img src="{{asset('storage/img/icons/telegram.svg')}}" alt="telegram">
                                <img src="{{asset('storage/img/icons/vk.svg')}}" alt="vk">
                                <img src="{{asset('storage/img/icons/yandex.svg')}}" alt="yandex">
                            </p>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-3 d-flex align-items-stretch mb-5 mb-lg-0">
                        <div class="icon-box" data-aos="fade-up" data-aos-delay="200">
                            <div class="icon"><i class="bx bx-file"></i></div>
                            <h4 class="title"><a href="">Удобное создание и управление соревнованиями</a></h4>
                            <p class="description">Быстрый подсчет, просмотр и экспорт</p>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-3 d-flex align-items-stretch mb-5 mb-lg-0">
                        <div class="icon-box" data-aos="fade-up" data-aos-delay="300">
                            <div class="icon"><i class="bx bx-tachometer"></i></div>
                            <h4 class="title"><a href="">Возможность просмотра результата соревнований</a></h4>
                            <p class="description">Прошедних,предстоящих и текущих по всей России</p>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-3 d-flex align-items-stretch mb-5 mb-lg-0">
                        <div class="icon-box" data-aos="fade-up" data-aos-delay="400">
                            <div class="icon"><i class="bx bx-world"></i></div>
                            <h4 class="title"><a href="">Рейтинг по всей России</a></h4>
                            <p class="description">Статистика по соревнованиям в личном кабинете каждого участника</p>
                        </div>
                    </div>

                </div>

            </div>
        </section><!-- End Featured Services Section -->
        <!-- ======= Portfolio Section ======= -->
    <section id="portfolio" class="portfolio">
        <div class="container" data-aos="fade-up">
            <div class="section-title">
                <h2>Недавние соревнования</h2>
            </div>
            <div class="row" data-aos="fade-up" data-aos-delay="100">
                <div class="col-lg-12 d-flex justify-content-center">
                    <ul id="portfolio-flters">
                        <li data-filter="*" class="filter-active">Все города</li>
                        @foreach($cities as $city)
                            <li data-filter=".filter-{{$city['name']}}">{{$city['name']}}<span style="margin-left: 7px; font-size: 10px;" class="badge rounded-pill bg-primary ml-5">{{$city['count_event']}}</span></li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <div class="row portfolio-container" data-aos="fade-up" data-aos-delay="200">
                @foreach($events as $event)
                    <div class="col-lg-4 col-md-6 portfolio-item filter-{{$event->city}}">
                        <a href="{{$event->link}}" class="details-link" title="More Details">
                            <img src="storage/{{$event->image}}" class="img-fluid" alt="">
                        </a>
                        <div class="portfolio-info">
                            <h4>{{$event->city}} {{date("d/m/Y", strtotime($event->start_date))}}</h4>
                            <a href="storage/{{$event->image}}" data-gallery="portfolioGallery"
                               class="portfolio-lightbox preview-link"><i class="bx bx-plus"></i></a>
                            <a href="{{$event->link}}" class="details-link" title="More Details"><i
                                    class="bx bx-link"></i></a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section><!-- End Portfolio Section -->
    <section id="portfolio" class="portfolio">
            <div class="container" data-aos="fade-up">
                <div class="section-title">
                    <h2>Открыта регистрация прямо сейчас</h2>
                </div>
                <div class="row" data-aos="fade-up" data-aos-delay="100">
                    <div class="col-lg-12 d-flex justify-content-center">
                        <ul id="portfolio-flters">
                            <li data-filter="*" class="filter-active">Все города</li>
                            @foreach($cities as $city)
                                <li data-filter=".filter-{{$city['name']}}">{{$city['name']}}<span style="margin-left: 7px; font-size: 10px;" class="badge rounded-pill bg-primary ml-5">{{$city['count_event']}}</span></li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <div class="row portfolio-container" data-aos="fade-up" data-aos-delay="200">
                @foreach($events as $event)
                        <div class="col-lg-4 col-md-6 portfolio-item filter-{{$event->city}}">
                            <a href="{{$event->link}}" class="details-link" title="More Details">
                                <img src="storage/{{$event->image}}" class="img-fluid" alt="">
                            </a>
                            <div class="portfolio-info">
                                <h4>{{$event->city}} {{date("d/m/Y", strtotime($event->start_date))}}</h4>
                                <a href="storage/{{$event->image}}" data-gallery="portfolioGallery"
                                   class="portfolio-lightbox preview-link"><i class="bx bx-plus"></i></a>
                                <a href="{{$event->link}}" class="details-link" title="More Details"><i
                                        class="bx bx-link"></i></a>
                            </div>
                        </div>
                @endforeach
                </div>
            </div>
        </section><!-- End Portfolio Section -->
    <section id="portfolio" class="portfolio">
        <div class="container" data-aos="fade-up">
            <div class="section-title">
                <h2>Прошедшие соревнования</h2>
            </div>
            <div class="row" data-aos="fade-up" data-aos-delay="100">
                <div class="col-lg-12 d-flex justify-content-center">
                    <ul id="portfolio-flters">
                        <li data-filter="*" class="filter-active">Все города</li>
                        @foreach($cities as $city)
                            <li data-filter=".filter-last-{{$city['name']}}">{{$city['name']}}<span style="margin-left: 7px; font-size: 10px;" class="badge rounded-pill bg-primary ml-5">{{$city['count_event']}}</span></li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <div class="row portfolio-container" data-aos="fade-up" data-aos-delay="200">
                @foreach($last_events as $event)
                    <div class="col-lg-4 col-md-6 portfolio-item filter-last-{{$event->city}}">
                        <a href="{{$event->link}}" class="details-link" title="More Details">
                            <img src="storage/{{$event->image}}" class="img-fluid" alt="">
                        </a>
                        <div class="portfolio-info">
                            <h4>{{$event->city}} {{date("d/m/Y", strtotime($event->start_date))}}</h4>
                            <a href="storage/{{$event->image}}" data-gallery="portfolioGallery"
                               class="portfolio-lightbox preview-link"><i class="bx bx-plus"></i></a>
                            <a href="{{$event->link}}" class="details-link" title="More Details"><i
                                    class="bx bx-link"></i></a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section><!-- End Portfolio Section -->
@endsection('content')
