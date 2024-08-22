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
                            <h4 class="title"><a href="#about">Удобная регистрация участников через соц. сети</a></h4>
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
    <!-- ======= About Section ======= -->
    <section id="about" class="about section-bg">
        <div class="container" data-aos="fade-up">

            <div class="section-title">
                <h2>Регистрация и логин через соц сети</h2>
                <p>Регистрация и вход через социальные сети, такие как Yandex, Telegram и ВКонтакте, предлагают удобный и быстрый способ доступа к вашему аккаунту на нашем сервисе.</p>
            </div>

            <div class="row">
                <div class="col-lg-6" data-aos="fade-right" data-aos-delay="100">
                    <img src="{{asset('images/login.png')}}" class="img-fluid" alt="">
                </div>
                <div class="col-lg-6 pt-4 pt-lg-0 content d-flex flex-column justify-content-center" data-aos="fade-up" data-aos-delay="100">
                    <ul>
                        <li>
                            <i class="bx bx-time"></i>
                            <div>
                                <h5>Экономия времени</h5>
                                <p>Вам не нужно заполнять длинные формы регистрации, придумывать и запоминать новый пароль</p>
                            </div>
                        </li>
                        <li>
                            <i class="bx bx-accessibility"></i>
                            <div>
                                <h5>Упрощённый доступ</h5>
                                <p>Использование социальных сетей для входа облегчает процесс доступа к вашему аккаунту на разных устройствах</p>
                            </div>
                        </li>
                        <li>
                            <i class="bx bx-lock"></i>
                            <div>
                                <h5>Безопасность</h5>
                                <p>Вход через социальные сети осуществляется с использованием современных методов аутентификации, таких как двухфакторная авторизация</p>
                            </div>
                        </li>
                        <li>
                            <i class="bx bx-arrow-back"></i>
                            <div>
                                <h5>Лёгкая интеграция</h5>
                                <p>Если у вас уже есть профиль в одной из поддерживаемых соцсетей (Yandex, Telegram или ВКонтакте), вы можете сразу начать использовать наш сервис</p>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>

        </div>
    </section><!-- End About Section -->
    <section id="about" class="about section-bg">
        <div class="container" data-aos="fade-up">

            <div class="section-title">
                <h2>Удобное создание и управление соревнованиями</h2>
                <p>Создание и управление соревнованиями через админ-панель разработано с акцентом на удобство и эффективность.</p>
            </div>

            <div class="row">
                <div class="col-lg-6 pt-4 pt-lg-0 content d-flex flex-column justify-content-center" data-aos="fade-up" data-aos-delay="100">
                    <ul>
                        <li>
                            <i class="bx bx-lock-open"></i>
                            <div>
                                <h5>Гибкий подход</h5>
                                <p>Организаторы могут легко создавать новые соревнования, задавая ключевые параметры, такие как формат, оплата, подсчет, статус и многое другое</p>
                            </div>
                        </li>
                        <li>
                            <i class="bx bx-envelope"></i>
                            <div>
                                <h5>Доступность</h5>
                                <p>Благодаря интуитивно понятному интерфейсу, все этапы настройки доступны из одного окна, позволяя быстро вносить изменения и актуализировать информацию</p>
                            </div>
                        </li>
                        <li>
                            <i class="bx bx-info-square"></i>
                            <div>
                                <h5>Информативность</h5>
                                <p>Инструменты аналитики и отчетности, встроенные в админ-панель, позволяют отслеживать ключевые метрики, такие как количество зарегистрированных участников, статус оплат, и результаты</p>
                            </div>
                        </li>
                        <li>
                            <i class="bx bx-message"></i>
                            <div>
                                <h5>Уведомления</h5>
                                <p>Массовой отправка уведомлений и добавления предупреждений помогает эффективно коммуницировать с участниками, гарантируя, что все важные изменения или новости будут своевременно доведены до их сведения</p>
                            </div>
                        </li>
                    </ul>
                </div>

                <div class="col-lg-6" data-aos="fade-right" data-aos-delay="100">
                    <div class="card">
                        <div class="card-body">
                            <!-- Slides with fade transition -->
                            <div id="carouselExampleFade" class="carousel slide carousel-fade" data-bs-ride="carousel">
                                <div class="carousel-inner">
                                    <div class="carousel-item active">
                                        <img src="{{asset('images/management.png')}}" class="d-block w-100" alt="...">
                                    </div>
                                    <div class="carousel-item">
                                        <img src="{{asset('images/analytics.png')}}" class="d-block w-100" alt="...">
                                    </div>
                                    <div class="carousel-item">
                                        <img src="{{asset('images/main.png')}}" class="d-block w-100" alt="...">
                                    </div>
                                </div>

                                <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleFade" data-bs-slide="prev">
                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Previous</span>
                                </button>
                                <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleFade" data-bs-slide="next">
                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Next</span>
                                </button>

                            </div><!-- End Slides with fade transition -->

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section><!-- End About Section -->
    @if(count($events) > 0)
        <!-- ======= Portfolio Section ======= -->
        <section id="portfolio" class="portfolio">
            <div class="container" data-aos="fade-up">
                <div class="section-title">
                    <h2>Текущие соревнования</h2>
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
        @else
            <section id="portfolio" class="portfolio">
                <div class="container" data-aos="fade-up">
                    <div class="section-title">
                        <h2>Прошедшие соревнования можно найти тут <a href="{{route('list_events')}}" class="btn btn-primary"> перейти </a></h2>
                    </div>
                </div>
            </section>
        @endif
    @if($info_climbing_events)
        <section id="counts" class="counts">
        <div class="container" data-aos="fade-up">

            <div class="row">

                <div class="col-lg-3 col-md-6">
                    <div class="count-box">
                        <i class="bi bi-people"></i>
                        <span data-purecounter-start="0" data-purecounter-end="{{$info_climbing_events['amount_users']}}" data-purecounter-duration="1" class="purecounter"></span>
                        <p>Пользователей</p>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mt-5 mt-md-0">
                    <div class="count-box">
                        <i class="bi bi-calendar-event"></i>
                        <span data-purecounter-start="0" data-purecounter-end="{{$info_climbing_events['amount_events']}}" data-purecounter-duration="1" class="purecounter"></span>
                        <p>Соревнований</p>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mt-5 mt-lg-0">
                    <div class="count-box">
                        <i class="bi bi-gear"></i>
                        <span data-purecounter-start="0" data-purecounter-end="{{$info_climbing_events['amount_climbing_gym']}}" data-purecounter-duration="1" class="purecounter"></span>
                        <p>Скалодромов</p>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mt-5 mt-lg-0">
                    <div class="count-box">
                        <i class="bi bi-building"></i>
                        <span data-purecounter-start="0" data-purecounter-end="{{$info_climbing_events['amount_city']}}" data-purecounter-duration="1" class="purecounter"></span>
                        <p>Городов</p>
                    </div>
                </div>
            </div>

        </div>
    </section><!-- End Counts Section -->
    @endif
{{--        @include('event.carousel')--}}
@endsection('content')
