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
            return parseInt(priceElement.textContent); // Стартовая цена до 31 августа
        } else if (today <= september30) {
            return parseInt(priceElement.textContent) + 500; // Цена до 30 сентября
        } else {
            return parseInt(priceElement.textContent) + 1000; // Цена после 30 сентября
        }
    }

    function updateFinalPrice() {
        finalPrice = basePrice;
        // Применяем выбранную скидку
        const selectedDiscount = parseFloat(discountsContainer.value)

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
    updateFinalPrice()
    function updatePrice(price) {
        priceElement.textContent = price.toFixed(2);
    }
});
