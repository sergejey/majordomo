/**
 * Select2 Russian translation.
 *
 * @author  Uriy Efremochkin <efremochkin@uriy.me>
 */
(function ($) {
    "use strict";

    $.fn.select2.locales['ru'] = {
        formatNoMatches: function () { return "Совпадений не найдено"; },
        formatInputTooShort: function (input, min) { return "Пожалуйста, введите еще хотя бы" + character(min - input.length); },
        formatInputTooLong: function (input, max) { return "Пожалуйста, введите на" + character(input.length - max) + " меньше"; },
        formatSelectionTooBig: function (limit) { return "Вы можете выбрать не более " + limit + " элемент" + (limit%10 == 1 && limit%100 != 11 ? "а" : "ов"); },
        formatLoadMore: function (pageNumber) { return "Загрузка данных…"; },
        formatSearching: function () { return "Поиск…"; }
    };
    
    $.fn.select2.locales['uk'] = {
        formatNoMatches: function () { return "Співпадінь не знайдено"; },
        formatInputTooShort: function (input, min) { return "Будь ласка введіть хочаб ще " + character(min - input.length); },
        formatInputTooLong: function (input, max) { return "Будь ласка, введіть на" + character(input.length - max) + " менше"; },
        formatSelectionTooBig: function (limit) { return "Ви можете обрати не більше " + limit + " елемент" + (limit%10 == 1 && limit%100 != 11 ? "а" : "ів"); },
        formatLoadMore: function (pageNumber) { return "Завантаження даних…"; },
        formatSearching: function () { return "Пошук…"; }
    };

    $.extend($.fn.select2.defaults, $.fn.select2.locales['ru'], $.fn.select2.locales['uk']);

    function character (n) {
        return " " + n + " символ" + (n%10 < 5 && n%10 > 0 && (n%100 < 5 || n%100 > 20) ? n%10 > 1 ? "a" : "" : "ов");
    }
})(jQuery);
