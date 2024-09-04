document.addEventListener('DOMContentLoaded', function () {
    const priceElement = document.getElementById('price-value');
    const currentAmountStartPrice = document.getElementById('price-value');

    let basePrice = 0;
    if (currentAmountStartPrice) {
        const attCurrentAmountStartPrice = currentAmountStartPrice.getAttribute('data-current-amount-start-price');
        basePrice = attCurrentAmountStartPrice ? parseFloat(attCurrentAmountStartPrice) : 0;
    }

    let finalPrice = basePrice;

    const productsContainer = document.getElementById('products');
    const discountsContainer = document.getElementById('discounts');
    const helperContainer = document.getElementById('helper_amount');

    if (productsContainer || helperContainer || discountsContainer) {
        if (productsContainer) {
            productsContainer.addEventListener('change', updateFinalPrice);
        }
        if (helperContainer) {
            helperContainer.addEventListener('change', updateFinalPrice);
        }
        if (discountsContainer) {
            discountsContainer.addEventListener('change', updateFinalPrice);
        }
    }

    // Отображаем стартовую цену
    updatePrice(finalPrice);

    function updateFinalPrice() {
        finalPrice = basePrice;

        if (helperContainer) {
            const selectedHelperValue = parseFloat(helperContainer.value);
            if (!isNaN(selectedHelperValue) && selectedHelperValue > 0) {
                finalPrice -= (finalPrice - selectedHelperValue);
            }
        }

        if (discountsContainer) {
            const selectedDiscountValue = parseFloat(discountsContainer.value);
            if (!isNaN(selectedDiscountValue) && selectedDiscountValue > 0) {
                finalPrice -= finalPrice * (selectedDiscountValue / 100);
            }
        }

        if (productsContainer) {
            const productCheckboxes = productsContainer.querySelectorAll('input[type="checkbox"]');
            productCheckboxes.forEach(checkbox => {
                if (checkbox.checked) {
                    const productPrice = parseFloat(checkbox.getAttribute('data-price'));
                    if (!isNaN(productPrice)) {
                        finalPrice += productPrice;
                    }
                }
            });
        }

        updatePrice(finalPrice);
    }

    function updatePrice(price) {
        if (priceElement) {
            priceElement.textContent = price.toFixed(2);  // Округление до двух знаков после запятой
        }
    }

    // Вызываем обновление цены при загрузке
    updateFinalPrice();
});
