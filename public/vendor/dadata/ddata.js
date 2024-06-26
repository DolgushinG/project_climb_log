//mask phone

var token = "33c419ffb77324c6b14c23909f66ac321646a8cc";

var defaultFormatResult = $.Suggestions.prototype.formatResult;

function formatResult(value, currentValue, suggestion, options) {
    var newValue = suggestion.data.city;
    suggestion.value = newValue;

    return defaultFormatResult.call(this, newValue, currentValue, suggestion, options);
}

function formatSelected(suggestion) {
    return suggestion.data.city;
}
    //dadate
$("#city").suggestions({
    minChars: "2",
    noSuggestionsHint: "ADDRESS: 'Неизвестный адрес'",
    count: 5,
    token: token,
    type: "ADDRESS",
    hint: false,
    bounds: "city",
    addon: "none",
    constraints: {
        locations: { city_type_full: "город" }
    },
    formatResult: formatResult,
    formatSelected: formatSelected,
    /* Вызывается, когда пользователь выбирает одну из подсказок */
    onSelect: function(suggestion) {
    }
});
