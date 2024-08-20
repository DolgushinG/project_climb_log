@extends('layouts.main_page.app')
@section('content')
    <script src="{{asset('js/analytics.js')}}"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <section class="section-bg contact">
        <div class="container mt-3">
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

            <div class="chart-container">
                <canvas id="myChart" data-id="{{$event->id}}"></canvas>
            </div>

            <!-- Добавляем таблицу данных -->
            <table class="table table-striped mt-4">
                <thead>
                <tr>
                    <th>Трасса</th>
                    <th>Степень</th>
                    <th>Флеши</th>
                    <th>Редпоинты</th>
                </tr>
                </thead>
                <tbody id="dataTableBody">
                <!-- Данные будут добавляться сюда -->
                </tbody>
            </table>
        </div>
    </section>
@endsection
