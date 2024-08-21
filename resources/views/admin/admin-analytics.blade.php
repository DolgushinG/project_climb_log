@extends('admin::content')

@section('content')
    <div class="box">
        <div class="box-body">
            <!-- Ваш HTML для таблицы -->
            <table class="table">
                <thead>
                <tr>
                    @foreach ($headers as $header)
                        <th>{{ $header }}</th>
                    @endforeach
                </tr>
                </thead>
                <tbody>
                @foreach ($stats as $stat)
                    <tr>
                        <td>{{ $stat['route_id'] }}</td>
                        <td>{{ $stat['grade'] }}</td>
                        <td>{{ $stat['flash'] }}</td>
                        <td>{{ $stat['redpoint'] }}</td>
                        <td>{{ $stat['all_passed'] }}</td>
                        <td>{{ $stat['coefficient'] }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            <!-- Контейнер для графиков -->
            <div>
                <canvas id="myChart" width="400" height="200"></canvas>
            </div>

            <!-- Скрипт для Chart.js -->
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    var ctx = document.getElementById('myChart').getContext('2d');
                    var chartData = @json($chartData);

                    var labels = chartData.map(function(stat) { return 'Route ' + stat.route_id; });
                    var flashData = chartData.map(function(stat) { return stat.flash; });
                    var redpointData = chartData.map(function(stat) { return stat.redpoint; });

                    var myChart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Flash Attempts',
                                data: flashData,
                                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                                borderColor: 'rgba(75, 192, 192, 1)',
                                borderWidth: 1
                            },
                                {
                                    label: 'Redpoint Attempts',
                                    data: redpointData,
                                    backgroundColor: 'rgba(153, 102, 255, 0.2)',
                                    borderColor: 'rgba(153, 102, 255, 1)',
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
                });
            </script>
        </div>
    </div>
@endsection
