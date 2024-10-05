document.addEventListener('DOMContentLoaded', function () {
    let myChart = null;
    let allData = []; // Переменная для хранения всех данных
    function loadChart() {
        let gender = document.getElementById('genderSelect').value;
        let event_id = document.getElementById('myChart').getAttribute('data-id');
        let url = `/get-analytics`;

        $.ajax({
            url: url,
            type: 'GET',
            data: {
                gender: gender,
                event_id: event_id
            },
            success: function (data) {
                allData = data.routes; // Сохраняем все данные в глобальной переменной
                updateChart(); // Обновляем график с фильтрацией
                updateTable(); // Обновляем таблицу с данными
            },
            error: function (xhr, status, error) {
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
            type: 'bar', // Используем вертикальный график
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
                    },
                ]
            },
            options: {
                responsive: true,
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            autoSkip: true,
                            maxTicksLimit: 10 // Ограничиваем количество меток на оси X
                        },
                        title: {
                            display: true,
                            text: 'Трассы и их категории'
                        }
                    },
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Количество'
                        }
                    }
                }
            }
        });
    }
    function updateTable() {
        let selectedGrade = document.getElementById('gradeSelect').value;

        // Фильтрация данных по выбранному grade
        let filteredData = selectedGrade ? allData.filter(route => route.grade === selectedGrade) : allData;

        // Обновление таблицы
        const tableBody = document.getElementById('dataTableBody');
        tableBody.innerHTML = ''; // Очистить таблицу

        filteredData.forEach(route => {
            let row = document.createElement('tr');
            row.innerHTML = `
                <td>${route.route_id}</td>
                <td>${route.grade}</td>
                <td>${route.flash}</td>
                <td>${route.flash_percentage}</td>
                <td>${route.redpoint}</td>
                <td>${route.redpoint_percentage}</td>
                <td >${route.difficulty}</td>
                <td>${route.all_passed}</td>
                <td>${route.coefficient}</td>
            `;
            tableBody.appendChild(row);
        });
        // Если таблица уже инициализирована, уничтожаем её перед повторной инициализацией
        if ($.fn.DataTable.isDataTable('#dataTable')) {
            $('#dataTable').DataTable().destroy(); // Уничтожаем старую инстанцию DataTable
        }

        table = $('#dataTable').DataTable({
            "paging": false,
            "ordering": true,
            "info": false,
            "language": {
                "search": "Поиск:",
                "zeroRecords": "Ничего не найдено"
            },
            "orderMulti": true
        });
    }

    // Привязка события change к элементам select
    document.getElementById('gradeSelect').addEventListener('change', function () {
        updateChart();
        updateTable();
    });
    document.getElementById('genderSelect').addEventListener('change', loadChart);

    // Первоначальная загрузка графика и таблицы
    loadChart();
});
$(document).ready(function () {
    $(".search").keyup(function () {
        var searchTerm = $(".search").val();
        var listItem = $('.results tbody').children('tr');
        var searchSplit = searchTerm.replace(/ /g, "'):containsi('")

        $.extend($.expr[':'], {
            'containsi': function (elem, i, match, array) {
                return (elem.textContent || elem.innerText || '').toLowerCase().indexOf((match[3] || "").toLowerCase()) >= 0;
            }
        });

        $(".results tbody tr").not(":containsi('" + searchSplit + "')").each(function (e) {
            $(this).attr('visible', 'false');
        });

        $(".results tbody tr:containsi('" + searchSplit + "')").each(function (e) {
            $(this).attr('visible', 'true');
        });

        var jobCount = $('.results tbody tr[visible="true"]').length;
        $('.counter').text('Найдено сорев ' + jobCount);

        if (jobCount == '0') {
            $('.no-result').show();
        } else {
            $('.no-result').hide();
        }
    });
});
