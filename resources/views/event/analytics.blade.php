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
                        <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                            Как определяется оценка сложности трассы?
                        </button>
                    </h2>
                    <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne"
                         data-bs-parent="#accordionExample">
                        <div class="accordion-body">
                            <div class="container align-center" style="font-size: 0.9em;">
                                <h2>Как определяется оценка сложности трассы?</h2>
                                <p>
                                    Сложность трассы оценивается на основе соотношения между процентами флешей и редпоинтов.
                                    Мы учитываем разницу между этими показателями, чтобы определить,
                                    насколько трасса легкая, сложная или сбалансированная.
                                </p>
                                <div class="row">
                                    <div class="col">
                                        <table class="thresholds">
                                            <thead>
                                            <tr>
                                                <th>Уровень сложности</th>
                                                <th>Соотношение флешей и редпоинтов</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr>
                                                <td>Слишком легкая</td>
                                                <td>Процент флешей превышает процент редпоинтов на 30% и более</td>
                                            </tr>
                                            <tr>
                                                <td>Легкая</td>
                                                <td>Процент флешей превышает процент редпоинтов на 10%-30%</td>
                                            </tr>
                                            <tr>
                                                <td>Сбалансированная</td>
                                                <td>Разница между процентом флешей и редпоинтов не превышает 10%
                                                    в любую сторону</td>
                                            </tr>
                                            <tr>
                                                <td>Сложная</td>
                                                <td>Процент редпоинтов превышает процент флешей на 10%-30%</td>
                                            </tr>
                                            <tr>
                                                <td>Слишком сложная</td>
                                                <td>Процент редпоинтов превышает процент флешей на 30% и более</td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="col mt-1">
                                        <p style="line-height: 1.5;">
                                            Пример: Если на трассе 70% флешей и 50% редпоинтов, разница составляет 20%,
                                            и трасса будет классифицирована как легкая. <br>
                                            <br>
                                            Если на трассе 40% флешей и 50% редпоинтов, разница составляет -10%,
                                            и трасса будет классифицирована как сбалансированная. <br>
                                            <br>
                                            Если на трассе 20% флешей и 60% редпоинтов, разница составляет -40%,
                                            и трасса будет классифицирована как слишком сложная.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div><!-- End Default Accordion Example -->


            <div class="container">
                <p>На компьютере Shift для многократной сортировки.</p>
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
            <!-- Элементы для мобильной сортировки -->

        </div>
    </section>
@endsection
