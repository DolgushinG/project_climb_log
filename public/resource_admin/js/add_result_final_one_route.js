const elementsWithModalAttribute = document.querySelectorAll('[modal="app-admin-actions-resultroutefinalstage-batchresultfinalcustomfilloneroute"]');
const elementsWithIdAttribute = document.querySelectorAll('[id="app-admin-actions-resultroutefinalstage-batchresultfinalcustomfilloneroute"]');

// Создаем объект для отслеживания счетчика для каждого modal
const modalCounters= {};
const idCounters = {};

// Перебираем найденные элементы
elementsWithModalAttribute.forEach(element => {
    const modalValue = element.getAttribute('modal');

    // Проверяем, существует ли уже счетчик для данного modal
    if (modalValue in modalCounters) {
        // Если счетчик уже существует, инкрементируем его значение
        modalCounters[modalValue]++;
    } else {
        // Если счетчика еще нет, создаем его и устанавливаем значение 1
        modalCounters[modalValue] = 1;
    }

    // Получаем номер элемента для данного modal
    const elementNumber = modalCounters[modalValue];

    // Устанавливаем новое значение modal
    element.setAttribute('modal', modalValue + '-' + elementNumber);
});
elementsWithIdAttribute.forEach(element => {
    const idValue = element.getAttribute('id');

    // Проверяем, существует ли уже счетчик для данного modal
    if (idValue in idCounters) {
        // Если счетчик уже существует, инкрементируем его значение
        idCounters[idValue]++;
    } else {
        // Если счетчика еще нет, создаем его и устанавливаем значение 1
        idCounters[idValue] = 1;
    }

    // Получаем номер элемента для данного modal
    const elementNumber = idCounters[idValue];

    // Устанавливаем новое значение modal
    element.setAttribute('id', idValue + '-' + elementNumber);
});
