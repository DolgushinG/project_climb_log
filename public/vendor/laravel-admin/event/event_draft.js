$(document).ready(function() {
    const submitButton = document.querySelector('.pull-right [type=\"submit\"]');
    const requiredInputs = document.querySelectorAll('input[required]');
    const requiredRadio = document.querySelectorAll('radio[required]');
    if(!submitButton || !requiredInputs || !requiredRadio){
        return;
    }
    if(submitButton){
        submitButton.disabled = true;
    }
    requiredInputs.forEach(input => {
        input.addEventListener('input', checkInputs);
        input.addEventListener('click', checkInputs);
    });

});
function checkInputs() {
    let isValid = true;
    requiredInputs.forEach(input => {
        if (input.value.trim() === '') {
            isValid = false;
        }
    });
    requiredRadio.forEach(input => {
        if (input.value.trim() === '') {
            isValid = false;
        }
    });

    if (isValid) {
        submitButton.disabled = false;
    } else {
        submitButton.disabled = true;
    }
}
$(document).ready(function() {
    var editingAreas = $('.note-editable');

    editingAreas.each(function(index) {
        $(this).attr('data-id', 'editableArea_' + (index + 1));
    });

    // Отслеживание изменений в тексте каждой редактируемой области
    editingAreas.on('input', function() {
        var content = $(this).html();
        var areaId = $(this).attr('data-id');
        saveDraft(areaId, content);
    });

    // Восстановление данных редактируемой области из cookies при загрузке страницы
    editingAreas.each(function() {
        var areaId = $(this).attr('data-id');
        var savedContent = getCookie(areaId);
        if (savedContent) {
            $(this).html(savedContent); // Восстановление данных
        }
    });

    document.querySelectorAll('#is_france_system_qualification').forEach(input => {
        input.addEventListener('click', radio_button);
    });

    document.querySelectorAll('#is_semifinal').forEach(input => {
        input.addEventListener('click', radio_button);
    });
    document.querySelectorAll('#is_sort_group_final').forEach(input => {
        input.addEventListener('click', radio_button);
    });
    document.querySelectorAll('#mode').forEach(input => {
        input.addEventListener('click', radio_button);
    });

    restoreSwitch('active')
    restoreSwitch('is_input_birthday')
    restoreSwitch('is_need_sport_category')

    restoreRadioButtons('is_semifinal')
    restoreRadioButtons('is_france_system_qualification')
    restoreRadioButtons('is_sort_group_final')
    restoreRadioButtons('mode')

    var addButton = document.querySelector('.categories-add');
    // Добавляем обработчик события для отслеживания клика
    addButton.addEventListener('click', function() {
        $('.list-categories-table').find('input').on('input change', function() {
            let index = 0
            document.querySelectorAll('input[name=\"categories[values][]\"]').forEach(input => {
                var inputName = input.name + index;
                saveDraft2(inputName,input.value)
                index = index + 1
            });
        });
    });

    // Отслеживание изменений в input и select элементах формы
    $('form').find('input, select').on('input change click', function() {
        var inputName = $(this).attr('name');
        if(inputName === 'categories[values][]'){
            return;
        }
        var inputValue = $(this).val();
        saveDraft(inputName, inputValue);
    });
    $('form').find('textarea').on('input change', function() {
        var inputName = $(this).attr('name');
        var inputValue = $(this).val();
        saveDraft(inputName, inputValue);
    });
    $('#start_time').on('click', function() {
        var inputName = $(this).attr('name');
        var inputValue = $(this).val();
        saveDraft(inputName, inputValue);
    });
    // Отслеживание кликов по input элементам для выбора дат и других выборов
    $('#start_time').on('click', function() {
        var inputName = $(this).attr('name');
        var inputValue = $(this).val();
        saveDraft(inputName, inputValue);
    });
    $('#start_date').on('click', function() {
        var inputName = $(this).attr('name');
        var inputValue = $(this).val();
        saveDraft(inputName, inputValue);
    });
    $('#end_time').on('click', function() {
        var inputName = $(this).attr('name');
        var inputValue = $(this).val();
        saveDraft(inputName, inputValue);
    });
    $('#end_date').on('click', function() {
        var inputName = $(this).attr('name');
        var inputValue = $(this).val();
        saveDraft(inputName, inputValue);
    });
    $('form').find('input:not([type=\"file\"]), input:not([type=\"radio\"]), select, textarea').each(function() {
        var inputName = $(this).attr('name');
        if(inputName === 'is_france_system_qualification'){
            return;
        }
        if(inputName === 'is_semifinal'){
            return;
        }
        if(inputName === 'is_sort_group_final'){
            return;
        }
        if(inputName === 'mode'){
            return;
        }
        if(inputName === 'categories[values][]'){
            let table = document.querySelectorAll('.list-categories-table > tr');
            if(table){
                table.forEach(input => {
                    input.remove();
                });
            }
            readCookie()
            // Функция для удаления куки по имени
            var removeButtons = document.querySelectorAll('.categories-remove');
            removeButtons.forEach(function(button) {
                button.addEventListener('click', function() {
                    var dataId = button.getAttribute('data-id');
                    dataId = dataId.trim();
                    document.cookie = dataId + "=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
                });
            });
            return;
        }
        var savedValue = getCookie(inputName);
        if (savedValue) {
            $(this).val(savedValue); // Восстановление данных
        }
    });


});
    function radio_button() {
    var inputName = $(this).attr('id');
    var inputClass = $(this).attr('class');
    var existingValue = getCookie(inputName);
    if (existingValue !== inputClass) {
        saveDraft(inputName, inputClass);
    }
}
    function getElementByXpath(path) {
    return document.evaluate(path, document, null, XPathResult.FIRST_ORDERED_NODE_TYPE, null).singleNodeValue;
}
    function restoreSwitch(name){
    var value = getCookie(name);
    if(value === 'on'){
        getElementByXpath('//input[contains(@class, \"'+name+'\")]/..//span[contains(@class, \"bootstrap-switch-handle-off bootstrap-switch-default\")]').click()
    }
}
    function restoreRadioButtons (name) {
    var inputClass = getCookie(name)
    let radio0 = name+'0'
    let radio1 = name+'1'
    if(!inputClass){
        document.querySelector('.'+ radio1).click()
    } else {
        if(inputClass == radio1){
            document.querySelector('.'+ radio1).click()
        } else {
            document.querySelector('.'+ radio1).click()
            let r = document.querySelector('.'+ radio0)
            if(r){
                document.querySelector('.'+ radio0).click()
            }
        }
    }
}
    function saveDraft(inputName, inputValue) {
    var existingValue = getCookie(inputName);
    if (existingValue !== inputValue) {
        document.cookie = encodeURIComponent(inputName) + '=' + encodeURIComponent(inputValue);
    }
}
    function saveDraft2(inputName, inputValue) {
    var existingValue = getCookie(inputName);
    if (existingValue !== inputValue) {
        var expires = '';
        var date = new Date();
        date.setTime(date.getTime() + (10 * 24 * 60 * 60 * 1000));
        expires = '; expires=' + date.toUTCString();
        document.cookie = inputName.trim() + '=' + inputValue + expires + '; path=/';
    }
}
    function readCookie()
    {
        var allcookies = document.cookie;

        // Get all the cookies pairs in an array
        cookiearray  = allcookies.split(';');

        // Now take key value pair out of this array
        for(var i=0; i<cookiearray.length; i++) {
            name = cookiearray[i].split('=')[0];
            value = cookiearray[i].split('=')[1];
            if (name.startsWith("categories") || name.startsWith(" categories")) {
                addRowToTable(name.trim(), value)
            }
        }

    }
    function radio_button() {
    var inputName = $(this).attr('id');
    var inputClass = $(this).attr('class');
    var existingValue = getCookie(inputName);
    if (existingValue !== inputClass) {
        saveDraft(inputName, inputClass);
    }
};
    function addRowToTable(name, value) {
        var newRow = document.createElement('tr');
        var firstTd = document.createElement('td');
        var formGroupDiv = document.createElement('div');
        formGroupDiv.className = 'form-group';
        var colDiv = document.createElement('div');
        colDiv.className = 'col-sm-12';
        var inputElement = document.createElement('input');
        inputElement.setAttribute('name', name);
        inputElement.setAttribute('value', value);
        inputElement.className = 'form-control';
        colDiv.appendChild(inputElement);
        formGroupDiv.appendChild(colDiv);
        firstTd.appendChild(formGroupDiv);
        var secondTd = document.createElement('td');
        secondTd.setAttribute('style', 'width: 75px;');
        var removeButtonDiv = document.createElement('div');
        removeButtonDiv.setAttribute('data-id', name);
        removeButtonDiv.className = 'categories-remove btn btn-warning btn-sm pull-right';
        var trashIcon = document.createElement('i');
        trashIcon.className = 'fa fa-trash';
        trashIcon.innerHTML = '&nbsp;Удалить';
        removeButtonDiv.appendChild(trashIcon);
        secondTd.appendChild(removeButtonDiv);
        newRow.appendChild(firstTd);
        newRow.appendChild(secondTd);
        var tableBody = document.querySelector('.list-categories-table');
        tableBody.appendChild(newRow);
    }

    $('form').submit(function() {
        clearDraft();
    });
    $('[type=submit]').on('click', function() {
        clearDraft();
    });
    function clearDraft() {
        var cookies = document.cookie.split(';');
        for (var i = 0; i < cookies.length; i++) {
            var cookie = cookies[i];
            var eqPos = cookie.indexOf('=');
            var name = eqPos > -1 ? cookie.substr(0, eqPos) : cookie;
            document.cookie = name.trim() + '=;expires=Thu, 01 Jan 1970 00:00:00 GMT';
        }
    }
    function getCookie(name) {
        var match = document.cookie.match(new RegExp('(^| )' + encodeURIComponent(name) + '=([^;]+)'));
        if (match) {
            return decodeURIComponent(match[2]);
        }
        return null;
    }
    if(getCookie('title') !== null){
        document.getElementById('create-events-link').textContent = 'Черновик соревнования'
    }
