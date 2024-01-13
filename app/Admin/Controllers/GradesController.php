<?php

namespace App\Admin\Controllers;

use App\Models\Grades;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Show;

class GradesController extends Controller
{
    use HasResourceActions;

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
            ->row(function(Row $row) {
                $row->column(5, $this->grid());
            });
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
        $grid = new Grid(new Grades);
        if (!Admin::user()->isAdministrator()){
            $grid->model()->where('owner_id', '=', Admin::user()->id);
        } else {
            $grid->column('owner_id', 'Владелец скалодрома')->filter();
        }
        $grid->column('grade', 'Ценность категории')->editable();
        $grid->column('value', 'Ценность категории')->sortable()->editable();
        $grid->column('amount', 'Ценность категории')->editable();

        return $grid;
    }

//    /**
//     * Make a show builder.
//     *
//     * @param mixed $id
//     * @return Show
//     */
//    protected function detail($id)
//    {
//        $show = new Show(Grades::findOrFail($id));
//
////        $show->id('ID');
//        $show->field('value', 'Ценность категории');
////        $show->created_at(trans('admin.created_at'));
////        $show->updated_at(trans('admin.updated_at'));
//
//        return $show;
//    }

//    /**
//     * Make a form builder.
//     *
//     * @return Form
//     */
//    protected function form()
//    {
//        $form = new Form(new Grades);
//
////        $form->display('ID');
//        $form->hidden('owner_id')->value(Admin::user()->id);
//        $form->text('grade', 'Категория трассы');
//        $form->text('amount', 'Кол-во данной категории');
//        $form->text('value', 'Ценность категории');
////        $form->display(trans('admin.created_at'));
////        $form->display(trans('admin.updated_at'));
//
//        return $form;
//    }
}
