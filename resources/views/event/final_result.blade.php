@extends('layouts.main_page.app')
@section('content')
    <main id="main" class="main">
        <section class="section contact">
            <div class="row gy-4">
                <div class="col-xl-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Мужчины <span
                                    class="badge bg-success text-light">{{$stats->male}}</span></h5>
                            <!-- Bordered Tabs Justified -->
                            <ul class="nav nav-tabs nav-tabs-bordered d-flex" id="borderedTabJustified" role="tablist">
                                @foreach($categories as $category)
                                    @if($category['id'] == $categories[array_search($category['id'], $categories)]['id'])
                                    <li class="nav-item flex-fill" role="presentation">
                                        <button class="nav-link w-100 active" id="{{$category['id']}}-tab"
                                                data-bs-toggle="tab"
                                                data-bs-target="#bordered-justified-{{$category['id']}}" type="button"
                                                role="tab" aria-controls="{{$category['id']}}"
                                                aria-selected="true">{{$category['category']}} <span
                                                class="badge bg-primary text-light">{{$stats->male_categories[$category['id']]}}</span>
                                        </button>
                                    </li>
                                    @else
                                        <li class="nav-item flex-fill" role="presentation">
                                            <button class="nav-link w-100" id="{{$category['id']}}-tab"
                                                    data-bs-toggle="tab"
                                                    data-bs-target="#bordered-justified-{{$category['id']}}" type="button"
                                                    role="tab" aria-controls="{{$category['id']}}"
                                                    aria-selected="true">{{$category['category']}} <span
                                                    class="badge bg-primary text-light">{{$stats->male_categories[$category['id']]}}</span>
                                            </button>
                                        </li>
                                    @endif
                                @endforeach
                            </ul>
                            <div class="tab-content pt-2" id="borderedTabJustifiedContent">
                                @foreach($categories as $category)
                                    @if($category['id'] ==  $categories[array_search($category['id'], $categories)]['id'])
                                        <div class="tab-pane fade show active" id="bordered-justified-{{$category['id']}}"
                                             role="tabpanel" aria-labelledby="{{$category['id']}}-tab">
                                            @else
                                                <div class="tab-pane fade show" id="bordered-justified-{{$category['id']}}"
                                                     role="tabpanel" aria-labelledby="{{$category['id']}}-tab">
                                            @endif
                                            <table class="table table-sm">
                                                <thead>
                                                <tr>
                                                    <th scope="col">
                                                        <b>Место
                                                    </th>
                                                    <th scope="col">
                                                        <b>Имя
                                                    </th>
                                                    <th scope="col">Город</th>
                                                    <th scope="col">Суммарные баллы</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach($result as $res)
                                                    @if($res['gender'] == "male")
                                                        @if($res['category_id'] == $category['id'])

                                                            <tr>
                                                                <td>{{$res['user_place']}}</td>
                                                                <td>{{$res['user_name']}}</td>
                                                                <td>{{$res['city']}}</td>
                                                                <td>{{$res['points']}}</td>
                                                            </tr>
                                                        @endif
                                                </tbody>
                                                @endif
                                                @endforeach
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
                <div class="col-xl-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Женщины <span
                                    class="badge bg-dark text-white">{{$stats->female}}</span></h5>
                            <!-- Bordered Tabs Justified -->
                            <ul class="nav nav-tabs nav-tabs-bordered d-flex" id="borderedTabJustifiedWomen"
                                role="tablist">
                                @foreach($categories as $category)
                                    @if($category['id'] == $categories[array_search($category['id'], $categories)]['id'])
                                        <li class="nav-item flex-fill" role="presentation">
                                            <button class="nav-link w-100 active" id="{{$category['id']}}-tab"
                                                    data-bs-toggle="tab"
                                                    data-bs-target="#bordered-justified-women-{{$category['id']}}"
                                                    type="button" role="tab" aria-controls="{{$category['id']}}"
                                                    aria-selected="true">{{$category['category']}} <span
                                                    class="badge bg-primary text-light">{{$stats->female_categories[$category['id']]}}</span>
                                            </button>
                                        </li>
                                    @else
                                        <li class="nav-item flex-fill" role="presentation">
                                            <button class="nav-link w-100" id="{{$category['id']}}-tab"
                                                    data-bs-toggle="tab"
                                                    data-bs-target="#bordered-justified-women-{{$category['id']}}"
                                                    type="button" role="tab" aria-controls="{{$category['id']}}"
                                                    aria-selected="true">{{$category['category']}} <span
                                                    class="badge bg-primary text-light">{{$stats->female_categories[$category['id']]}}</span>
                                            </button>
                                        </li>
                                    @endif
                                @endforeach
                            </ul>
                            <div class="tab-content pt-2" id="borderedTabJustifiedContentWomen">
                                @foreach($categories as $category)
                                        @if($category['id'] == $categories[array_search($category['id'], $categories)]['id'])
                                        <div class="tab-pane fade show active"
                                             id="bordered-justified-women-{{$category['id']}}" role="tabpanel"
                                             aria-labelledby="{{$category['id']}}-tab">
                                            @else
                                                <div class="tab-pane fade show"
                                                     id="bordered-justified-women-{{$category['id']}}" role="tabpanel"
                                                     aria-labelledby="{{$category['id']}}-tab">
                                            @endif
                                            <table class="table table-sm">
                                                <thead>
                                                <tr>
                                                    <th scope="col">
                                                        <b>Место
                                                    </th>
                                                    <th scope="col">
                                                        <b>Имя
                                                    </th>
                                                    <th scope="col">Город</th>
                                                    <th scope="col">Суммарные баллы</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach($result as $res)
                                                    @if($res['gender'] == "female")
                                                        @if($res['category_id'] == $category['id'])
                                                            <tr>
                                                                <td>{{$res['user_place']}}</td>
                                                                <td>{{$res['user_name']}}</td>
                                                                <td>{{$res['city']}}</td>
                                                                <td>{{$res['points']}}</td>
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
        </section>
    </main><!-- End #main -->
@endsection
