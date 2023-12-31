<?php

namespace App\Admin\Controllers;

use App\Models\Set;
use App\Http\Controllers\Controller;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class SetsController extends Controller
{
    use HasResourceActions;

    const DAYS = [ 'friday' => 'Пятница', 'saturday' => 'Суббота','sunday' => 'Воскресенье'];

    /**
     * Index interface.
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        return $content
            ->header(trans('admin.index'))
            ->description(trans('admin.description'))
            ->body($this->grid());
    }

    /**
     * Show interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function show($id, Content $content)
    {
        return $content
            ->header(trans('admin.detail'))
            ->description(trans('admin.description'))
            ->body($this->detail($id));
    }

    /**
     * Edit interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function edit($id, Content $content)
    {
        return $content
            ->header(trans('admin.edit'))
            ->description(trans('admin.description'))
            ->body($this->form()->edit($id));
    }

    /**
     * Create interface.
     *
     * @param Content $content
     * @return Content
     */
    public function create(Content $content)
    {
        return $content
            ->header(trans('admin.create'))
            ->description(trans('admin.description'))
            ->body($this->form());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Set);
        $grid->model()->orderBy('day_of_week', 'desc');
        if (!Admin::user()->isAdministrator()){
            $grid->model()->where('owner_id', '=', Admin::user()->id);
        } else {
            $grid->column('owner_id', 'Owner');
        }
//        $grid->id('ID');
        $grid->filter(function($filter){

            // Remove the default id filter
            $filter->disableIdFilter();

            // Add a column filter
            $filter->in('day_of_week', 'День слота')->checkbox(self::DAYS);

        });
        $grid->quickCreate(function (Grid\Tools\QuickCreate $create) {
            $admin_id = \Encore\Admin\Facades\Admin::user()->id;
            $create->integer('owner_id', $admin_id)->default($admin_id)->style('display', 'None');
            $create->text('time', 'Время слота')->placeholder('например 10:00 - 12:00');
            $create->integer('max_participants', 'Максимальное число участников')->placeholder('введи число');
            $create->select('day_of_week', 'День слота')->options(self::DAYS);
            $create->integer('number_set', 'Номер сета')->placeholder('введи число');;
        });
        $grid->column('time', 'Время слота')->editable();
        $grid->column('max_participants', 'Макс. число участников')->editable();
        $grid->column('day_of_week', 'День слота')->select(self::DAYS)->sortable();
        $grid->column('number_set', 'Номер сета')->editable();

//        $grid->created_at(trans('admin.created_at'));
//        $grid->updated_at(trans('admin.updated_at'));

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(Set::findOrFail($id));

        $show->id('ID');
        $show->owner_id('owner_id');
        $show->time('time');
        $show->max_participants('max_participants');
        $show->day_of_week('day_of_week');
        $show->number_set('number_set');
        $show->created_at(trans('admin.created_at'));
        $show->updated_at(trans('admin.updated_at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Set);

        $form->display('ID');
        $form->text('owner_id', 'owner_id');
        $form->text('time', 'time');
        $form->text('max_participants', 'max_participants');
        $form->text('day_of_week', 'day_of_week');
        $form->text('number_set', 'number_set');
        $form->display(trans('admin.created_at'));
        $form->display(trans('admin.updated_at'));

        return $form;
    }
}
