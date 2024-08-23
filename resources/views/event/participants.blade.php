@extends('layouts.main_page.app')
@section('content')
    <section class="section contact">
            <div class="row m-3">
                @if(count($participants) != 0 )
                    @if($days)
                        @foreach($days as $day)
                            <div class="form-group m-3">
                                <label class="m-1 bold" for="search"> Поиск тут </label>
                                <input id="search" type="text" class="search form-control" placeholder="Что ищем?">
                            </div>
                            <div class="col-md-2"></div>
                            <div class="col-md-8 mb-2">
                                <div class="card">
                                    <div class="card-body">
                                        <!-- Bordered Tabs Justified -->
                                        <ul class="nav nav-pills nav-tabs-bordered d-flex" id="borderedTabJustified" role="tablist">
                                            @foreach($sets as $index_sets => $set)
                                                @if($day->day_of_week === $set->day_of_week)
                                                    <li class="nav-item" role="presentation">
                                                        <button class="nav-link {{ $index_sets == 0 ? 'active' : '' }}" style="font-size: 10px" id="{{$set->id}}-tab"
                                                                data-bs-toggle="tab"
                                                                data-bs-target="#bordered-justified-{{$set->id}}" type="button"
                                                                role="tab" aria-controls="{{$set->id}}"
                                                                aria-selected="true">{{$set->time}} @lang('somewords.'.ucfirst($set->day_of_week))
                                                                @isset($set->date[$set->day_of_week])
                                                                    {{$set->date[$set->day_of_week]}}
                                                                @endisset
                                                            <span style="margin-left: 5px;" class="badge bg-dark text-light">{{$set->count_participant}}</span></button>
                                                    </li>
                                                @endif
                                            @endforeach
                                        </ul>
                                        <div class="tab-content pt-2" id="borderedTabJustifiedContent">
                                            @foreach($sets as $index_set => $set)
                                                @if($day->day_of_week == $set->day_of_week)
                                                    <div class="tab-pane fade show {{ $index_set == 0 ? 'active' : '' }}" id="bordered-justified-{{$set->id}}"
                                                         role="tabpanel" aria-labelledby="{{$set->id}}-tab">
                                                        <table class="table table-sm table-striped results">
                                                            <thead>
                                                            <tr>
                                                                <th scope="col">Участник</th>
                                                                @if(!$event->is_auto_categories)
                                                                    <th scope="col">Группа</th>
                                                                @endif
                                                                <th scope="col">Город</th>
                                                                <th scope="col">Команда</th>
                                                            </tr>
                                                            </thead>
                                                            <tbody>
                                                            @foreach($participants as $participant)
                                                                @if($participant['number_set'] == $set->number_set)
                                                                    <tr>
                                                                        <td>{{$participant['middlename']}}</td>
                                                                        @if(!$event->is_auto_categories)
                                                                            <td>{{$participant['category']}}</td>
                                                                        @endif
                                                                        <td>{{$participant['city']}}</td>
                                                                        <td>{{$participant['team']}}</td>
                                                                    </tr>
                                                                @endif
                                                                <tr class="warning no-result">
                                                                    <td colspan="6"><i class="fa fa-warning"></i> Нет результата</td>
                                                                </tr>
                                                            </tbody>
                                                            @endforeach
                                                        </table>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div><!-- End Bordered Tabs Justified -->
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2"></div>
                        @endforeach
                    @else
                        <div class="col-md-2"></div>
                        <div class="col mb-8">
                            <div class="form-group m-3">
                                <label class="m-1 bold" for="search"> Поиск тут </label>
                                <input id="search" type="text" class="search form-control" placeholder="Что ищем?">
                            </div>
                            <div class="card">
                                <div class="card-body">
                                    <ul class="nav nav-pills nav-tabs-bordered d-flex" id="borderedTabJustified" role="tablist">
                                        @foreach(['male', 'female'] as $index_gender => $var)
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link {{ $index_gender == 0 ? 'active' : '' }}" id="{{$var}}-tab"
                                                        data-bs-toggle="tab"
                                                        data-bs-target="#bordered-justified-{{$var}}" type="button"
                                                        role="tab" aria-controls="{{$var}}"
                                                        aria-selected="true">@lang('somewords.'.$var)</button>
                                            </li>
                                        @endforeach
                                    </ul>
                                    <div class="tab-content pt-2" id="borderedTabJustifiedContent">
                                        @foreach(['male', 'female'] as $index_gender => $var)
                                            <div class="tab-pane fade show {{ $index_gender == 0 ? 'active' : '' }}" id="bordered-justified-{{$var}}"
                                                 role="tabpanel" aria-labelledby="{{$var}}-tab">
                                                <table class="table table-sm table-striped results">
                                                    <thead>
                                                    <tr>
                                                        <th scope="col">Участник</th>
                                                        @if(!$event->is_auto_categories)
                                                            <th scope="col">Группа</th>
                                                        @endif
                                                        <th scope="col">Город</th>
                                                        <th scope="col">Команда</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    @foreach($participants as $participant)
                                                        @if($participant['gender'] == $var)

                                                            <tr>
                                                                <td>{{$participant['middlename']}}</td>
                                                                @if(!$event->is_auto_categories)
                                                                    <td>{{$participant['category']}}</td>
                                                                @endif
                                                                <td>{{$participant['city']}}</td>
                                                                <td>{{$participant['team']}}</td>
                                                            </tr>

                                                       @endif
                                                    @endforeach
                                                    <tr class="warning no-result">
                                                        <td colspan="6"><i class="fa fa-warning"></i> Нет результата</td>
                                                    </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        @endforeach
                                    </div><!-- End Bordered Tabs Justified -->
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2"></div>
                    @endif
                @else
                    <div class="col-md-2"></div>
                    <div class="col-md-8 mb-2">
                        <div class="card">
                            <div class="card-body">
                                <table class="table table-sm table-striped">
                                    <thead>
                                    <tr>
                                        <th scope="col">Участник</th>
                                        <th scope="col">Группа</th>
                                        <th scope="col">Город</th>
                                        <th scope="col">Команда</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr>
                                        <td>пусто</td>
                                        <td>пусто</td>
                                        <td>пусто</td>
                                        <td>пусто</td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2"></div>
                @endif
            </div>

        </section>
        <section class="section contact">
            <div class="row mt-3">
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
        if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
            $(document).ready(function () {
                {
                    let navs = document.querySelectorAll('li.nav-item')
                    navs.forEach(el => {
                        el.classList.add("w-100");
                    });
                    let nav_link = document.querySelectorAll('button.nav-link')
                    nav_link.forEach(el => {
                        el.classList.add("w-100");
                    });

                }
            });
        }
    </script>
@endsection
