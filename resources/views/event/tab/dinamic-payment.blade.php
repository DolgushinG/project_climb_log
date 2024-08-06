
<div class="container mt-5">
    <p id="price" data-main-price="{{$event->amount_start_price}}" class="h3">Цена: <span id="price-value"></span> руб.</p>

    <div class="form-group">
        <label for="products">Выберите продукты:</label>
        <div id="products">
            @foreach($event->products as $index => $product)
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="{{ $product['Цена'] }}{{ $index }}" data-price="{{ $product['Цена'] }}">
                    <label class="form-check-label" for="{{ $product['Цена'] }}{{ $index }}">
                        {{ $product['Название'] }} ({{ $product['Цена'] }} руб.)
                    </label>
                </div>
            @endforeach
        </div>
    </div>

    <div class="form-group">
        <label for="discounts">Выберите скидку:</label>
        <div id="discounts">
            <div class="form-check">
                <input class="form-check-input" type="radio" name="discountGroup" id="Нет скидок" data-value="0">
                <label class="form-check-label" for="Нет скидок">
                    Нет скидок
                </label>
            </div>
            @foreach($event->discounts as $index => $discount)
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="discountGroup" id="{{ $discount['Название'] }}{{ $index }}" data-value="{{ $discount['Проценты'] }}">
                    <label class="form-check-label" for="{{ $discount['Название'] }}{{ $index }}">
                        {{ $discount['Название'] }} скидка ({{ $discount['Проценты'] }} %)
                    </label>
                </div>
            @endforeach
        </div>
    </div>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const priceElement = document.getElementById('price-value');
        const productsContainer = document.getElementById('products');
        const discountsContainer = document.getElementById('discounts');
        const data_main_price = document.querySelector('#price').getAttribute('data-main-price');

        let basePrice = calculateBasePrice();
        let finalPrice = basePrice;

        // Обработчики изменений
        productsContainer.addEventListener('change', updateFinalPrice);
        discountsContainer.addEventListener('change', updateFinalPrice);

        // Отображаем стартовую цену
        updatePrice(finalPrice);

        function calculateBasePrice() {
            const today = new Date();
            const august31 = new Date(today.getFullYear(), 7, 31); // 31 августа
            const september30 = new Date(today.getFullYear(), 8, 30); // 30 сентября

            if (today <= august31) {
                return parseInt(data_main_price); // Стартовая цена до 31 августа
            } else if (today <= september30) {
                return parseInt(data_main_price) + 500; // Цена до 30 сентября
            } else {
                return parseInt(data_main_price) + 1000; // Цена после 30 сентября
            }
        }

        function updateFinalPrice() {
            finalPrice = basePrice;
            // Применяем выбранную скидку
            const discountRadios = discountsContainer.querySelectorAll('input[type="radio"]');
            let selectedDiscount = 0;
            discountRadios.forEach(radio => {
                if (radio.checked) {
                    selectedDiscount = radio.getAttribute('data-value');
                }
            });

            // Применяем скидку к цене
            if (selectedDiscount > 0) {
                finalPrice = finalPrice - (finalPrice * (parseInt(selectedDiscount)/100));
            }
            // Добавляем стоимость выбранных продуктов
            const productCheckboxes = productsContainer.querySelectorAll('input[type="checkbox"]');
            productCheckboxes.forEach(checkbox => {
                if (checkbox.checked) {
                    finalPrice += parseFloat(checkbox.getAttribute('data-price'));
                }
            });

            updatePrice(finalPrice);
        }

        function updatePrice(price) {
            priceElement.textContent = price.toFixed(2);
        }
    });
</script>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

