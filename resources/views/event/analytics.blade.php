@extends('layouts.main_page.app')
@section('content')
    <script src="{{asset('js/analytics.js')}}"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <section class="section-bg contact">
        <div class="row m-3">
            <div class="col-xl-12 mb-3">
                <div class="container">
                    <h2>Аналитика прохождений трасс</h2>
                    <div class="row mb-3">
                        <div class="col">
                            <label for="gradeSelect">Трасса</label>
                            <select id="gradeSelect" class="form-control">
                                <option value="">Все категории</option>
                                @foreach($grades as $route_id => $grade)
                                    <option value="{{$grade}}">{{$route_id}}. {{$grade}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col">
                            <label for="genderSelect">Пол</label>
                            <select id="genderSelect" class="form-control">
                                <option value="male">Мужчины</option>
                                <option value="female">Женщины</option>
                            </select>
                        </div>
                    </div>
                    <canvas id="myChart" width="400" height="200" data-id="{{$event->id}}"></canvas>
                </div>
            </div>
        </div>
    </section>
{{--    <style>--}}
{{--        .container {--}}
{{--            margin-top: 20px;--}}
{{--        }--}}

{{--        canvas {--}}
{{--            max-width: 100%;--}}
{{--            height: auto;--}}
{{--        }--}}

{{--        .filters {--}}
{{--            margin-bottom: 20px;--}}
{{--        }--}}

{{--        .chart-title {--}}
{{--            text-align: center;--}}
{{--            margin-bottom: 20px;--}}
{{--        }--}}

{{--        @media (max-width: 576px) {--}}
{{--            .filters select {--}}
{{--                width: 100%;--}}
{{--                margin-bottom: 10px;--}}
{{--            }--}}
{{--        }--}}
{{--    </style>--}}
@endsection
