@extends('layouts.main_page.app')
@section('content')
    <script src="{{asset('js/analytics.js')}}"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.1/css/jquery.dataTables.css">
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.js"></script>
    <section>
        <div class="container">
            <div class="row">
            </div>
        </div>
    </section>
    <section class="section">
        <div class="container align-center">
            <h2>Аналитика прохождений трасс</h2>

            <div class="row mb-3">
                <div class="col">
                    <label for="gradeSelect">Трасса</label>
                    <select id="gradeSelect" class="form-control">
                        <option value="">Все категории</option>
                        @foreach($grades as $route_id => $grade)
                            <option value="{{$grade}}">{{$grade}}</option>
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
            <div class="form-group">
                <label class="m-1" for="search"> Поиск тут </label>
                <input id="search" type="text" class="search form-control" placeholder="Что ищем?">
            </div>
            <!-- Добавляем таблицу данных -->
            <table class="table table-striped mt-4 results" id="dataTable">
                <thead>
                <tr>
                    <th>Трасса</th>
                    <th>Категория</th>
                    <th>Флеши</th>
                    <th>Редпоинты</th>
                    <th>Всего прохождений(М + Ж)</th>
                    <th>Коэффициент трассы</th>
                </tr>
                </thead>
                <tbody id="dataTableBody">
                <!-- Данные будут добавляться сюда -->
                </tbody>
            </table>
        </div>
    </section>
@endsection
