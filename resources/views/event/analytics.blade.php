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
            <div class="accordion mb-2" id="accordionExample">
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingOne">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                            Как определяется оценка сложности трассы?
                        </button>
                    </h2>
                    <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
                        <div class="accordion-body">
                            <div class="container align-center">
                                <h2>Пороговые значения для оценки сложности</h2>
                                <p>Классифицируем сложность трассы в зависимости от процента флешей и редпоинтов:</p>
                                <div class="row">
                                    <div class="col">
                                        <table class="thresholds">
                                            <thead>
                                            <tr>
                                                <th>Уровень сложности</th>
                                                <th>Процент флешей</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr>
                                                <td>Слишком легкая</td>
                                                <td>90% и выше</td>
                                            </tr>
                                            <tr>
                                                <td>Легкая</td>
                                                <td>75% - 89%</td>
                                            </tr>
                                            <tr>
                                                <td>Сбалансированная</td>
                                                <td>50% - 74%</td>
                                            </tr>
                                            <tr>
                                                <td>Сложная</td>
                                                <td>25% - 49%</td>
                                            </tr>
                                            <tr>
                                                <td>Слишком сложная</td>
                                                <td>Менее 25%</td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="col mt-1">
                                        <table class="thresholds">
                                            <thead>
                                            <tr>
                                                <th>Уровень сложности</th>
                                                <th>Процент редпоинтов</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr>
                                                <td>Слишком легкая</td>
                                                <td>0%</td>
                                            </tr>
                                            <tr>
                                                <td>Легкая</td>
                                                <td>1% - 10%</td>
                                            </tr>
                                            <tr>
                                                <td>Сбалансированная</td>
                                                <td>11% - 20%</td>
                                            </tr>
                                            <tr>
                                                <td>Сложная</td>
                                                <td>21% - 30%</td>
                                            </tr>
                                            <tr>
                                                <td>Слишком сложная</td>
                                                <td>Более 30%</td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div><!-- End Default Accordion Example -->

            <div class="form-group">
                <label class="m-1" for="search"> Поиск тут </label>
                <input id="search" type="text" class="search form-control" placeholder="Что ищем?">
            </div>
            <table class="table table-striped mt-4 results" id="dataTable">
                <thead>
                <tr>
                    <th>Трасса</th>
                    <th>Категория</th>
                    <th>Флеши</th>
                    <th>% флешей</th>
                    <th>Редпоинты</th>
                    <th>% редпоинтов</th>
                    <th >Оценка сложности </th>
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
