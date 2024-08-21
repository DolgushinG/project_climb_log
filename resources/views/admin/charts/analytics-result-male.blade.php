<div>
    <canvas id="myChartMale" width="400" height="200"></canvas>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<!-- Скрипт для Chart.js -->
<script>

    $(function () {
        var ctx = document.getElementById('myChartMale').getContext('2d');
        var chartData = @json($chartData);

        var labels = chartData.map(function(stat) { return 'Трасса ' + stat.route_id; });
        var flashData = chartData.map(function(stat) { return stat.flash; });
        var redpointData = chartData.map(function(stat) { return stat.redpoint; });

        var myChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Флэши',
                    data: flashData,
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                },
                    {
                        label: 'Редпоинт',
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
