<div id="map" style="width: 800px; height: 600px; background-image: url('/storage/images/map.png'); background-size: cover; position: relative; border: 1px solid #ddd;">
    @foreach($points as $point)
        <div class="point"
             style="background-color: {{ $point->color }};
                    left: {{ $point->x }}px;
                    top: {{ $point->y }}px;
                    width: 50px;
                    height: 50px;
                    border-radius: 50%;
                    position: absolute;
                    display: flex;
                    flex-direction: column; /* Вертикальное расположение элементов */
                    align-items: center;
                    justify-content: center;
                    color: white;
                    font-size: 12px;
                    text-align: center;"
             data-id="{{ $point->id }}"
             data-author="{{ $point->author }}"
             data-grade="{{ $point->grade }}"
             data-route_id="{{ $point->route_id }}">
            <span>{{ $point->route_id }}</span><br>
            <br><span>{{ $point->grade }}</span>
        </div>
    @endforeach
</div>

<!-- Модальное окно для создания/редактирования точки -->
<div id="modal" style="display:none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: #fff; padding: 20px; border: 1px solid #ddd;">
    <form id="point-form">
        <input type="hidden" id="point-id" name="id">
        <label for="author">Author:</label>
        <input type="text" id="author" name="author" required><br>
        <label for="route_id"></label>
        <select id="route_id" class="form-select">
        @foreach($routes as $route)
            <option value="{{$route->route_id}}">{{$route->route_id}}</option>
        @endforeach
        </select>
        <label for="grade">Grade:</label>
        <input type="text" id="grade" name="grade" required><br>
        <label for="route_id">Route:</label>
        <input type="text" id="route_id" name="route_id" required><br>
        <label for="color">Color:</label>
        <input type="color" id="color" name="color" required><br>
        <input type="hidden" id="x-coordinate" name="x">
        <input type="hidden" id="y-coordinate" name="y">
        <button type="submit">Save</button>
        <button id="btn_close">Close</button>
    </form>
</div>

<script>
    let isNewPoint = false;

    // Обработчик клика на карту для создания новой точки
    document.getElementById('map').addEventListener('click', function(e) {
        // Проверяем, что клик был не по существующей точке
        if (!e.target.classList.contains('point')) {
            isNewPoint = true;
            const rect = this.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;

            // Открываем модальное окно для ввода данных новой точки
            document.getElementById('point-id').value = '';
            document.getElementById('author').value = '';
            document.getElementById('grade').value = '';
            document.getElementById('color').value = '#000000';
            document.getElementById('x-coordinate').value = x;
            document.getElementById('y-coordinate').value = y;
            document.getElementById('modal').style.display = 'block';
        }
    });

    // Обработчик клика на точку для редактирования
    document.getElementById('map').addEventListener('click', function(e) {
        if (e.target.classList.contains('point')) {
            isNewPoint = false;
            const point = e.target;
            document.getElementById('point-id').value = point.dataset.id;
            document.getElementById('author').value = point.dataset.author;
            document.getElementById('grade').value = point.dataset.grade;
            document.getElementById('color').value = point.style.backgroundColor;
            document.getElementById('x-coordinate').value = parseInt(point.style.left);
            document.getElementById('y-coordinate').value = parseInt(point.style.top);
            document.getElementById('modal').style.display = 'block';
        }
    });

    // Обработка формы сохранения точки (создание или обновление)
    document.getElementById('point-form').addEventListener('submit', function(e) {
        e.preventDefault();

        let formData = new FormData(this);
        let url = isNewPoint ? '/admin/map' : `/admin/map/${document.getElementById('point-id').value}`;
        let method = isNewPoint ? 'POST' : 'POST'; // Для Laravel используется 'POST' с 'X-HTTP-Method-Override'

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
                    if (isNewPoint) {
                        // Создание новой точки на карте
                        const newPoint = document.createElement('div');
                        newPoint.classList.add('point');
                        newPoint.style.backgroundColor = formData.get('color');
                        newPoint.style.left = `${formData.get('x')}px`;
                        newPoint.style.top = `${formData.get('y')}px`;
                        newPoint.style.width = '50px';
                        newPoint.style.height = '50px';
                        newPoint.style.borderRadius = '50%';
                        newPoint.style.position = 'absolute';
                        newPoint.style.display = 'flex';
                        newPoint.style.flexDirection = 'column';
                        newPoint.style.alignItems = 'center';
                        newPoint.style.justifyContent = 'center';
                        newPoint.style.color = 'white';
                        newPoint.style.fontSize = '12px';
                        newPoint.style.textAlign = 'center';
                        newPoint.dataset.id = data.point.id;
                        newPoint.dataset.author = formData.get('author');
                        newPoint.dataset.grade = formData.get('grade');
                        newPoint.dataset.route_id = formData.get('route_id');

                        newPoint.innerHTML = `<span>${formData.get('route_id')}</span><span>${formData.get('grade')}</span>`;
                        document.getElementById('map').appendChild(newPoint);
                    } else {
                        // Обновление существующей точки
                        const point = document.querySelector(`.point[data-id='${data.point.id}']`);
                        point.style.backgroundColor = formData.get('color');
                        point.style.left = `${formData.get('x')}px`;
                        point.style.top = `${formData.get('y')}px`;
                        point.dataset.author = formData.get('author');
                        point.dataset.grade = formData.get('grade');
                        point.dataset.route_id = formData.get('route_id');
                        point.innerHTML = `<span>${formData.get('route_id')}</span><span>${formData.get('grade')}</span>`;
                    }

                    document.getElementById('modal').style.display = 'none';
                }
            });
    });
    let btn_close = document.getElementById('btn_close')
    if(btn_close){
        btn_close.addEventListener('click', function(event) {
            const modal = document.getElementById('modal');
            modal.style.display = 'none';
        });
    }
</script>
