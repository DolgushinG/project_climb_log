$(document).ready(function() {
    $('.ie-trigger-column-sport_category').each(function() {
        // Если внутри span нет текста, делаем иконку видимой
        if ($(this).find('.ie-display').text().trim() === '') {
            $(this).find('i.fa-edit').css('visibility', 'visible');
        }
    });
});
