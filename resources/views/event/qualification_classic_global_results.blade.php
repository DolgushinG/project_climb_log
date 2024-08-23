@extends('layouts.main_page.app')
@section('content')
    <section class="section-bg contact">
            <div class="row m-3">
                <div class="col-md-2"></div>
                <div class="col-md-8 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Мужчины <span
                                    class="badge bg-success text-light">{{$stats->male}}</span></h5>
                            <div class="form-group m-3">
                                <label class="m-1 bold" for="search-men"> Поиск тут </label>
                                <input id="search-men" type="text" class="search-men form-control" placeholder="Что ищем?">
                            </div>
                            <!-- Bordered Tabs Justified -->
                            <ul class="nav nav-pills nav-tabs-bordered d-flex" id="borderedTabJustified" role="tablist">
                                @foreach($categories as $index => $category)
                                    <li class="nav-item mr-2 flex-fill" role="presentation" style="margin-right: 8px!important;">
                                        <button class="nav-link w-100 {{ $index == 0 ? 'active' : '' }}" id="{{$category['id']}}-tab"
                                                data-bs-toggle="tab"
                                                data-bs-target="#bordered-justified-{{$category['id']}}" type="button"
                                                role="tab" aria-controls="{{$category['id']}}"
                                                aria-selected="true">{{$category['category']}} <span
                                                class="badge bg-primary text-light">{{$stats->male_categories[$category['id']]}}</span>
                                        </button>
                                    </li>
                                @endforeach
                            </ul>
                            <div class="tab-content pt-2" id="borderedTabJustifiedContent">
                                @foreach($categories as $index => $category)
                                        <div class="tab-pane fade show {{ $index == 0 ? 'active' : '' }}" id="bordered-justified-{{$category['id']}}"
                                             role="tabpanel" aria-labelledby="{{$category['id']}}-tab">
                                            <table class="table table-auto font-size table-striped results-men">
                                                <thead>
                                                <tr>
                                                    <th scope="col">
                                                        <b>Место
                                                    </th>
                                                    <th scope="col">
                                                        <b>Имя
                                                    </th>
{{--                                                    <th scope="col">Город</th>--}}
                                                    <th scope="col">Суммарные баллы</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach($result as $res)
                                                    @if($res['gender'] == "male")
                                                        @if($res['global_category_id'] == $category['id'])
                                                            <tr>
                                                                <td>{{$res['user_global_place']}}</td>
                                                                <td>{{$res['middlename']}}</td>
                                                                <td>{{$res['global_points']}}</td>
                                                            </tr>
                                                        @endif
                                                    @endif
                                                @endforeach
                                                <tr class="warning no-result-men">
                                                    <td colspan="6"><i class="fa fa-warning"></i> Нет результата</td>
                                                </tr>
                                                </tbody>
                                            </table>
                                            @if($stats->male_categories[$category['id']] == 0)
                                                <p>Результатов пока нет</p>
                                            @endif
                                        </div>
                                @endforeach
                            </div><!-- End Bordered Tabs Justified -->
                        </div>
                    </div>
                </div>
                <div class="col-md-2"></div>
                <div class="col-md-2"></div>
                <div class="col-md-8 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Женщины <span
                                    class="badge bg-dark text-white">{{$stats->female}}</span></h5>
                            <div class="form-group m-3">
                                <label class="m-1 bold" for="search-women"> Поиск тут </label>
                                <input id="search-women" type="text" class="search-women form-control" placeholder="Что ищем?">
                            </div>
                            <!-- Bordered Tabs Justified -->
                            <ul class="nav nav-pills nav-tabs-bordered d-flex" id="borderedTabJustified"
                                role="tablist">
                                @foreach($categories as $index => $category)
                                    <li class="nav-item flex-fill" role="presentation" style="margin-right: 8px!important;">
                                        <button class="nav-link mr-2 w-100 {{ $index == 0 ? 'active' : '' }}" id="tab-women-{{$category['id']}}"
                                                data-bs-toggle="tab"
                                                data-bs-target="#bordered-justified-women-{{$category['id']}}"
                                                type="button" role="tab" aria-controls="women-{{$category['id']}}"
                                                aria-selected="true">{{$category['category']}} <span
                                                class="badge bg-primary text-light">{{$stats->female_categories[$category['id']]}}</span>
                                        </button>
                                    </li>
                                @endforeach
                            </ul>
                            <div class="tab-content pt-2" id="borderedTabJustifiedContent">
                                @foreach($categories as $index => $category)
                                    <div class="tab-pane fade show {{ $index == 0 ? 'active' : '' }}" id="bordered-justified-women-{{$category['id']}}" role="tabpanel" aria-labelledby="tab-women-{{$category['id']}}">
                                        <table class="table table-sm table-striped results-women">
                                        <thead>
                                            <tr>
                                                <th scope="col">
                                                    <b>Место
                                                </th>
                                                <th scope="col">
                                                    <b>Имя
                                                </th>
{{--                                                <th scope="col">Город</th>--}}
                                                <th scope="col">Суммарные баллы</th>
                                            </tr>
                                        </thead>
                                            <tbody>
                                            @foreach($result as $res)
                                                @if($res['gender'] == "female")
                                                    @if($res['global_category_id'] == $category['id'])
                                                        <tr>
                                                            <td>{{$res['user_global_place']}}</td>
                                                            <td>{{$res['middlename']}}</td>
                                                            <td>{{$res['global_points']}}</td>
                                                        </tr>
                                                    @endif
                                                @endif
                                            @endforeach
                                            <tr class="warning no-result-women">
                                                <td colspan="6"><i class="fa fa-warning"></i> Нет результата</td>
                                            </tr>
                                            </tbody>
                                        </table>
                                            @if($stats->female_categories[$category['id']] == 0)
                                                <p>Результатов пока нет</p>
                                            @endif
                                        </div>
                                @endforeach
                            </div><!-- End Bordered Tabs Justified -->
                        </div>
                    </div>
                </div>
                <div class="col-md-2"></div>
                @if($result_team && $event->is_open_team_result)
                    <div class="col-md-2"></div>
                    <div class="col-md-8 mb-3">
                        <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Командный зачет <span
                                    class="badge bg-dark text-white">{{$stats->team}}</span></h5>
                                <table class="table table-sm table-striped">
                                    <thead>
                                    <tr>
                                        <th scope="col">
                                            <b>Место
                                        </th>
                                        <th scope="col">
                                            <b>Команда
                                        </th>
                                        <th scope="col">Суммарные баллы</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($result_team as $index => $res)
                                        <tr>
                                            <td>{{$res['place']}}</td>
                                            <td>{{$res['team']}}</td>
                                            <td>{{$res['points']}}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                                @if($stats->team == 0)
                                    <p>Результатов пока нет</p>
                                @endif
                        </div>
                    </div>
                    </div>
                    <div class="col-md-2"></div>
                @endif
            </div>
        </section>
        <script>
        $(document).ready(function() {
            $(".search-men").keyup(function () {
                var searchTerm = $(".search-men").val();
                var listItem = $('.results-men tbody').children('tr');
                var searchSplit = searchTerm.replace(/ /g, "'):containsi('")

                $.extend($.expr[':'], {'containsi': function(elem, i, match, array){
                        return (elem.textContent || elem.innerText || '').toLowerCase().indexOf((match[3] || "").toLowerCase()) >= 0;
                    }
                });

                $(".results-men tbody tr").not(":containsi('" + searchSplit + "')").each(function(e){
                    $(this).attr('visible','false');
                });

                $(".results-men tbody tr:containsi('" + searchSplit + "')").each(function(e){
                    $(this).attr('visible','true');
                });

                var jobCount = $('.results-men tbody tr[visible="true"]').length;
                $('.counter').text('Найдено ' + jobCount );

                if(jobCount == '0') {$('.no-result-men').show();}
                else {$('.no-result-men').hide();}
            });
            $(".search-women").keyup(function () {
                var searchTerm = $(".search-women").val();
                var listItem = $('.results-women tbody').children('tr');
                var searchSplit = searchTerm.replace(/ /g, "'):containsi('")

                $.extend($.expr[':'], {'containsi': function(elem, i, match, array){
                        return (elem.textContent || elem.innerText || '').toLowerCase().indexOf((match[3] || "").toLowerCase()) >= 0;
                    }
                });

                $(".results-women tbody tr").not(":containsi('" + searchSplit + "')").each(function(e){
                    $(this).attr('visible','false');
                });

                $(".results-women tbody tr:containsi('" + searchSplit + "')").each(function(e){
                    $(this).attr('visible','true');
                });

                var jobCount = $('.results-women tbody tr[visible="true"]').length;
                $('.counter').text('Найдено ' + jobCount );

                if(jobCount == '0') {$('.no-result-women').show();}
                else {$('.no-result-women').hide();}
            });
        });
    </script>
@endsection
