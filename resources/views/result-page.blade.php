@extends('layouts.main_page.app')

@section('content')
    <main id="main" class="main">
        <section class="list-route">
            <div class="row gy-4">
                <div class="col">
                    <div class="container">
                        <div class="row">
            @if(\App\Models\Participant::is_active_participant($event->id, Auth()->user()->id))
                <h1> Результат был внесен </h1>
            @else
            <h1> Внести результаты </h1>
            <div>

            </div>
            <div class="text-right">
                <button type="button" class="btn btn-dark" id="all-flash">Отметить все FLASH</button>
                <button type="button" class="btn btn-dark" id="all-redpoint">Отметить все REDPOINT</button>
            </div>
            <table class="table">
                <thead>
                <tr>
                    <th scope="col">Номер трассы</th>
                    <th id="grade" scope="col">Категория</th>
                    <th scope="col">Не пролез</th>
                    <th scope="col">Flash</th>
                    <th scope="col">Redpoint</th>
                </tr>
                </thead>
                <tbody>
                @foreach($routes as $route)
                    <tr>
                        <th>{{$route->count}}</th>
                        <th>{{$route->grade}}</th>
                        <td>
                            <input type="radio" class="btn-check" data-grade="{{$route->grade}}"
                                   name="{{$route->count}}" id="failed-{{$route->count}}" autocomplete="off"
                                   checked>
                            <label class="btn btn-outline-danger btn-failed" for="failed-{{$route->count}}">Не пролез</label>
                        </td>
                        <td>
                            <input type="radio" data-id="all-flash" data-grade="{{$route->grade}}" class="btn-check"
                                   name="{{$route->count}}" id="flash-{{$route->count}}" autocomplete="off">
                            <label class="btn btn-outline-success  btn-flash" for="flash-{{$route->count}}">FLASH</label>
                        </td>
                        <td>
                            <input type="radio" data-id="all-redpoint" class="btn-check"
                                   data-grade="{{$route->grade}}" name="{{$route->count}}"
                                   id="redpoint-{{$route->count}}" autocomplete="off">
                            <label class="btn btn-outline-danger btn-redpoint" for="redpoint-{{$route->count}}">REDPOINT</label>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>


            {{--                <div class="container text-center">--}}
            {{--                    <div class="row row-cols-5">--}}
            {{--                        @foreach($routes as $route)--}}
            {{--                        <div class="col"><label class="btn btn-primary">{{$route->count}}</label></div>--}}
            {{--                        <div class="col"><label class="btn btn-success">{{$route->grade}}</label></div>--}}
            {{--                            <div class="col" style="display: block">--}}
            {{--                                <input type="radio" class="btn-check" data-grade="{{$route->grade}}" name="{{$route->count}}" id="failed-{{$route->count}}" autocomplete="off" checked>--}}
            {{--                                <label class="btn btn-outline-danger" for="failed-{{$route->count}}">FAILED</label>--}}
            {{--                            </div>--}}
            {{--                        <div class="col">--}}
            {{--                            <input type="radio" data-id="all-flash" data-grade="{{$route->grade}}" class="btn-check" name="{{$route->count}}" id="flash-{{$route->count}}" autocomplete="off">--}}
            {{--                            <label class="btn btn-outline-success" for="flash-{{$route->count}}">FLASH</label>--}}
            {{--                        </div>--}}
            {{--                        <div class="col">--}}
            {{--                            <input type="radio" class="btn-check"  data-grade="{{$route->grade}}" name="{{$route->count}}" id="redpoint-{{$route->count}}" autocomplete="off">--}}
            {{--                            <label class="btn btn-outline-danger" for="redpoint-{{$route->count}}">REDPOINT</label>--}}
            {{--                        </div>--}}
            {{--                        <div class="col"> <i class="bi bi-emoji-heart-eyes-fill"></i> </div>--}}
            {{--                        @endforeach--}}
            {{--                    </div>--}}
            {{--                </div>--}}
            <div id="mobile-fixed" class="btn-container-desktop-fixed">
                <button type="button" id="btn-send-result" data-owner-id="{{$event->owner_id}}"
                        data-id="{{$event->id}}" data-user-id="{{Auth()->user()->id}}"
                        class="btn btn-success button-desktop-fixed rounded-pill">Внести
                </button>
            </div>
            <!-- End Table with stripped rows -->
            @endif
        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
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
        $(document).on('click', '#all-flash', function (e) {
            var check = document.querySelector("#all-flash"),
                radios = document.querySelectorAll("[data-id='all-flash']");

            if (check.textContent === "Отметить все FLASH") {
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

            if (check.textContent === "Отметить все REDPOINT") {
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
                        check.textContent = "Отметить все  REDPOINT"
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
            button.text('').append('<i id="spinner" style="margin-left: -12px;\n' +
                        '    margin-right: 8px;" class="fa fa-spinner fa-spin"></i> Обработка...')
            let event_id = document.getElementById('btn-send-result').getAttribute('data-id')
            let user_id = document.getElementById('btn-send-result').getAttribute('data-user-id')
            let owner_id = document.getElementById('btn-send-result').getAttribute('data-owner-id')
            e.preventDefault()
            $.ajax({
                type: 'POST',
                url: '/sendResultParticipant',
                data: {'result': results, 'event_id': event_id, 'user_id': user_id, 'owner_id': owner_id},
                success: function (xhr, status, error) {
                    // button.removeClass('btn-save-change')
                    // button.addClass('btn-edit-change')
                    button.text('').append('<i id="spinner" style="margin-left: -12px;\n' +
                        '    margin-right: 8px;" class="fa fa-spinner fa-spin"></i> Почти почти...')

                    setTimeout(function () {
                        button.text(xhr.message)
                    }, 3000);
                    setTimeout(function () {
                        button.text('Да результаты добавлены...')
                        button.removeClass('btn btn-dark rounded-pill')
                        button.addClass('btn border-t-neutral-500 rounded-pil')
                        button.attr("disabled", "true");
                        button.css('pointer-events', 'none');
                    }, 6000);

                    setTimeout(function () {
                        window.location.href = xhr.link;
                    }, 3000);
                },
                error: function (xhr, status, error) {
                    button.text('').append('<i id="spinner" style="margin-left: -12px;\n' +
                        '    margin-right: 8px;" class="fa fa-spinner fa-spin"></i> Обработка...')
                    setTimeout(function () {
                        button.removeClass('btn-save-change')
                        button.addClass('btn-failed-change')
                        button.text(xhr.message)
                    }, 3000);
                    setTimeout(function () {
                        button.removeClass('btn-failed-change')
                        button.addClass('btn-save-change')
                        button.text('Внести')
                    }, 6000);

                },

            });
        });

    </script>
@endsection
