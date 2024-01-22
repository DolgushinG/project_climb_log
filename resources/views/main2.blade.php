@extends('layouts.main_page.app')
@section('content')
    <main class="main">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.isotope/3.0.6/isotope.pkgd.min.js" integrity="sha512-Zq2BOxyhvnRFXu0+WE6ojpZLOU2jdnqbrM1hmVdGzyeCa1DgM3X5Q4A/Is9xA1IkbUeDd7755dNNI/PzSf2Pew==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <link href="https://cdn.jsdelivr.net/npm/suggestions-jquery@20.3.0/dist/css/suggestions.min.css"
              rel="stylesheet"/>
        <script src="https://cdn.jsdelivr.net/npm/suggestions-jquery@20.3.0/dist/js/jquery.suggestions.min.js"></script>
{{--        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.1/dist/css/bootstrap.min.css">--}}
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.1.1/dist/js/bootstrap.bundle.min.js"></script>
        <section class="section" style="padding-top: 5em">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="text-center">
                            <ul class="col container-filter portfolioFilte list-unstyled mb-0" id="filter">
                                <li><a class="categories active" data-filter="*">Все</a></li>
                                @foreach($active_cities as $active_city)
                                    <li><a class="categories" data-filter=".{{str_replace(' ', '_',$active_city)}}">{{$active_city}}</a></li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="port portfolio-masonry mt-4">
                <div class="portfolioContainer row">
                    @foreach($events as $index => $event)
                    <div class="col-lg-4 p-4 {{str_replace(' ', '_',$event->city)}}">
                        <div class="item-box">
                            <a class="mfp-image" href="event/{{$event->climbing_gym_name_eng}}/{{$event->title_eng}}" title="Project Name">
                                <img class="item-container img-fluid" src="storage/{{$event->image}}" alt="work-img">
                                <div class="item-mask">
                                    <div class="item-caption">
                                        <p class="text-white mb-0"></p>
                                        <h6 class="text-white mt-1 text-uppercase"></h6>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                    @endforeach
            </div>
            </div>
        </section>
    </main>
    <style>
        .container-filter {
            margin-top: 0;
            margin-right: 0;
            margin-left: 0;
            margin-bottom: 30px;
            padding: 0;
            text-align: center;
        }

        .container-filter li {
            list-style: none;
            display: inline-block;
        }

        .container-filter a {
            display: block;
            font-size: 14px;
            margin: 10px 20px;
            text-transform: uppercase;
            cursor: pointer;
            font-weight: 400;
            line-height: 30px;
            -webkit-transition: all 0.6s;
            border-bottom: 1px solid transparent;
            color: #807c7c !important;
        }

        .container-filter a:hover {
            color: #222222 !important;
        }

        .container-filter a.active {
            color: #222222 !important;
            border-bottom: 1px solid #222222;
        }

        .item-box {
            position: relative;
            overflow: hidden;
            display: block;
        }

        .item-box a {
            display: inline-block;
        }

        .item-box .item-mask {
            background: none repeat scroll 0 0 rgba(32, 151, 192, 0.3);
            position: absolute;
            transition: all 0.5s ease-in-out 0s;
            -moz-transition: all 0.5s ease-in-out 0s;
            -webkit-transition: all 0.5s ease-in-out 0s;
            -o-transition: all 0.5s ease-in-out 0s;
            top: 10px;
            left: 10px;
            bottom: 10px;
            right: 10px;
            opacity: 0;
            visibility: hidden;
            overflow: hidden;
            text-align: center;
        }

        .item-box .item-mask .item-caption {
            position: absolute;
            width: 100%;
            bottom: 10px;
            opacity: 0;
        }

        .item-box:hover .item-mask {
            opacity: 1;
            visibility: visible;
            cursor: pointer !important;
        }

        .item-box:hover .item-caption {
            opacity: 1;
        }

        .item-box:hover .item-container {
            width: 100%;
        }

        .services-box {
            padding: 45px 25px 45px 25px;
        }
    </style>
    <script>
        $(window).on('load', function() {
            var $container = $('.portfolioContainer');
            var $filter = $('#filter');
            $container.isotope({
                filter: '*',
                layoutMode: 'masonry',
                animationOptions: {
                    duration: 750,
                    easing: 'linear'
                }
            });
            $filter.find('a').click(function() {
                var selector = $(this).attr('data-filter');
                $filter.find('a').removeClass('active');
                $(this).addClass('active');
                $container.isotope({
                    filter: selector,
                    animationOptions: {
                        animationDuration: 750,
                        easing: 'linear',
                        queue: false,
                    }
                });
                return false;
            });
        });

    </script>
@endsection('content')
