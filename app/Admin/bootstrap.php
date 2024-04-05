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
//Admin::js('/plugins/jquery/jquery.min.js');
Encore\Admin\Form::forget(['map', 'editor']);
Admin::css('/plugins/jquery/suggestions.min.css');
Admin::js('/plugins/jquery/jquery.suggestions.min.js');
Admin::js('js/pages/js.cookie.js');
Grid::init(function (Grid $grid) {
    $grid->actions(function (Grid\Displayers\Actions $actions) {
        $actions->disableView();
    });
});

Encore\Admin\Grid\Column::define('actions', app\Admin\CustomAction\CustomActions::class);
app('view')->prependNamespace('admin', resource_path('views/admin'));
//Admin::js('/vendor/chart.js/chart.js');
Form::extend('tablecustom', CustomTable::class);
Form::extend('customlist', \App\Admin\Extensions\CustomList::class);
//Admin::js('/vendor/dadata/ddata.js');
Admin::navbar(function (\Encore\Admin\Widgets\Navbar $navbar) {
    $navbar->right(new \App\Admin\Extensions\Links());
});


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
