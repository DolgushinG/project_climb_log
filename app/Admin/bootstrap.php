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
Form::extend('custom_button', CustomButton::class);
Admin::js('/vendor/dadata/ddata.js');
Admin::navbar(function (\Encore\Admin\Widgets\Navbar $navbar) {

    $navbar->left(view('admin.nav-bar.create-event'));

});
Admin::script('window.onbeforeunload = function() {
    return "Вы уверены, что хотите покинуть эту страницу? Ваши данные могут быть потеряны.";
};');

Admin::script("$(document).ready(function() {

      const submitButton = document.querySelector('.pull-right [type=\"submit\"]');
      const requiredInputs = document.querySelectorAll('input[required]');
      const requiredRadio = document.querySelectorAll('radio[required]');
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
    // Отслеживание изменений в input и select элементах формы
    $('form').find('input, select').on('input change', function() {
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
        document.cookie = encodeURIComponent(inputName) + '=' + encodeURIComponent(inputValue);
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

    // Функция для очистки данных черновика
    function clearDraft() {
        var cookies = document.cookie.split(';');
        for (var i = 0; i < cookies.length; i++) {
            var cookie = cookies[i];
            var eqPos = cookie.indexOf('=');
            var name = eqPos > -1 ? cookie.substr(0, eqPos) : cookie;
            document.cookie = name + '=;expires=Thu, 01 Jan 1970 00:00:00 GMT';
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
});");
