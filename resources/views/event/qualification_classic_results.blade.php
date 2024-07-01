@extends('layouts.main_page.app')
@section('content')
    <section class="section-bg contact">
            <div class="row m-3">
                <div class="col-xl-12 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Мужчины <span
                                    class="badge bg-success text-light">{{$stats->male}}</span></h5>
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
                                            <table class="table table-auto font-size table-striped">
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
                                                        @if($res['category_id'] == $category['id'])
                                                            <tr>
                                                                <td>{{$res['middlename']}}</td>
                                                                @if($event->is_open_main_rating)
                                                                    <td>{{$res["user_global_place"]}}</td>
                                                                    {{--                                                                <td>{{$res['city']}}</td>--}}
                                                                    <td>{{$res['global_points']}}</td>
                                                                @else
                                                                    <td>{{$res["user_place"]}}</td>
                                                                    {{--                                                                <td>{{$res['city']}}</td>--}}
                                                                    <td>{{$res['points']}}</td>
                                                                @endif
                                                            </tr>
                                                        @endif
                                                    @endif
                                                @endforeach
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
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Женщины <span
                                    class="badge bg-dark text-white">{{$stats->female}}</span></h5>
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
                                        <table class="table table-sm table-striped">
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
                                                    @if($res['category_id'] == $category['id'])
                                                        <tr>
                                                            <td>{{$res['middlename']}}</td>

                                                            @if($event->is_open_main_rating)
                                                                <td>{{$res["user_global_place"]}}</td>
                                                                {{--                                                                <td>{{$res['city']}}</td>--}}
                                                                <td>{{$res['global_points']}}</td>
                                                            @else
                                                                <td>{{$res["user_place"]}}</td>
                                                                {{--                                                                <td>{{$res['city']}}</td>--}}
                                                                <td>{{$res['points']}}</td>
                                                            @endif

                                                        </tr>
                                                    @endif
                                                @endif
                                            @endforeach
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
                </div>
            </div>
        </section>
@endsection
