@extends('layouts.main_page.app')
@section('content')
    <section>
        <div class="container">
            <div class="row">
            </div>
        </div>
    </section>
    <section>
        <div class="container">
            <div class="row">
                <h2 class="section-title">Список всех соревнований</h2>
                <div class="form-group pull-right">
                    <label class="m-1" for="search"> Поиск тут </label>
                    <input id="search" type="text" class="search form-control" placeholder="Что ищем?">
                </div>
                <span class="counter pull-right"></span>
                <table class="table table-hover table-bordered results">
                    <thead class="table-primary">
                    <tr>
                        <th class="col-md-2 col-xs-3">Афиша</th>
                        <th class="col-md-3 col-xs-3">Название</th>
                        <th class="col-md-2 col-xs-2">Скалодром</th>
                        <th class="col-md-3 col-xs-3">Город</th>
                        <th class="col-md-5 col-xs-5">Дата</th>
                    </tr>
                    <tr class="warning no-result">
                        <td colspan="6"><i class="fa fa-warning"></i> Нет результата</td>
                    </tr>
                    </thead>

                    <tbody>

                    @foreach($events as $index => $event)
                        <tr>
                            <td><a href="{{$event->link}}"><img width="100px" height="80px" class="img-thumbnail" src="storage/{{$event->image}}" alt="image"></a></td>
                            <td><a href="{{$event->link}}">{{$event->title}}</a> <br>Регистрация {{($event->is_registration_state ? 'открыта' : 'закрыта')}}<br>Участников {{$amount_participant[$event->id]}}</td>
                            <td>{{$event->climbing_gym_name}}</td>
                            <td>{{$event->city}}</td>
                            <td><b>{{date("d/m", strtotime($event->start_date))}}-{{date("d/m/y", strtotime($event->end_date))}}</b></td>
                        </tr>
                    @endforeach

                    </tbody>

                </table>
                <div class="row mx-auto mt-5">
                    <div class="col-lg-12">
                        {{$events->links("pagination::bootstrap-4")}}
                    </div>
                </div>
            </div>
        </div>
    </section>
    <script>
        $(document).ready(function() {
            $(".search").keyup(function () {
                var searchTerm = $(".search").val();
                var listItem = $('.results tbody').children('tr');
                var searchSplit = searchTerm.replace(/ /g, "'):containsi('")

                $.extend($.expr[':'], {'containsi': function(elem, i, match, array){
                        return (elem.textContent || elem.innerText || '').toLowerCase().indexOf((match[3] || "").toLowerCase()) >= 0;
                    }
                });

                $(".results tbody tr").not(":containsi('" + searchSplit + "')").each(function(e){
                    $(this).attr('visible','false');
                });

                $(".results tbody tr:containsi('" + searchSplit + "')").each(function(e){
                    $(this).attr('visible','true');
                });

                var jobCount = $('.results tbody tr[visible="true"]').length;
                $('.counter').text('Найдено сорев ' + jobCount );

                if(jobCount == '0') {$('.no-result').show();}
                else {$('.no-result').hide();}
            });
        });
    </script>
@endsection
