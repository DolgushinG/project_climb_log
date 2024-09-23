const elementsWithModalAttribute2 = document.querySelectorAll('[modal="app-admin-actions-resultroutesemifinalstage-batchresultsemifinalcustomfilloneroute"]');
const elementsWithIdAttribute2 = document.querySelectorAll('[id="app-admin-actions-resultroutesemifinalstage-batchresultsemifinalcustomfilloneroute"]');

// Создаем объект для отслеживания счетчика для каждого modal
const modalCounters2 = {};
const idCounters2 = {};

// Перебираем найденные элементы
elementsWithModalAttribute2.forEach(element => {
    const modalValue2 = element.getAttribute('modal');

    // Проверяем, существует ли уже счетчик для данного modal
    if (modalValue2 in modalCounters2) {
        // Если счетчик уже существует, инкрементируем его значение
        modalCounters2[modalValue2]++;
    } else {
        // Если счетчика еще нет, создаем его и устанавливаем значение 1
        modalCounters2[modalValue2] = 1;
    }

    // Получаем номер элемента для данного modal
    const elementNumber2 = modalCounters2[modalValue2];

    // Устанавливаем новое значение modal
    element.setAttribute('modal', modalValue2 + '-' + elementNumber2);
});
elementsWithIdAttribute2.forEach(element => {
    const idValue2 = element.getAttribute('id');

    // Проверяем, существует ли уже счетчик для данного modal
    if (idValue2 in idCounters2) {
        // Если счетчик уже существует, инкрементируем его значение
        idCounters2[idValue2]++;
    } else {
        // Если счетчика еще нет, создаем его и устанавливаем значение 1
        idCounters2[idValue2] = 1;
    }

    // Получаем номер элемента для данного modal
    const elementNumber2 = idCounters2[idValue2];

    // Устанавливаем новое значение modal
    element.setAttribute('id', idValue2 + '-' + elementNumber2);
});
