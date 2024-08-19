<div class="tab-pane fade active show pt-3" id="tab-analytics">
@if(!$analytics)
    <div class="event-wrap">
        <h5>Вы еще не принимали участие в соревнованиях</h5>
    </div>
@else
    @foreach($analytics as $analytic)
            <div class="container">
                <h2>Аналитика</h2>

                <!-- Пример графика -->
                <canvas id="analyticsChart"></canvas>

                <!-- Таблица значений -->
                <table class="table table-bordered mt-4">
                    <thead>
                    <tr>
                        <th>Место</th>
                        <th>Количество трасс</th>
                        <th>Флеши</th>
                        <th>Участие</th>
                        <th>Скалодром</th>
                        <th>Полуфиналы</th>
                        <th>Финалы</th>
                        <th>Призовые места</th>
                        <th>Коэффициент стабильности</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($results as $result)
                        <tr>
                            <td>{{ $result->place }}</td>
                            <td>{{ $result->routes_count }}</td>
                            <td>{{ $result->flashes_count }}</td>
                            <td>{{ $result->participations_count }}</td>
                            <td>{{ $result->is_home_gym ? 'Домашний' : 'Чужой' }}</td>
                            <td>{{ $result->semifinals_count }}</td>
                            <td>{{ $result->finals_count }}</td>
                            <td>{{ $result->prize_places_count }}</td>
                            <td>{{ $result->stability_coefficient }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Подключаем Chart.js для графиков -->
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <script>
                var ctx = document.getElementById('analyticsChart').getContext('2d');
                var analyticsChart = new Chart(ctx, {
                    type: 'bar', // или другой тип графика
                    data: {
                        labels: ['Место', 'Трассы', 'Флеши', 'Участие', 'Полуфиналы', 'Финалы', 'Призы', 'Стабильность'],
                        datasets: [{
                            label: 'Статистика',
                            data: [
                                // Заполните данными из результатов
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
    @endforeach

@endif
</div>
