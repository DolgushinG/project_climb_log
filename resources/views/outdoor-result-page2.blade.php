@extends('layouts.main_page.app')

@section('content')
    <section class="list-route">
        <div class="row mt-3 gy-4">
            <div class="col">
                <div class="container">
                    <div class="row">
                        @foreach($areas as $area)
{{--                            <div class="container">--}}
{{--                                <div class="row m-1">--}}
{{--                                    <div class="col-lg">--}}
{{--                                        @if(isset($area_images[$area]))--}}
{{--                                            <img class="img-fluid"--}}
{{--                                                 style="width: 500px;height: 300px;"--}}
{{--                                                 src="{{asset('storage'.$area_images[$area])}}"--}}
{{--                                                 alt="image">--}}
{{--                                        @else--}}
{{--                                            <img class="img-fluid"--}}
{{--                                                 style="width: 500px;height: 300px;"--}}
{{--                                                 src="" alt="image">--}}
{{--                                        @endif--}}

{{--                                    </div>--}}
{{--                                    <div class="col-sm">--}}
{{--                                        <p> Описание района </p>--}}
{{--                                        <p> Ссылка </p>--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                            </div>--}}
                            <div class="section-title"> <h2> Секторы </h2></div>

                            @foreach($sectors as $sector)
                                <div class="accordion"
                                     id="accordionExample{{str_replace(' ', '-', $sector)}}3">
                                    <div class="accordion-item">
                                        <h2 class="accordion-header"
                                            id="headingOne{{str_replace(' ', '-', $sector)}}3">
                                            <button class="accordion-button"
                                                    type="button"
                                                    data-bs-toggle="collapse"
                                                    data-bs-target="#collapseOne{{str_replace(' ', '-', $sector)}}3"
                                                    aria-expanded="true"
                                                    aria-controls="collapseOne{{str_replace(' ', '-', $sector)}}3">
                                                {{$sector}}
                                            </button>
                                        </h2>
                                        <div
                                            id="collapseOne{{str_replace(' ', '-', $sector)}}3"
                                            class="accordion-collapse collapse"
                                            aria-labelledby="headingOne{{str_replace(' ', '-', $sector)}}3"
                                            data-bs-parent="#accordionExample{{str_replace(' ', '-', $sector)}}3">
                                            <div class="accordion-body" style="padding-left: 5px;padding-right: 5px">
                                                <div class="row">
                                                        <div class="col-lg">
                                                            @if(isset($sector_fields[$sector]['image']))
                                                                <img
                                                                    class="img-fluid"
                                                                    style="width: 500px;height: 300px;"
                                                                    src="{{asset('storage/'.$sector_fields[$sector]['image'])}}"
                                                                    alt="image">
                                                            @else
                                                                <img
                                                                    class="img-fluid"
                                                                    style="width: 500px;height: 300px;"
                                                                    src=""
                                                                    alt="image">
                                                            @endif

                                                        </div>
                                                        <div class="col-sm">
                                                            <p> Описание
                                                                Сектора </p>
                                                            <p><a href="https://allclimb.com{{$sector_fields[$sector]['web_link']}}">Ссылка на allclimb.com</a></p>
                                                        </div>
                                                    </div>
                                                <div class="row mt-3">
                                                    <div class="col">
                                                        <div class="container">
                                                            <div class="row">
                                                                <div>
                                                                </div>
                                                                <table
                                                                    class="table">
                                                                    <thead>
                                                                    <tr>
                                                                        <th scope="col">
                                                                            Номер
                                                                        </th>
                                                                        <th scope="col">
                                                                            Трасса
                                                                        </th>
                                                                        @if(!$event->is_hide_grades)
                                                                            <th id="grade"
                                                                                style="font-size: 11px"
                                                                                scope="col">
                                                                                Категория
                                                                            </th>
                                                                        @endif
                                                                        @if($event->is_zone_show)
                                                                            <th scope="col">
                                                                                Нет
                                                                            </th>
                                                                        @else
                                                                            <th scope="col">
                                                                                Не
                                                                                пролез
                                                                            </th>
                                                                        @endif
                                                                        <th scope="col">
                                                                            Флэш
                                                                        </th>
                                                                        @if($event->is_zone_show)
                                                                            <th scope="col">
                                                                                Зона
                                                                            </th>
                                                                        @endif
                                                                        <th scope="col">
                                                                            Редпоинт
                                                                        </th>
                                                                    </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                    @foreach($routes as $index => $route)
                                                                        @if($route->sector == $sector)
                                                                            <tr>
                                                                                @if($event->type_event)
                                                                                    <th>{{$route->count}}</th>
                                                                                    <th><a href="{{asset('storage/'.$route->image)}}" data-gallery="portfolioGallery"
                                                                                           class="portfolio-lightbox preview-link">{{$route->route_name}}</a>
                                                                                        </th>
                                                                                @else
                                                                                    <th>{{$route->count}}</th>
                                                                                @endif
                                                                                @if(!$event->is_hide_grades)
                                                                                    <th>{{$route->grade}}</th>
                                                                                @endif
                                                                                <td>
                                                                                    @if($result_participant)
                                                                                        @if($result_participant[$index]['attempt'] == '0')
                                                                                            <input
                                                                                                type="radio"
                                                                                                class="btn-check"
                                                                                                data-grade="{{$route->grade}}"
                                                                                                name="{{$route->count}}"
                                                                                                id="failed-{{$route->count}}"
                                                                                                autocomplete="off"
                                                                                                checked>
                                                                                        @else
                                                                                            <input
                                                                                                type="radio"
                                                                                                class="btn-check"
                                                                                                data-grade="{{$route->grade}}"
                                                                                                name="{{$route->count}}"
                                                                                                id="failed-{{$route->count}}"
                                                                                                autocomplete="off">
                                                                                        @endif

                                                                                    @else
                                                                                        <input
                                                                                            type="radio"
                                                                                            class="btn-check"
                                                                                            data-grade="{{$route->grade}}"
                                                                                            name="{{$route->count}}"
                                                                                            id="failed-{{$route->count}}"
                                                                                            autocomplete="off" checked>
                                                                                    @endif
                                                                                    @if($event->is_zone_show)
                                                                                        <label
                                                                                            class="btn btn-outline-danger btn-failed"
                                                                                            for="failed-{{$route->count}}">Нет</label>
                                                                                    @else
                                                                                        <label
                                                                                            class="btn btn-outline-danger btn-failed"
                                                                                            for="failed-{{$route->count}}">Не
                                                                                            пролез</label>
                                                                                    @endif
                                                                                </td>
                                                                                <td>
                                                                                    @if($result_participant)
                                                                                        @if($result_participant[$index]['attempt'] == '1')
                                                                                            <input
                                                                                                type="radio"
                                                                                                data-id="all-flash"
                                                                                                data-grade="{{$route->grade}}"
                                                                                                class="btn-check"
                                                                                                name="{{$route->count}}"
                                                                                                id="flash-{{$route->count}}"
                                                                                                autocomplete="off"
                                                                                                checked>
                                                                                        @else
                                                                                            <input
                                                                                                type="radio"
                                                                                                data-id="all-flash"
                                                                                                data-grade="{{$route->grade}}"
                                                                                                class="btn-check"
                                                                                                name="{{$route->count}}"
                                                                                                id="flash-{{$route->count}}"
                                                                                                autocomplete="off">
                                                                                        @endif

                                                                                    @else
                                                                                        <input
                                                                                            type="radio"
                                                                                            data-id="all-flash"
                                                                                            data-grade="{{$route->grade}}"
                                                                                            class="btn-check"
                                                                                            name="{{$route->count}}"
                                                                                            id="flash-{{$route->count}}"
                                                                                            autocomplete="off">
                                                                                    @endif

                                                                                    <label
                                                                                        class="btn btn-outline-success  btn-flash"
                                                                                        for="flash-{{$route->count}}">FLASH</label>
                                                                                </td>
                                                                                @if($event->is_zone_show)
                                                                                    <td>
                                                                                        @if($result_participant)
                                                                                            @if($result_participant[$index]['attempt'] == '3')
                                                                                                <input
                                                                                                    type="radio"
                                                                                                    class="btn-check"
                                                                                                    data-grade="{{$route->grade}}"
                                                                                                    name="{{$route->count}}"
                                                                                                    id="zone-{{$route->count}}"
                                                                                                    autocomplete="off"
                                                                                                    checked>
                                                                                            @else
                                                                                                <input
                                                                                                    type="radio"
                                                                                                    class="btn-check"
                                                                                                    data-grade="{{$route->grade}}"
                                                                                                    name="{{$route->count}}"
                                                                                                    id="zone-{{$route->count}}"
                                                                                                    autocomplete="off">
                                                                                            @endif

                                                                                        @else
                                                                                            <input
                                                                                                type="radio"
                                                                                                class="btn-check"
                                                                                                data-grade="{{$route->grade}}"
                                                                                                name="{{$route->count}}"
                                                                                                id="zone-{{$route->count}}"
                                                                                                autocomplete="off">
                                                                                        @endif
                                                                                        <label
                                                                                            class="btn btn-outline-secondary btn-failed"
                                                                                            for="zone-{{$route->count}}">Зона</label>
                                                                                    </td>
                                                                                @endif

                                                                                <td>
                                                                                    @if($result_participant)
                                                                                        @if($result_participant[$index]['attempt'] == '2')
                                                                                            <input
                                                                                                type="radio"
                                                                                                data-id="all-redpoint"
                                                                                                class="btn-check"
                                                                                                data-grade="{{$route->grade}}"
                                                                                                name="{{$route->count}}"
                                                                                                id="redpoint-{{$route->count}}"
                                                                                                autocomplete="off"
                                                                                                checked>
                                                                                        @else
                                                                                            <input
                                                                                                type="radio"
                                                                                                data-id="all-redpoint"
                                                                                                class="btn-check"
                                                                                                data-grade="{{$route->grade}}"
                                                                                                name="{{$route->count}}"
                                                                                                id="redpoint-{{$route->count}}"
                                                                                                autocomplete="off">
                                                                                        @endif
                                                                                    @else
                                                                                        <input
                                                                                            type="radio"
                                                                                            data-id="all-redpoint"
                                                                                            class="btn-check"
                                                                                            data-grade="{{$route->grade}}"
                                                                                            name="{{$route->count}}"
                                                                                            id="redpoint-{{$route->count}}"
                                                                                            autocomplete="off">
                                                                                    @endif

                                                                                    <label
                                                                                        class="btn btn-outline-warning btn-redpoint"
                                                                                        for="redpoint-{{$route->count}}">REDPOINT</label>
                                                                                </td>

                                                                            </tr>
                                                                        @endif
                                                                    @endforeach
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                            <div
                                id="mobile-fixed"
                                class="btn-container-desktop-fixed">
                                <button
                                    type="button"
                                    id="btn-send-result"
                                    data-owner-id="{{$event->owner_id}}"
                                    data-id="{{$event->id}}"
                                    data-user-id="{{Auth()->user()->id}}"
                                    class="btn btn-success button-desktop-fixed rounded-pill">
                                    Внести
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>
    <script>
        if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
            $(document).ready(function () {
                {
                    var mobile = $('#mobile-fixed')
                    mobile.removeClass('btn-container-desktop-fixed');
                    mobile.addClass('btn-container-mobile-fixed');
                    var send = $('#btn-send-result')
                    send.removeClass('button-desktop-fixed');
                    send.addClass('button-mobile-fixed');
                    var col = $('#grade')
                    col.text('').append('Кате-<br>гория')
                }
            });
        }
    </script>
    <script>
        function reset_flash() {
            let check = document.querySelector("#all-flash"),
                radios = document.querySelectorAll("[data-id='all-flash']");
            for (i = 0; i < radios.length; i++) {
                //And the elements are radios
                if (radios[i].checked === true) {
                    radios[i].checked = false;
                    check.textContent = "Отметить все FLASH"
                }
            }//if
        }

        function reset_redpoint() {
            let check = document.querySelector("#all-redpoint"),
                radios = document.querySelectorAll("[data-id='all-redpoint']");
            for (i = 0; i < radios.length; i++) {
                //And the elements are radios
                if (radios[i].checked === true) {
                    radios[i].checked = false;
                    check.textContent = "Отметить все REDPOINT"
                }
            }//if
        }

        $(document).on('click', '#all-flash', function (e) {
            var check = document.querySelector("#all-flash"),
                radios = document.querySelectorAll("[data-id='all-flash']");

            if (check.textContent.trim() === "Отметить все FLASH") {
                reset_redpoint()
                for (i = 0; i < radios.length; i++) {
                    if (radios[i].checked === false) {
                        radios[i].checked = true;
                        check.textContent = "Сбросить все FLASH"
                    }
                }//for
                //If the second radio is checked
            } else {

                for (i = 0; i < radios.length; i++) {
                    //And the elements are radios
                    if (radios[i].checked === true) {
                        radios[i].checked = false;
                        check.textContent = "Отметить все FLASH"
                    }
                }//if
            }//for
            return null
        });
        $(document).on('click', '#all-redpoint', function (e) {
            var check = document.querySelector("#all-redpoint"),
                radios = document.querySelectorAll("[data-id='all-redpoint']");

            if (check.textContent.trim() === "Отметить все REDPOINT") {
                reset_flash()
                for (i = 0; i < radios.length; i++) {
                    if (radios[i].checked === false) {
                        radios[i].checked = true;
                        check.textContent = "Сбросить все REDPOINT"
                    }
                }//for
                //If the second radio is checked
            } else {
                for (i = 0; i < radios.length; i++) {
                    //And the elements are radios
                    if (radios[i].checked === true) {
                        radios[i].checked = false;
                        check.textContent = "Отметить все REDPOINT"
                    }
                }//if
            }//for
            return null
        });
        $(document).on('click', '#btn-send-result', function (e) {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            var results = [...document.querySelectorAll('.btn-check')].map(input => [input.id, input.checked, input.getAttribute('data-grade')])
            let button = $('#btn-send-result')
            button.attr("disabled", "true")
            button.text('').append('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Обработка...')
            let event_id = document.getElementById('btn-send-result').getAttribute('data-id')
            let user_id = document.getElementById('btn-send-result').getAttribute('data-user-id')
            let owner_id = document.getElementById('btn-send-result').getAttribute('data-owner-id')
            e.preventDefault()
            $.ajax({
                type: 'POST',
                url: '/sendResultParticipant',
                data: {'result': results, 'event_id': event_id, 'user_id': user_id, 'owner_id': owner_id},
                success: function (xhr, status, error) {
                    button.text('').append('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Почти почти...')
                    setTimeout(function () {
                        button.text(xhr.message)
                    }, 1000);
                    setTimeout(function () {
                        window.location.href = xhr.link;
                    }, 3000);
                },
                error: function (xhr, status, error) {
                    setTimeout(function () {
                        button.removeClass('btn-save-change')
                        button.addClass('btn-failed-result-page-change')
                        $('.spinner-border.spinner-border-sm').remove()
                        button.text(xhr.responseJSON.message)
                    }, 1000);
                    setTimeout(function () {
                        button.removeClass('btn-failed-result-page-change')
                        let button_css = document.getElementById('btn-send-result')
                        button_css.removeAttribute("disabled");
                        button.text('Внести')
                    }, 3000);

                },

            });
        });

    </script>
@endsection
