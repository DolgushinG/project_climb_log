$(document).ready(function() {

    // Инициализируем календарь для поля ввода с идентификатором "birthday"
    $("#birthday").datepicker({
        dateFormat: 'yy-mm-dd',
        changeYear: true,
        changeMonth: true,
        yearRange: '1950:2024',
    });
});
