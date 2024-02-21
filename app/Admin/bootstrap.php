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

Grid::init(function (Grid $grid) {
    $grid->actions(function (Grid\Displayers\Actions $actions) {
        $actions->disableView();
    });
});
Encore\Admin\Grid\Column::define('actions', app\Admin\CustomAction\CustomActions::class);
app('view')->prependNamespace('admin', resource_path('views/admin'));
//Admin::js('/vendor/chart.js/chart.js');
Form::extend('tablecustom', CustomTable::class);

Admin::headerJs('/vendor/laravel-admin-ext/material-ui/MaterialAdminLTE/dist/js/material.min.js');
Admin::headerJs('/vendor/laravel-admin-ext/material-ui/MaterialAdminLTE/dist/js/ripples.min.js');
