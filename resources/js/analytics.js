document.addEventListener('DOMContentLoaded', function() {
    let myChart = null;
    let allData = []; // Переменная для хранения всех данных

    function loadChart() {
        let gender = document.getElementById('genderSelect').value;
        let event_id = document.getElementById('myChart').getAttribute('data-id');
        let url = `/get_analytics`;

        $.ajax({
            url: url,
            type: 'GET',
            data: {
                gender: gender,
                event_id: event_id
            },
            success: function(data) {
                allData = data.routes; // Сохраняем все данные в глобальной переменной
                updateChart(); // Обновляем график с фильтрацией
            },
            error: function(xhr, status, error) {
                console.error("Ошибка: " + error);
            }
        });
    }

    function updateChart() {
        let selectedGrade = document.getElementById('gradeSelect').value;

        // Фильтрация данных по выбранному grade
        let filteredData = selectedGrade ? allData.filter(route => route.grade === selectedGrade) : allData;

        const ctx = document.getElementById('myChart').getContext('2d');

        // Если график уже существует, уничтожаем его перед созданием нового
        if (myChart) {
            myChart.destroy();
        }

        let labels = filteredData.map(route => `${route.grade} (Трасса : ${route.route_id})`);
        let flashData = filteredData.map(route => route.flash);
        let redpointData = filteredData.map(route => route.redpoint);

        myChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Флеши',
                        data: flashData,
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Редпоинты',
                        data: redpointData,
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    // Привязка события change к элементам select
    document.getElementById('gradeSelect').addEventListener('change', updateChart);
    document.getElementById('genderSelect').addEventListener('change', loadChart);

    // Первоначальная загрузка графика
    loadChart();
});
