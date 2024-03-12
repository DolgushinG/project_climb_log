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
Admin::js('/vendor/dadata/ddata.js');
Admin::navbar(function (\Encore\Admin\Widgets\Navbar $navbar) {

    $navbar->left(view('admin.nav-bar.create-event'));

});
Admin::script('window.onbeforeunload = function() {
    return "Вы уверены, что хотите покинуть эту страницу? Ваши данные могут быть потеряны.";
};');
Admin::script("$(document).ready(function() {
    // Отслеживание изменений в input и select элементах формы
    $('form').find('input, select').on('input change', function() {
        var inputName = $(this).attr('name');
        var inputValue = $(this).val();
        saveDraft(inputName, inputValue);
    });
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

    // Функция для сохранения данных каждого инпута и селекта в localStorage
    function saveDraft(inputName, inputValue) {
        localStorage.setItem(inputName, inputValue);
    }

    // Восстановление данных каждого инпута и селекта из localStorage при загрузке страницы
    $('form').find('input:not([type=\"file\"]), select').each(function() {
        var inputName = $(this).attr('name');
        var savedValue = localStorage.getItem(inputName);
        if (savedValue !== null) {
            $(this).val(savedValue); // Восстановление данных
        }
    });

    $('form').submit(function() {
        clearDraft();
    });

    // Функция для очистки данных черновика
    function clearDraft() {
        localStorage.clear();
    }
});");
//Admin::headerJs('/vendor/laravel-admin-ext/material-ui/MaterialAdminLTE/dist/js/material.min.js');
//Admin::headerJs('/vendor/laravel-admin-ext/material-ui/MaterialAdminLTE/dist/js/ripples.min.js');
