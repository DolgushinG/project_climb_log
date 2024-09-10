<div class="container text-center">
    <h2>Карта трасс для скалодрома</h2>
</div>
<style>
    /* Стили для модальных окон */
    #modal, #modal_show {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: #fff;
        padding: 20px;
        border: 1px solid #ddd;
        z-index: 1000;
        max-width: 90%;
        max-height: 90%;
        overflow-y: auto;
    }
</style>

<div id="map"
     style="width: 1200px;
            height: 800px;
            background-image: url({{$scheme_climbing_gym}});
            background-size: contain;
            position: relative;
            background-position: center;
            background-repeat: no-repeat">
    @foreach($points as $point)
        <div class="point"
             style="background-color: {{ $point->color }};
                    left: {{ $point->x }}px;
                    top: {{ $point->y }}px;
                    width: 40px;
                    height: 36px;
                    border-radius: 50%;
                    position: absolute;
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    justify-content: center;
                    color:  {{ $point->font_background[0] ?? 'white' }};
                    border: {{ $point->font_background[1] ?? ''}};
                    font-size: 10px;
                    text-align: center;"
             data-id="{{ $point->id }}"
             data-author="{{ $point->author }}"
             data-grade="{{ $point->grade }}"
             data-route_id="{{ $point->route_id }}">
            <span>{{ $point->route_id }}</span><br>
            <span>{{ $point->grade }}</span>
        </div>
    @endforeach
</div>

<!-- Модальное окно для создания/редактирования точки -->
<div id="modal" style="display:none;">
    <form id="point-form">
        <input type="hidden" id="point-id" name="id">
        <label for="author">Автор:</label>
        <input class="form-control" type="text" id="author" name="author"><br>
        <label for="route_id">Маршрут:</label>
        <select id="route_id" name="route_id" class="form-select" autocomplete="off">
            @foreach($routes as $route)
                @if(in_array($route->route_id, $points_exist))
                    <option data-grade="{{$route->grade}}" value="{{$route->route_id}}" style="display: none">{{$route->route_id}}</option>
                @else
                    <option data-grade="{{$route->grade}}" value="{{$route->route_id}}">{{$route->route_id}}</option>
                @endif
            @endforeach
        </select><br>
        <label for="color">Цвет:</label>
        <input class="form-control" type="color" id="color" name="color" required><br>
        <input type="hidden" id="x-coordinate" name="x">
        <input type="hidden" id="y-coordinate" name="y">
        <input type="hidden" id="event_id" name="event_id">
        <input type="hidden" id="owner_id" name="owner_id">
        <button class="btn btn-secondary m-1" id="btn_save" type="submit">Сохранить</button>
        <button class="btn btn-danger m-1" id="btn_delete" type="button">Удалить</button>
        <button class="btn btn-secondary m-1" id="btn_close" type="button">Закрыть</button>
    </form>
</div>

<!-- Модальное окно для просмотра точки -->
<div id="modal_show" style="display:none;">
    <input type="hidden" id="point-id_show" name="id">
    <label for="author">Автор:</label>
    <input class="form-control" type="text" id="author_show" name="author" disabled><br>
    <label for="route_id">Маршрут:</label>
    <input class="form-control" type="text" id="route_id_show" name="route_id" disabled><br>
    <label for="grade_show">Категория:</label>
    <input class="form-control" type="text" id="grade_show" name="grade_show" disabled><br>
    <label for="color">Цвет:</label>
    <input class="form-control" type="color" id="color_show" name="color" disabled><br>
    <input type="hidden" id="x-coordinate_show" name="x">
    <input type="hidden" id="y-coordinate_show" name="y">
    <button class="btn btn-secondary" id="btn_close_show" type="button">Закрыть</button>
</div>

<script>
    let isNewPoint = false;

    // Открытие модального окна для новой или существующей точки
    function openModal(isNew, point = {}) {
        const modal = document.getElementById('modal');
        isNewPoint = isNew;

        if (isNew) {
            document.getElementById('point-id').value = '';
            document.getElementById('author').value = '';
            document.getElementById('color').value = '#000000';
            document.getElementById('x-coordinate').value = point.x;
            document.getElementById('y-coordinate').value = point.y;
            document.getElementById('route_id').value = '';
            document.getElementById('btn_delete').style.display = 'none';
        } else {
            document.getElementById('point-id').value = point.id;
            document.getElementById('author').value = point.author;
            let rgbArray = point.color.match(/\d+/g).map(Number);
            document.getElementById('color').value = rgbToHex(rgbArray[0], rgbArray[1], rgbArray[2]);
            document.getElementById('x-coordinate').value = point.x;
            document.getElementById('y-coordinate').value = point.y;
            document.getElementById('route_id').value = point.route_id;
            document.getElementById('btn_delete').style.display = 'inline-block';
        }

        modal.style.display = 'block';
    }
    function rgbToHex(r, g, b) {
        return "#" + ((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1).toUpperCase();
    }
    // Закрытие любого модального окна
    function closeModal(modalId) {
        document.getElementById(modalId).style.display = 'none';
    }

    // Создание новой точки
    document.getElementById('map').addEventListener('click', function(e) {
        if (!e.target.classList.contains('point')) {
            const rect = this.getBoundingClientRect();
            const x = e.clientX - rect.left - 30;
            const y = e.clientY - rect.top - 30;

            openModal(true, { x, y });
        }
    });

    // Открытие модального окна для редактирования/удаления точки
    document.getElementById('map').addEventListener('click', function(e) {
        if (e.target.classList.contains('point') || e.target.closest('.point')) {
            const point = e.target.closest('.point');
            const pointData = {
                id: point.dataset.id,
                author: point.dataset.author,
                color: point.style.backgroundColor,
                route_id: point.dataset.route_id,
                grade: point.dataset.grade,
                x: parseInt(point.style.left),
                y: parseInt(point.style.top)
            };
            openModal(false, pointData);
        }
    });

    // Обработка сохранения точки
    document.getElementById('point-form').addEventListener('submit', function(e) {
        e.preventDefault();

        let formData = new FormData(this);
        let routeSelect = document.getElementById('route_id');
        let event_id = document.getElementById('event_id');
        let owner_id = document.getElementById('owner_id');
        let selectedOption = routeSelect.options[routeSelect.selectedIndex];
        let grade = selectedOption.getAttribute('data-grade');

        formData.set('grade', grade);
        formData.set('event_id', event_id);
        formData.set('owner_id', owner_id);
        let url = isNewPoint ? '/admin/map' : `/admin/map/${document.getElementById('point-id').value}`;
        let method = isNewPoint ? 'POST' : 'POST';

        fetch(url, {
            method: method,
            body: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-HTTP-Method-Override': isNewPoint ? 'POST' : 'PUT'
            }
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    let button = $('#point-form')
                    button.text('').append('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Обработка...')
                    setTimeout(function () {
                        button.text(data.message)
                    }, 1500);
                    setTimeout(function () {
                        window.location.reload();
                    }, 3000);
                }
            });
    });

    // Создание нового элемента точки на карте
    function createPointElement(point, formData) {
        const newPoint = document.createElement('div');
        setupPointElement(newPoint, point, formData);
        document.getElementById('map').appendChild(newPoint);
    }

    // Обновление существующей точки
    function updatePointElement(point, formData) {
        const existingPoint = document.querySelector(`.point[data-id='${point.id}']`);
        setupPointElement(existingPoint, point, formData);
    }

    // Настройка элемента точки
    function setupPointElement(element, point, formData) {
        element.classList.add('point');
        element.style.backgroundColor = formData.get('color');
        element.style.left = `${formData.get('x')}px`;
        element.style.top = `${formData.get('y')}px`;
        element.style.width = '40px';
        element.style.height = '36px';
        element.style.borderRadius = '50%';
        element.style.position = 'absolute';
        element.style.display = 'flex';
        element.style.flexDirection = 'column';
        element.style.alignItems = 'center';
        element.style.justifyContent = 'center';
        element.dataset.color = element.dataset.font_background[0];
        element.style.fontSize = '10px';
        element.style.textAlign = 'center';
        element.dataset.id = point.id;
        element.dataset.author = formData.get('author');
        element.dataset.route_id = formData.get('route_id');
        element.innerHTML = `<span>${formData.get('route_id')}</span><br><span>${formData.get('grade')}</span>`;
    }

    // Обработка удаления точки
    document.getElementById('btn_delete').addEventListener('click', function() {
        const pointId = document.getElementById('point-id').value;
        if (confirm('Вы уверены, что хотите удалить эту точку?')) {
            fetch(`/admin/map/${pointId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        let button = $('#point-form')
                        button.text('').append('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Обработка...')
                        setTimeout(function () {
                            button.text(data.message)
                        }, 1500);
                        setTimeout(function () {
                            window.location.reload();
                        }, 3000);
                    }
                });
        }
    });

    // Закрытие модальных окон
    document.getElementById('btn_close').addEventListener('click', function() {
        closeModal('modal');
    });
    document.getElementById('btn_close_show').addEventListener('click', function() {
        closeModal('modal_show');
    });
    document.addEventListener('DOMContentLoaded', function () {
        @if(session('success'))
        let toastSuccess = new bootstrap.Toast(document.getElementById('toastSuccess'));
        toastSuccess.show();
        @endif

        @if(session('error'))
        let toastError = new bootstrap.Toast(document.getElementById('toastError'));
        toastError.show();
        @endif
    });

</script>
