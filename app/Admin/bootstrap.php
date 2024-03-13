<?php

/**
 * Laravel-admin - admin builder based on Laravel.
 * @author z-song <https://github.com/z-song>
 *
 * Bootstraper for Admin.
 *
 * Here you can remove builtin form field:
 * Encore\Admin\Form::forget(['map', 'editor']);
 *
 * Or extend custom form field:
 * Encore\Admin\Form::extend('php', PHPEditor::class);
 *
 * Or require js and css assets:
 * Admin::css('/packages/prettydocs/css/styles.css');
 * Admin::js('/packages/prettydocs/js/main.js');
 *
 */


use App\Admin\Extensions\CustomButton;
use App\Admin\Extensions\CustomTable;
use App\Admin\Extensions\Links;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;

Encore\Admin\Form::forget(['map', 'editor']);
Admin::css('https://cdn.jsdelivr.net/npm/suggestions-jquery@20.3.0/dist/css/suggestions.min.css');
Admin::js('https://cdn.jsdelivr.net/npm/suggestions-jquery@20.3.0/dist/js/jquery.suggestions.min.js');
Grid::init(function (Grid $grid) {
    $grid->actions(function (Grid\Displayers\Actions $actions) {
        $actions->disableView();
    });
});

Encore\Admin\Grid\Column::define('actions', app\Admin\CustomAction\CustomActions::class);
app('view')->prependNamespace('admin', resource_path('views/admin'));
//Admin::js('/vendor/chart.js/chart.js');
Form::extend('tablecustom', CustomTable::class);
Admin::js('/vendor/dadata/ddata.js');
Admin::navbar(function (\Encore\Admin\Widgets\Navbar $navbar) {
    $navbar->right(new \App\Admin\Extensions\Links());
});
Admin::script('window.onbeforeunload = function() {
    return "Вы уверены, что хотите покинуть эту страницу? Ваши данные могут быть потеряны.";
};');
Admin::script("$(document).ready(function() {

      const submitButton = document.querySelector('.pull-right [type=\"submit\"]');
      const requiredInputs = document.querySelectorAll('input[required]');
      const requiredRadio = document.querySelectorAll('radio[required]');
      if(!submitButton || !requiredInputs || !requiredRadio){
        return;
      }
      if(submitButton){
         submitButton.disabled = true;
      }
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
          console.log(isValid,false);
          submitButton.disabled = false;
        } else {
            console.log(isValid,true);
          submitButton.disabled = true;
        }
      }

      requiredInputs.forEach(input => {
        input.addEventListener('input', checkInputs);
        input.addEventListener('click', checkInputs);
      });

    });");

Admin::script("$(document).ready(function() {
    var editingAreas = $('.note-editable');

    // Отслеживание изменений в input и select элементах формы
    $('form').find('input, select, radio').on('input change click', function() {
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

    // Функция для сохранения данных каждого инпута и селекта в cookies
    function saveDraft(inputName, inputValue) {
        var existingValue = getCookie(inputName);
        if (existingValue !== inputValue) {
            document.cookie = encodeURIComponent(inputName) + '=' + encodeURIComponent(inputValue);
        }
    }

    // Восстановление данных каждого инпута и селекта из cookies при загрузке страницы
    $('form').find('input:not([type=\"file\"]), select').each(function() {
        var inputName = $(this).attr('name');
        var savedValue = getCookie(inputName);
        if (savedValue) {
            $(this).val(savedValue); // Восстановление данных
        }
    });
    // Очистка данных черновика при успешной отправке формы
    $('form').submit(function() {
        clearDraft();
    });
     $('[type=submit]').on('click', function() {
       clearDraft();
    });
    // Функция для очистки данных черновика
    function clearDraft() {
        var cookies = document.cookie.split(';');
        for (var i = 0; i < cookies.length; i++) {
            var cookie = cookies[i];
            var eqPos = cookie.indexOf('=');
            var name = eqPos > -1 ? cookie.substr(0, eqPos) : cookie;
            document.cookie = name.trim() + '=;expires=Thu, 01 Jan 1970 00:00:00 GMT';
        }
    }

    // Вспомогательная функция для получения значения cookie по имени
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
});");


//Admin::script("document.addEventListener('DOMContentLoaded', function() {
//    var form = document.querySelector('form[action]');
//    var confirmExit = function(event) {
//        if (form && (form.offsetHeight > 0 || form.getClientRects().length > 0) && !form.checkValidity()) {
//            event.preventDefault(); // Отменяем переход по ссылке
//            var confirmResult = confirm('Уверены, что хотите выйти из формы, заполненные данные будут потеряны, продолжить?');
//            if (!confirmResult) {
//                event.preventDefault(); // Отменяем переход по ссылке
//            } else {
//                window.location.href = this.getAttribute('href'); // Переходим по ссылке
//            }
//        }
//    };
//
//    document.querySelectorAll('a[href]').forEach(function(link) {
//        link.addEventListener('click', confirmExit);
//    });
//});");
