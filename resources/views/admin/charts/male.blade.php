<canvas id="doughnut-male" width="200" height="200"></canvas>
<script>
    $(function () {

        const data_set = {!! json_encode($all_group['male']) !!};
        const categories = {!! json_encode($categories_array) !!};
        let randomBackgroundColor = [];
        let usedColors = new Set();

        let dynamicColors = function() {
            let r = Math.floor(Math.random() * 255);
            let g = Math.floor(Math.random() * 255);
            let b = Math.floor(Math.random() * 255);
            let color = "rgb(" + r + "," + g + "," + b + ")";

            if (!usedColors.has(color)) {
                usedColors.add(color);
                return color;
            } else {
                return dynamicColors();
            }
        };

        for (let i in categories) {
            randomBackgroundColor.push(dynamicColors());
        }

        var config = {
            type: 'doughnut',
            options: {
                title: {
                    display: true,
                    text: 'Мужчины'
                },
                maintainAspectRatio: false
            },
            data: {
                datasets: [{
                    data: data_set,
                    backgroundColor: randomBackgroundColor
                }],
                labels: categories,
            },
        };

        var ctx = document.getElementById('doughnut-male').getContext('2d');
        new Chart(ctx, config);
    });
</script>
