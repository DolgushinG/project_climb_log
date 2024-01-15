<?php

namespace App\Admin\Controllers;

use App\Models\ResultFinalStage;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class  ResultFinalStageController extends Controller
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
        $grid = new Grid(new ResultFinalStage);

        $grid->id('ID');
        $grid->owner_id('owner_id');
        $grid->event_id('event_id');
        $grid->user_id('user_id');
        $grid->sum_amount_top('sum_amount_top');
        $grid->sum_amount_try_top('sum_amount_try_top');
        $grid->sum_amount_zone('sum_amount_zone');
        $grid->sum_amount_try_zone('sum_amount_try_zone');
        $grid->place('place');
        $grid->created_at(trans('admin.created_at'));
        $grid->updated_at(trans('admin.updated_at'));

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
        $show = new Show(ResultFinalStage::findOrFail($id));

        $show->id('ID');
        $show->owner_id('owner_id');
        $show->event_id('event_id');
        $show->user_id('user_id');
        $show->sum_amount_top('sum_amount_top');
        $show->sum_amount_try_top('sum_amount_try_top');
        $show->sum_amount_zone('sum_amount_zone');
        $show->sum_amount_try_zone('sum_amount_try_zone');
        $show->place('place');
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
        $form = new Form(new ResultFinalStage);

        $form->display('ID');
        $form->text('owner_id', 'owner_id');
        $form->text('event_id', 'event_id');
        $form->text('user_id', 'user_id');
        $form->text('sum_amount_top', 'sum_amount_top');
        $form->text('sum_amount_try_top', 'sum_amount_try_top');
        $form->text('sum_amount_zone', 'sum_amount_zone');
        $form->text('sum_amount_try_zone', 'sum_amount_try_zone');
        $form->text('place', 'place');
        $form->display(trans('admin.created_at'));
        $form->display(trans('admin.updated_at'));

        return $form;
    }
}
