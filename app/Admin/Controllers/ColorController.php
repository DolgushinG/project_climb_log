<?php

namespace App\Admin\Controllers;

use App\Models\Color;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Grades;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Show;

class ColorController extends Controller
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
            ->row(function(Row $row) {
                $event = Event::where('owner_id', '=', Admin::user()->id)->where('active', 1)->first();
                if($event){
                    if(!$event->is_france_system_qualification){
                        $row->column(10, function (Column $column) use ($event) {
                            $column->row($this->grid());
                        });
                    }
                }
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
        $grid = new Grid(new Color);
        if (!Admin::user()->isAdministrator()){
            $grid->model()->where('owner_id', '=', Admin::user()->id);
        }
//        $grid->model()->where(function ($query) {
//            $query->has('event.colors');
//        });
        $grid->actions(function ($actions) {
            if(Admin::user()->is_delete_result == 0){
                $actions->disableDelete();
                $actions->disableBatchActions();
            }
//            $actions->disableEdit();
            $actions->disableView();
        });
//        $grid->disableCreateButton();
        $grid->disableColumnSelector();
        $grid->disableExport();
        $grid->disableFilter();
        $grid->disablePagination();
        $grid->hideColumns(['event_id', 'owner_id']);
        $grid->column('color', __('Цвет в представлении'))->display(function ($color) {
            return "<div style='width: 50px; height: 20px; background-color: {$color}; border: 1px solid #ddd;'></div>";
        });
        $grid->column('color_name', 'Название')->sortable();

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
        $show = new Show(Color::findOrFail($id));

        $show->id('ID');
        $show->event_id('event_id');
        $show->owner_id('owner_id');
        $show->color('color');
        $show->color_name('color_name');
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
        $form = new Form(new Color);
        $event = Event::where('owner_id', '=', \Encore\Admin\Facades\Admin::user()->id)
            ->where('active', '=', 1)->first();
//        $form->hidden('event_id', 'event_id')->value($event->id);
        $form->hidden('owner_id', 'owner_id')->value($event->owner_id);
        $form->color('color', 'Цвет');
        $form->text('color_name', 'Название цвета')->placeholder('Деревянный или прозрачный');
        $form->tools(function (Form\Tools $tools) {
            $tools->disableList();
            $tools->disableDelete();
            $tools->disableView();
        });
        $form->footer(function ($footer) {
            $footer->disableReset();
            $footer->disableViewCheck();
            $footer->disableEditingCheck();
            $footer->disableCreatingCheck();

        });
        return $form;
    }
}
