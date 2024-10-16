@extends('layouts.main_page.app')
@section('content')
    <section class="section contact">
            <div class="row mt-3 w-80">
                <div class="col-md-2"></div>
                <div class="col-md-8 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Мужчины <span
                                    class="badge bg-success text-light"></span></h5>
                            <!-- Pills Tabs -->
                            @if($event->is_sort_group_semifinal)
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
{{--                                                <th scope="col" style="background-color:  #106eea; color: white">Город</th>--}}
                                                <th scope="col" style="background-color:  #106eea; color: white">Результат</th>
                                                <th scope="col" style="background-color:  #106eea; color: white">Результат</th>
                                                <th scope="col" style="background-color:  #106eea; color: white">Попытки</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @if(count($result_each_routes['male'][$category['id']]) > 0)
                                                @foreach($result_each_routes['male'][$category['id']] as $res)
                                                    <tr>
                                                    <th scope="row">{{$res['place']}}</th>
                                                    <td>{{$res['middlename']}}</td>
{{--                                                    <td>{{$res['city']}}</td>--}}
                                                    <td>
                                                        @foreach($routes as $route)
                                                            @if(isset($res['amount_try_top_'.$route]) && isset($res['amount_try_zone_'.$route]))
                                                                <span class="bg-dark result-try" style="border-radius: 5px 5px 0 0;">М{{$route}}</span>
                                                            @endif
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
                                                    </td>
                                                    <td>
                                                        <span class="bg-dark result-try" style="font-size: 14px; border-radius: 5px 0 0 0;">T</span><span class="bg-dark result-try" style="font-size: 14px; border-radius: 0 5px 0 0;">Z</span><br>
                                                        <span class="bg-success result-try" style="font-size: 14px; border-radius: 0 0 0 5px;">{{$res['amount_top']}}</span><span class="bg-success result-try" style="font-size: 14px; border-radius: 0 0 5px 0;">{{$res['amount_zone']}}</span>
                                                    </td>
                                                    <td>
                                                        <span class="bg-dark result-try" style="font-size: 14px; border-radius: 5px 0 0 0;">T</span><span class="bg-dark result-try" style="font-size: 14px; border-radius: 0 5px 0 0;">Z</span><br>
                                                        <span class="bg-secondary result-try" style="font-size: 14px; border-radius: 0 0 0 5px;">{{$res['amount_try_top']}}</span><span class="bg-secondary result-try" style="font-size: 14px; border-radius: 0 0 5px 0;">{{$res['amount_try_zone']}}</span>
                                                    </td>
                                                    </tr>
                                                @endforeach
                                            @else
                                                <tr>
                                                    <th scope="row">-</th>
                                                    <td>Результата пока нет</td>
                                                    <td>
                                                        -
                                                    </td>
                                                    <td>
                                                        -
                                                    </td>
                                                </tr>
                                            @endif
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
{{--                                                <th scope="col" style="background-color:  #106eea; color: white">Город</th>--}}
                                                <th scope="col" style="background-color:  #106eea; color: white">Результат</th>
                                                <th scope="col" style="background-color:  #106eea; color: white">Результат</th>
                                                <th scope="col" style="background-color:  #106eea; color: white">Попытки</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @if(count($result_each_routes['male']) > 0)
                                                @foreach($result_each_routes['male'] as $res)
                                                <tr>
                                                    <th scope="row">{{$res['place'] ?? ''}}</th>
                                                    <td>{{$res['middlename']}}</td>
{{--                                                    <td>{{$res['city']}}</td>--}}
                                                    <td>
                                                        @foreach($routes as $route)
                                                            @if(isset($res['amount_try_top_'.$route]) && isset($res['amount_try_zone_'.$route]))
                                                                <span class="bg-dark result-try" style="border-radius: 5px 5px 0 0;">М{{$route}}</span>
                                                            @endif
                                                        @endforeach
                                                        <br>
                                                        @foreach($routes as $route)
                                                            @isset($res['amount_try_top_'.$route])
                                                                @if($res['amount_try_top_'.$route] > 0)
                                                                    <span class="bg-success result-try">{{$res['amount_try_top_'.$route]}}</span>
                                                                @else
                                                                    <span class="bg-danger result-try" >-</span>
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
                                                    </td>
                                                    <td>
                                                        <span class="bg-dark result-try" style="font-size: 14px; border-radius: 5px 0 0 0;">T</span><span class="bg-dark result-try" style="font-size: 14px; border-radius: 0 5px 0 0;">Z</span><br>
                                                        <span class="bg-success result-try" style="font-size: 14px; border-radius: 0 0 0 5px;">{{$res['amount_top']}}</span><span class="bg-success result-try" style="font-size: 14px; border-radius: 0 0 5px 0;">{{$res['amount_zone']}}</span>
                                                    </td>
                                                    <td>
                                                        <span class="bg-dark result-try" style="font-size: 14px; border-radius: 5px 0 0 0;">T</span><span class="bg-dark result-try" style="font-size: 14px; border-radius: 0 5px 0 0;">Z</span><br>
                                                        <span class="bg-secondary result-try" style="font-size: 14px; border-radius: 0 0 0 5px;">{{$res['amount_try_top']}}</span><span class="bg-secondary result-try" style="font-size: 14px; border-radius: 0 0 5px 0;">{{$res['amount_try_zone']}}</span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                            @else
                                                <tr>
                                                    <th scope="row">-</th>
                                                    <td>Результата пока нет</td>
                                                    <td>
                                                        -
                                                    </td>
                                                    <td>
                                                        -
                                                    </td>
                                                </tr>
                                            @endif
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
                            @if($event->is_sort_group_semifinal)
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
{{--                                                    <th scope="col" style="background-color:  #106eea; color: white">Город</th>--}}
                                                    <th scope="col" style="background-color:  #106eea; color: white">Результат</th>
                                                    <th scope="col" style="background-color:  #106eea; color: white">Результат</th>
                                                    <th scope="col" style="background-color:  #106eea; color: white">Попытки</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @if(count($result_each_routes['female'][$category['id']]))
                                                    @foreach($result_each_routes['female'][$category['id']] as $res)
                                                        <tr>
                                                        <th scope="row">{{$res['place']}}</th>
                                                        <td>{{$res['middlename']}}</td>
{{--                                                        <td>{{$res['city']}}</td>--}}
                                                        <td>
                                                            @foreach($routes as $route)
                                                                @if(isset($res['amount_try_top_'.$route]) && isset($res['amount_try_zone_'.$route]))
                                                                    <span class="bg-dark result-try" style="border-radius: 5px 5px 0 0;">Ж{{$route}}</span>
                                                                @endif
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
                                                        </td>
                                                            <td>
                                                                <span class="bg-dark result-try" style="font-size: 14px; border-radius: 5px 0 0 0;">T</span><span class="bg-dark result-try" style="font-size: 14px; border-radius: 0 5px 0 0;">Z</span><br>
                                                                <span class="bg-success result-try" style="font-size: 14px; border-radius: 0 0 0 5px;">{{$res['amount_top']}}</span><span class="bg-success result-try" style="font-size: 14px; border-radius: 0 0 5px 0;">{{$res['amount_zone']}}</span>
                                                            </td>
                                                            <td>
                                                                <span class="bg-dark result-try" style="font-size: 14px; border-radius: 5px 0 0 0;">T</span><span class="bg-dark result-try" style="font-size: 14px; border-radius: 0 5px 0 0;">Z</span><br>
                                                                <span class="bg-secondary result-try" style="font-size: 14px; border-radius: 0 0 0 5px;">{{$res['amount_try_top']}}</span><span class="bg-secondary result-try" style="font-size: 14px; border-radius: 0 0 5px 0;">{{$res['amount_try_zone']}}</span>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                @else
                                                    <tr>
                                                        <th scope="row">-</th>
                                                        <td>Результата пока нет</td>
                                                        <td>
                                                            -
                                                        </td>
                                                        <td>
                                                            -
                                                        </td>
                                                    </tr>
                                                @endif
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
{{--                                                <th scope="col" style="background-color:  #106eea; color: white">Город</th>--}}
                                                <th scope="col" style="background-color:  #106eea; color: white">Результат</th>
                                                <th scope="col" style="background-color:  #106eea; color: white">Результат</th>
                                                <th scope="col" style="background-color:  #106eea; color: white">Попытки</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @if(count($result_each_routes['female']) > 0)
                                                @foreach($result_each_routes['female'] as $res)
                                                <tr>
                                                    <th scope="row">{{$res['place']}}</th>
                                                    <td>{{$res['middlename']}}</td>
{{--                                                    <td>{{$res['city']}}</td>--}}
                                                    <td>
                                                        @foreach($routes as $route)
                                                            @if(isset($res['amount_try_top_'.$route]) && isset($res['amount_try_zone_'.$route]))
                                                                <span class="bg-dark result-try" style="border-radius: 5px 5px 0 0;">Ж{{$route}}</span>
                                                            @endif
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
                                                    </td>
                                                    <td>
                                                        <span class="bg-dark result-try" style="font-size: 14px; border-radius: 5px 0 0 0;">T</span><span class="bg-dark result-try" style="font-size: 14px; border-radius: 0 5px 0 0;">Z</span><br>
                                                        <span class="bg-success result-try" style="font-size: 14px; border-radius: 0 0 0 5px;">{{$res['amount_top']}}</span><span class="bg-success result-try" style="font-size: 14px; border-radius: 0 0 5px 0;">{{$res['amount_zone']}}</span>
                                                    </td>
                                                    <td>
                                                        <span class="bg-dark result-try" style="font-size: 14px; border-radius: 5px 0 0 0;">T</span><span class="bg-dark result-try" style="font-size: 14px; border-radius: 0 5px 0 0;">Z</span><br>
                                                        <span class="bg-secondary result-try" style="font-size: 14px; border-radius: 0 0 0 5px;">{{$res['amount_try_top']}}</span><span class="bg-secondary result-try" style="font-size: 14px; border-radius: 0 0 5px 0;">{{$res['amount_try_zone']}}</span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                            @else
                                                <tr>
                                                    <th scope="row">-</th>
                                                    <td>Результата пока нет</td>
                                                    <td>
                                                        -
                                                    </td>
                                                    <td>
                                                        -
                                                    </td>
                                                </tr>
                                            @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div><!-- End Pills Tabs -->
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-md-2"></div>
            </div>
        </section>
@endsection
