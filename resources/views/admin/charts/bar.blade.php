<canvas id="myChart" width="400" height="400"></canvas>
{{--<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>--}}
<script>
    $(function () {
        var config = {
            type: 'line',
            labels: ['January', 'February', 'March', 'April'],
            datasets: [{
                label: 'My First Dataset',
                data: [65, 59, 80, 81, 56, 55, 40],
                fill: true,
                borderColor: 'rgb(75, 192, 192)',
                tension: 0.1
            }],
            options: {

                maintainAspectRatio: false
            }
        };

        var ctx = document.getElementById('myChart').getContext('2d');
        new Chart(ctx, config);
    });
</script>
