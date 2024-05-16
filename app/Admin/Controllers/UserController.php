<?php

namespace App\Admin\Controllers;

use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Hash;

class UserController extends AdminController
{
    /**
     * {@inheritdoc}
     */
    protected function title()
    {
        return trans('admin.administrator');
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $userModel = config('admin.database.users_model');

        $grid = new Grid(new $userModel());

        $grid->column('id', 'ID')->sortable();
        $grid->column('username', trans('admin.username'));
        $grid->column('name', trans('admin.name'));
        $grid->column('climbing_gym_name', trans('admin.climbing_gym_name'));
        $grid->column('climbing_gym_link', trans('admin.climbing_gym_link'));
        $grid->column('address', trans('admin.address'));
        $grid->column('phone', trans('admin.phone'));
        $grid->column('city', trans('admin.city'));
        $grid->column('roles', trans('admin.roles'))->pluck('name')->label();
        $states = [
            'on' => ['value' => 1, 'text' => 'Да', 'color' => 'success'],
            'off' => ['value' => 0, 'text' => 'Нет', 'color' => 'default'],
        ];

        $grid->column('is_access_to_create_event', 'Доступ к проведению')->switch($states);
        $grid->column('created_at', trans('admin.created_at'));
        $grid->column('updated_at', trans('admin.updated_at'));

        $grid->actions(function (Grid\Displayers\Actions $actions) {
            if ($actions->getKey() == 1) {
                $actions->disableDelete();
            }
        });

        $grid->tools(function (Grid\Tools $tools) {
            $tools->batch(function (Grid\Tools\BatchActions $actions) {
                $actions->disableDelete();
            });
        });

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     *
     * @return Show
     */
    protected function detail($id)
    {
        $userModel = config('admin.database.users_model');

        $show = new Show($userModel::findOrFail($id));

        $show->field('id', 'ID');
        $show->field('username', trans('admin.username'));
        $show->field('name', trans('admin.name'));
        $show->field('climbing_gym_name', trans('admin.climbing_gym_name'));
        $show->field('climbing_gym_link', trans('admin.climbing_gym_link'));
        $show->field('address', trans('admin.address'));
        $show->field('phone', trans('admin.phone'));
        $show->field('city', trans('admin.city'));
        $show->field('is_access_to_create_event', 'Доступ к проведению');
        $show->field('is_paid', 'Оплачено');
//        $show->field('bill', 'Чек по оплате');
        $show->field('roles', trans('admin.roles'))->as(function ($roles) {
            return $roles->pluck('name');
        })->label();
        $show->field('permissions', trans('admin.permissions'))->as(function ($permission) {
            return $permission->pluck('name');
        })->label();
        $show->field('created_at', trans('admin.created_at'));
        $show->field('updated_at', trans('admin.updated_at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    public function form()
    {
        $userModel = config('admin.database.users_model');
        $permissionModel = config('admin.database.permissions_model');
        $roleModel = config('admin.database.roles_model');

        $form = new Form(new $userModel());

        $userTable = config('admin.database.users_table');
        $connection = config('admin.database.connection');
        $form->display('id', 'ID');
        $form->text('username', trans('admin.username'))
            ->creationRules(['required', "unique:{$connection}.{$userTable}"])
            ->updateRules(['required', "unique:{$connection}.{$userTable},username,{{id}}"]);
        $form->text('name', trans('admin.name'))->rules('required');
        $form->text('phone', trans('admin.phone'))->rules('required');
        $form->text('climbing_gym_name', trans('admin.climbing_gym_name'));
        $form->text('climbing_gym_link', trans('admin.climbing_gym_link'));
        $form->text('address', trans('admin.address'));
        $form->text('city', trans('admin.city'));
        $form->image('avatar', trans('admin.avatar'));
        $form->switch('is_access_to_create_event', 'Доступ к проведению');
        $form->switch('is_paid', 'Оплачено');
//        $form->file('bill', 'Чек по оплате')->move('/bill/events/'.Admin::user()->id, $name);
        $form->password('password', trans('admin.password'))->rules('required|confirmed');
        $form->password('password_confirmation', trans('admin.password_confirmation'))->rules('required')
            ->default(function ($form) {
                return $form->model()->password;
            });

        $form->ignore(['password_confirmation']);

        $form->multipleSelect('roles', trans('admin.roles'))->options($roleModel::all()->pluck('name', 'id'));
        $form->multipleSelect('permissions', trans('admin.permissions'))->options($permissionModel::all()->pluck('name', 'id'));

        $form->display('created_at', trans('admin.created_at'));
        $form->display('updated_at', trans('admin.updated_at'));

        $form->saving(function (Form $form) {
            if ($form->password && $form->model()->password != $form->password) {
                $form->password = Hash::make($form->password);
            }
        });

        return $form;
    }
}
