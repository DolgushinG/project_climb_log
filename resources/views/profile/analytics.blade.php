<div class="tab-pane fade active show pt-3" id="tab-analytics">
@if($analytics && $analytics_progress)
        <div class="container">

            <p>Высокий коэффициент стабильности (например, 4.17):</p>
            <p>Это означает, что участник показывает очень стабильные результаты, и его результаты значительно выше, чем у большинства других участников. Это свидетельствует о высоком уровне постоянства и надежности в его выступлениях.</p>
            <p>Низкий коэффициент стабильности (например, 1.29):</p>
            <p>Это указывает на то, что результаты участника имеют большую вариацию. Это может означать, что его результаты сильно отличаются от соревнования к соревнованию, и он может быть менее предсказуем в своих выступлениях.</p>
        </div>
        <div class="container">
            <table class="table table-bordered mt-4">
                <thead>
                <tr>
                    <th>Кол-во полуфиналов</th>
                    <th>Кол-во финалов</th>
                    <th>Коэффициент стабильности</th>
                    <th>Призовые места</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>{{ $analytics['semifinal_rate'] }}</td>
                    <td>{{ $analytics['final_rate'] }}</td>
                    <td>{{ $analytics['averageStability'] }}</td>
                    <td>{{ $analytics['totalPrizePlaces'] }}</td>
                </tr>
                </tbody>
            </table>
            <!-- Пример графика -->
            <canvas id="analyticsChart"></canvas>
        </div>

        <!-- Подключаем Chart.js для графиков -->
        <script>
            // Передаем данные из Blade в JavaScript
            var analyticsData = @json($analytics);

            // Инициализация графика
            var ctx = document.getElementById('analyticsChart').getContext('2d');
            var analyticsChart = new Chart(ctx, {
                type: 'bar', // Можно изменить на другой тип графика, если нужно
                data: {
                    labels: ['Полуфиналы', 'Финалы', 'Стабильность', 'Призы'],
                    datasets: [{
                        label: 'Статистика',
                        data: [
                            analyticsData.semifinal_rate,
                            analyticsData.final_rate,
                            analyticsData.averageStability,
                            analyticsData.totalPrizePlaces
                        ],
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        </script>
        <div class="container">
            <canvas id="progressChart"></canvas>
        </div>
        <script>
            // Передаем данные из Blade в JavaScript
            var analyticsData = @json($analytics_progress);

            // Инициализация графика
            var ctx = document.getElementById('progressChart').getContext('2d');
            var analyticsChart = new Chart(ctx, {
                type: 'line', // Используем линейный график
                data: {
                    labels: analyticsData.labels,
                    datasets: [{
                        label: 'Флеши',
                        data: analyticsData.flashes,
                        borderColor: 'rgba(75, 192, 192, 1)',
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        fill: false
                    }, {
                        label: 'Редпоинты',
                        data: analyticsData.redpoints,
                        borderColor: 'rgba(153, 102, 255, 1)',
                        backgroundColor: 'rgba(153, 102, 255, 0.2)',
                        fill: false
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        </script>
    @else
        <div class="event-wrap">
            <h5>Нет данных</h5>
        </div>
@endif
</div>
