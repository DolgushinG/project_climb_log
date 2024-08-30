document.addEventListener('DOMContentLoaded', function () {
    const priceElement = document.getElementById('price-value');
    const current_amount_start_price = document.getElementById('price-value').getAttribute('data-current-amount-start-price');
    const productsContainer = document.getElementById('products');
    const discountsContainer = document.getElementById('discounts');
    const helperContainer = document.getElementById('helper_amount');
    const data_main_price = document.querySelector('#price').getAttribute('data-main-price');

    let basePrice = parseFloat(current_amount_start_price);
    let finalPrice = basePrice;

    // Обработчики изменений
    productsContainer.addEventListener('change', updateFinalPrice);
    helperContainer.addEventListener('change', updateFinalPrice);
    discountsContainer.addEventListener('change', updateFinalPrice);

    // Отображаем стартовую цену
    updatePrice(finalPrice);

    function updateFinalPrice() {
        finalPrice = basePrice;
        // Применяем выбранную скидку
        const selectedDiscount = parseFloat(discountsContainer.value)
        const selectedHelperContainer = parseFloat(helperContainer.value)
        // Применяем скидку к цене
        if (selectedHelperContainer > 0) {
            finalPrice = finalPrice - (finalPrice - parseInt(selectedHelperContainer));
        }
        if (selectedDiscount > 0) {
            let t = finalPrice * (parseInt(selectedDiscount)/100);
            finalPrice = finalPrice - t;
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
        priceElement.textContent = price;
    }
});
