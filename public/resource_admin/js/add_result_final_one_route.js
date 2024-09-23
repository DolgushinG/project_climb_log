const elementsWithModalAttribute = document.querySelectorAll('[modal="app-admin-actions-resultroutefinalstage-batchresultfinalcustomfilloneroute"]');
const elementsWithIdAttribute = document.querySelectorAll('[id="app-admin-actions-resultroutefinalstage-batchresultfinalcustomfilloneroute"]');
const modalCounters= {};
const idCounters = {};

// Перебираем найденные элементы
elementsWithModalAttribute.forEach(element => {
    const modalValue = element.getAttribute('modal');
    if (modalValue in modalCounters) {
        modalCounters[modalValue]++;
    } else {
        modalCounters[modalValue] = 1;
    }
    const elementNumber = modalCounters[modalValue];
    element.setAttribute('modal', modalValue + '-' + elementNumber);
});
elementsWithIdAttribute.forEach(element => {
    const idValue = element.getAttribute('id');
    if (idValue in idCounters) {
        idCounters[idValue]++;
    } else {
        idCounters[idValue] = 1;
    }
    const elementNumber = idCounters[idValue];
    element.setAttribute('id', idValue + '-' + elementNumber);
});
