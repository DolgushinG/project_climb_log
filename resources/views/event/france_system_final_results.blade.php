@extends('layouts.main_page.app')
@section('content')
    <section class="section contact">
            <div class="row mt-3 w-80">
                <div class="col-xl-12 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Мужчины <span
                                    class="badge bg-success text-light"></span></h5>
                            <!-- Pills Tabs -->
                            @if($event->is_sort_group_final)
                                <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                                    @foreach($categories as $index => $category)
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link {{ $index == 0 ? 'active' : '' }}" id="pills-home-men-tab" data-bs-toggle="pill" data-bs-target="#pills-home-men-{{$category['id']}}" type="button" role="tab" aria-controls="pills-home-men-{{$category['id']}}" aria-selected="true">{{$category['category']}}</button>
                                        </li>
                                    @endforeach
                                </ul>
                                <div class="tab-content pt-2" id="myTabContent">
                                    @foreach($categories as $index => $category)
                                        <div class="tab-pane fade show {{ $index == 0 ? 'active' : '' }}" id="pills-home-men-{{$category['id']}}" role="tabpanel" aria-labelledby="home-tab-men-{{$category['id']}}">
                                            <!-- Table with stripped rows -->
                                            <table class="table table-striped">
                                                <thead>
                                                <tr>
                                                    <th scope="col" style="background-color:  #106eea; color: white">Место</th>
                                                    <th scope="col" style="background-color:  #106eea; color: white">Фамилия Имя</th>
                                                    <th scope="col" style="background-color:  #106eea; color: white">Город</th>
                                                    <th scope="col" style="background-color:  #106eea; color: white">Результат</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach($result_each_routes['male'][$category['id']] as $res)
                                                    <tr>
                                                        <th scope="row">{{$res['place']}}</th>
                                                        <td>{{$res['middlename']}}</td>
                                                        <td>{{$res['city']}}</td>
                                                        <td>
                                                            @foreach($routes as $route)
                                                                <span class="bg-dark result-try" style="border-radius: 5px 5px 0 0;">М{{$route}}</span>
                                                            @endforeach
                                                                <br>
                                                            @foreach($routes as $route)
                                                                @isset($res['amount_try_top_'.$route])
                                                                    @if($res['amount_try_top_'.$route] > 0)
                                                                        <span class="bg-success result-try">{{$res['amount_try_top_'.$route]}}</span>
                                                                    @else
                                                                        <span class="bg-danger result-try">-</span>
                                                                    @endif
                                                                @endisset
                                                            @endforeach
                                                            <br>
                                                            @foreach($routes as $route)
                                                                @isset($res['amount_try_top_'.$route])
                                                                    @if($res['amount_try_zone_'.$route] > 0)
                                                                        <span class="bg-success result-try" style="border-radius: 0 0 5px 5px;">{{$res['amount_try_zone_'.$route]}}</span>
                                                                    @else
                                                                        <span class="bg-danger result-try" style="color:white; border-radius: 0 0 5px 5px;">-</span>
                                                                    @endif
                                                                @endisset
                                                            @endforeach
                                                            {{$res['amount_top']}}T{{$res['amount_try_top']}}z {{$res['amount_zone']}} {{$res['amount_try_zone']}}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                            </table>
                                            <!-- End Table with stripped rows -->
                                        </div>
                                    @endforeach
                                </div><!-- End Pills Tabs -->
                            @else
                                <div class="tab-content pt-2" id="myTabContent">
                                    <div class="tab-pane fade show active" id="pills-home-male" role="tabpanel" aria-labelledby="home-tab-male">
                                        <!-- Table with stripped rows -->
                                        <table class="table table-striped">
                                            <thead>
                                            <tr>
                                                <th scope="col" style="background-color:  #106eea; color: white">Место</th>
                                                <th scope="col" style="background-color:  #106eea; color: white">Фамилия Имя</th>
                                                <th scope="col" style="background-color:  #106eea; color: white">Город</th>
                                                <th scope="col" style="background-color:  #106eea; color: white">Результат</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($result_each_routes['male'] as $res)
                                                <tr>
                                                    <th scope="row">{{$res['place'] ?? ''}}</th>
                                                    <td>{{$res['middlename']}}</td>
                                                    <td>{{$res['city']}}</td>
                                                    <td>
                                                        @foreach($routes as $route)
                                                            <span class="bg-dark result-try" style="border-radius: 5px 5px 0 0;">М{{$route}}</span>
                                                        @endforeach
                                                        @foreach($routes as $route)
                                                            <br>
                                                            @isset($res['amount_try_top_'.$route])
                                                                @if($res['amount_try_top_'.$route] > 0)
                                                                    <span class="bg-success result-try">{{$res['amount_try_top_'.$route]}}</span>
                                                                @else
                                                                    <span class="bg-danger result-try">-</span>
                                                                @endif
                                                            @endisset
                                                        @endforeach
                                                        <br>
                                                        @foreach($routes as $route)
                                                            @isset($res['amount_try_top_'.$route])
                                                                @if($res['amount_try_zone_'.$route] > 0)
                                                                    <span class="bg-success result-try" style="border-radius: 0 0 5px 5px;">{{$res['amount_try_zone_'.$route]}}</span>
                                                                @else
                                                                    <span class="bg-danger result-try" style="color:white; border-radius: 0 0 5px 5px;">-</span>
                                                                @endif
                                                            @endisset
                                                        @endforeach
                                                        {{$res['amount_top']}}T{{$res['amount_try_top']}}z {{$res['amount_zone']}} {{$res['amount_try_zone']}}
                                                    </td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                        <!-- End Table with stripped rows -->
                                    </div>
                                </div><!-- End Pills Tabs -->
                            @endif
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Женщины <span
                                    class="badge bg-success text-light"></span></h5>
                            <!-- Pills Tabs -->
                            @if($event->is_sort_group_final)
                                <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                                    @foreach($categories as $index => $category)
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link {{ $index == 0 ? 'active' : '' }}" id="pills-home-tab" data-bs-toggle="pill" data-bs-target="#pills-home-{{$category['id']}}" type="button" role="tab" aria-controls="pills-home-{{$category['id']}}" aria-selected="true">{{$category['category']}}</button>
                                        </li>
                                    @endforeach
                                </ul>
                                <div class="tab-content pt-2" id="myTabContent">
                                    @foreach($categories as $index => $category)
                                        <div class="tab-pane fade show {{ $index == 0 ? 'active' : '' }}" id="pills-home-{{$category['id']}}" role="tabpanel" aria-labelledby="home-tab-{{$category['id']}}">
                                            <!-- Table with stripped rows -->
                                            <table class="table table-striped">
                                                <thead>
                                                <tr>
                                                    <th scope="col" style="background-color:  #106eea; color: white">Место</th>
                                                    <th scope="col" style="background-color:  #106eea; color: white">Фамилия Имя</th>
                                                    <th scope="col" style="background-color:  #106eea; color: white">Город</th>
                                                    <th scope="col" style="background-color:  #106eea; color: white">Результат</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach($result_each_routes['female'][$category['id']] as $res)
                                                    <tr>
                                                        <th scope="row">{{$res['place']}}</th>
                                                        <td>{{$res['middlename']}}</td>
                                                        <td>{{$res['city']}}</td>
                                                        <td>
                                                            @foreach($routes as $route)
                                                                <span class="bg-dark result-try" style="border-radius: 5px 5px 0 0;">Ж{{$route}}</span>
                                                            @endforeach
                                                            <br>
                                                            @foreach($routes as $route)
                                                                @isset($res['amount_try_top_'.$route])
                                                                    @if($res['amount_try_top_'.$route] > 0)
                                                                        <span class="bg-success result-try">{{$res['amount_try_top_'.$route]}}</span>
                                                                    @else
                                                                        <span class="bg-danger result-try">-</span>
                                                                    @endif
                                                                @endisset
                                                            @endforeach
                                                            <br>
                                                            @foreach($routes as $route)
                                                                @isset($res['amount_try_top_'.$route])
                                                                    @if($res['amount_try_zone_'.$route])
                                                                        <span class="bg-success result-try" style="border-radius: 0 0 5px 5px;">{{$res['amount_try_zone_'.$route]}}</span>
                                                                    @else
                                                                        <span class="bg-danger result-try" style="color:white; border-radius: 0 0 5px 5px;">-</span>
                                                                    @endif
                                                                @endisset
                                                            @endforeach
                                                            {{$res['amount_top']}}T{{$res['amount_try_top']}}z {{$res['amount_zone']}} {{$res['amount_try_zone']}}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @endforeach
                                </div><!-- End Pills Tabs -->
                            @else
                                <div class="tab-content pt-2" id="myTabContent">
                                    <div class="tab-pane fade show active" id="pills-home-female" role="tabpanel" aria-labelledby="home-tab-female">
                                        <!-- Table with stripped rows -->
                                        <table class="table table-striped">
                                            <thead>
                                            <tr>
                                                <th scope="col" style="background-color:  #106eea; color: white">Место</th>
                                                <th scope="col" style="background-color:  #106eea; color: white">Фамилия Имя</th>
                                                <th scope="col" style="background-color:  #106eea; color: white">Город</th>
                                                <th scope="col" style="background-color:  #106eea; color: white">Результат</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($result_each_routes['female'] as $res)
                                                <tr>
                                                    <th scope="row">{{$res['place']}}</th>
                                                    <td>{{$res['middlename']}}</td>
                                                    <td>{{$res['city']}}</td>
                                                    <td>
                                                        @foreach($routes as $route)
                                                            <span class="bg-dark result-try" style="border-radius: 5px 5px 0 0;">Ж{{$route}}</span>
                                                        @endforeach
                                                        <br>
                                                        @foreach($routes as $route)
                                                            @isset($res['amount_try_top_'.$route])
                                                                @if($res['amount_try_top_'.$route] > 0)
                                                                    <span class="bg-success result-try">{{$res['amount_try_top_'.$route]}}</span>
                                                                @else
                                                                    <span class="bg-danger result-try">-</span>
                                                                @endif
                                                            @endisset
                                                        @endforeach
                                                        <br>
                                                        @foreach($routes as $route)
                                                            @isset($res['amount_try_top_'.$route])
                                                                @if($res['amount_try_zone_'.$route])
                                                                    <span class="bg-success result-try" style="border-radius: 0 0 5px 5px;">{{$res['amount_try_zone_'.$route]}}</span>
                                                                @else
                                                                    <span class="bg-danger result-try" style="color:white; border-radius: 0 0 5px 5px;">-</span>
                                                                @endif
                                                            @endisset
                                                        @endforeach
                                                        {{$res['amount_top']}}T{{$res['amount_try_top']}}z {{$res['amount_zone']}} {{$res['amount_try_zone']}}
                                                    </td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div><!-- End Pills Tabs -->
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </section>
@endsection
