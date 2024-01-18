@extends('layouts.main_page.app')
@section('content')
    <main id="main" class="main">
        <section class="section contact">
            <div class="row gy-4">
                <div class="col-xl-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Мужчины</h5>
                            <!-- Bordered Tabs Justified -->
                            <ul class="nav nav-tabs nav-tabs-bordered d-flex" id="borderedTabJustified" role="tablist">
                                @foreach($categories as $category)
                                    @if($category->id == 1)
                                        <li class="nav-item flex-fill" role="presentation">
                                            <button class="nav-link w-100 active" id="{{$category->id}}-tab"
                                                    data-bs-toggle="tab"
                                                    data-bs-target="#bordered-justified-{{$category->id}}" type="button"
                                                    role="tab" aria-controls="{{$category->id}}"
                                                    aria-selected="true">{{$category->category}}</button>
                                        </li>
                                    @else
                                        <li class="nav-item flex-fill" role="presentation">
                                            <button class="nav-link w-100" id="{{$category->id}}-tab"
                                                    data-bs-toggle="tab"
                                                    data-bs-target="#bordered-justified-{{$category->id}}" type="button"
                                                    role="tab" aria-controls="{{$category->id}}"
                                                    aria-selected="true">{{$category->category}}</button>
                                        </li>
                                    @endif
                                @endforeach
                            </ul>
                            <div class="tab-content pt-2" id="borderedTabJustifiedContent">
                                @foreach($categories as $category)
                                    @if($category->id == 1)
                                        <div class="tab-pane fade show active" id="bordered-justified-{{$category->id}}"
                                             role="tabpanel" aria-labelledby="{{$category->id}}-tab">
                                            <table class="table table-sm">
                                                <thead>
                                                <tr>
                                                    <th scope="col">Участник</th>
                                                    <th scope="col">Сет</th>
                                                    <th scope="col">Город</th>
                                                    <th scope="col">Команда</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach($participants as $participant)
                                                    @if($participant['gender'] == "male")
                                                        @if($participant['category_id'] == $category->id)
                                                            <tr>
                                                                <td>{{$participant['firstname']}} {{$participant['lastname']}}</td>
                                                                <td>{{$participant['number_set']}} ({{$participant['time']}})
                                                                </td>
                                                                <td>{{$participant['city']}}</td>
                                                                <td>{{$participant['team']}}</td>
                                                            </tr>
                                                        @endif
                                                </tbody>
                                                @endif
                                                @endforeach
                                            </table>
                                        </div>
                                    @else
                                        <div class="tab-pane fade" id="bordered-justified-{{$category->id}}"
                                             role="tabpanel" aria-labelledby="{{$category->id}}-tab">
                                            <table class="table table-sm">
                                                <thead>
                                                <tr>
                                                    <th scope="col">Участник</th>
                                                    <th scope="col">Сет</th>
                                                    <th scope="col">Город</th>
                                                    <th scope="col">Команда</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach($participants as $participant)
                                                    @if($participant['gender'] == "male")
                                                        @if($participant['category_id'] == $category->id)
                                                            <tr>
                                                                <td>{{$participant['firstname']}} {{$participant['lastname']}}</td>
                                                                <td>{{$participant['number_set']}} ({{$participant['time']}})
                                                                </td>
                                                                <td>{{$participant['city']}}</td>
                                                                <td>{{$participant['team']}}</td>
                                                            </tr>
                                                        @endif
                                                    @endif
                                                @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @endif
                                @endforeach
                            </div><!-- End Bordered Tabs Justified -->
                        </div>
                    </div>
                </div>
                <div class="col-xl-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Женщины</h5>
                            <!-- Bordered Tabs Justified -->
                            <ul class="nav nav-tabs nav-tabs-bordered d-flex" id="borderedTabJustifiedWomen"
                                role="tablist">
                                @foreach($categories as $category)
                                    @if($category->id == 1)
                                        <li class="nav-item flex-fill" role="presentation">
                                            <button class="nav-link w-100 active" id="{{$category->id}}-tab"
                                                    data-bs-toggle="tab"
                                                    data-bs-target="#bordered-justified-women-{{$category->id}}"
                                                    type="button" role="tab" aria-controls="{{$category->id}}"
                                                    aria-selected="true">{{$category->category}}</button>
                                        </li>
                                    @else
                                        <li class="nav-item flex-fill" role="presentation">
                                            <button class="nav-link w-100" id="{{$category->id}}-tab"
                                                    data-bs-toggle="tab"
                                                    data-bs-target="#bordered-justified-women-{{$category->id}}"
                                                    type="button" role="tab" aria-controls="{{$category->id}}"
                                                    aria-selected="true">{{$category->category}}</button>
                                        </li>
                                    @endif
                                @endforeach
                            </ul>
                            <div class="tab-content pt-2" id="borderedTabJustifiedContentWomen">
                                @foreach($categories as $category)
                                    @if($category->id == 1)
                                        <div class="tab-pane fade show active"
                                             id="bordered-justified-women-{{$category->id}}" role="tabpanel"
                                             aria-labelledby="{{$category->id}}-tab">
                                            <table class="table table-sm">
                                                <thead>
                                                <tr>
                                                    <th scope="col">Участник</th>
                                                    <th scope="col">Сет</th>
                                                    <th scope="col">Город</th>
                                                    <th scope="col">Команда</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach($participants as $participant)
                                                    @if($participant['gender'] == "female")
                                                        @if($participant['category_id'] == $category->id)
                                                            <tr>
                                                                <td>{{$participant['firstname']}} {{$participant['lastname']}}</td>
                                                                <td>{{$participant['number_set']}} ({{$participant['time']}})
                                                                </td>
                                                                <td>{{$participant['city']}}</td>
                                                                <td>{{$participant['team']}}</td>
                                                            </tr>
                                                        @endif
                                                    @endif
                                                @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="tab-pane fade" id="bordered-justified-women-{{$category->id}}"
                                             role="tabpanel" aria-labelledby="{{$category->id}}-tab">
                                            <table class="table table-sm">
                                                <thead>
                                                <tr>
                                                    <th scope="col">Участник</th>
                                                    <th scope="col">Сет</th>
                                                    <th scope="col">Город</th>
                                                    <th scope="col">Команда</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach($participants as $participant)
                                                    @if($participant['gender'] == "female")
                                                        @if($participant['category_id'] == $category->id)
                                                            <tr>
                                                                <td>{{$participant['firstname']}} {{$participant['lastname']}}</td>
                                                                <td>{{$participant['number_set']}} ({{$participant['time']}})
                                                                </td>
                                                                <td>{{$participant['city']}}</td>
                                                                <td>{{$participant['team']}}</td>
                                                            </tr>
                                                        @endif
                                                    @endif
                                                @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @endif
                                @endforeach
                            </div><!-- End Bordered Tabs Justified -->
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main><!-- End #main -->
@endsection
