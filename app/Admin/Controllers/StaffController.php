<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Staff;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class StaffController extends Controller
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
            ->body($this->form());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Staff);
        $grid->model()->orderBy('middlename', 'desc');
        $grid->model()->where('owner_id', '=', Admin::user()->id);
        $grid->filter(function($filter){
            $filter->disableIdFilter();
            $filter->in('type', 'Роль')->checkbox(Staff::SHOW_TYPES);
        });
        $grid->disableExport();
        $event = Event::where('owner_id', '=', \Encore\Admin\Facades\Admin::user()->id)->where('active', 1)->first();
        $events = Event::where('owner_id', Admin::user()->id)->pluck('id')->toArray();
        if($event){
            if($event->is_input_birthday){
                $grid->column('allow_years', 'Возраста для сета')->multipleSelect(User::ages);
                Admin::script(<<<EOT
                $(document).ready(function() {
                    $('.ie-trigger-column-allow_years').each(function() {
                        if ($(this).find('.ie-display').text().trim() === '') {
                            $(this).find('i.fa-edit').css('visibility', 'visible');
                        }
                    });
                });
        EOT
                );
            }
        }
        $grid->quickCreate(function (Grid\Tools\QuickCreate $create) use ($event, $events) {
            $admin_id = \Encore\Admin\Facades\Admin::user()->id;
            $create->integer('owner_id', $admin_id)->default($admin_id)->style('display', 'None');
            $create->text('middlename', 'Фамилия Имя')->required();
            $create->select('type', 'Роль')->options(Staff::SHOW_TYPES)->required();
            $create->integer('cost', 'Оплата в час');
            $create->text('contact', 'Контакты');
        });

        $grid->column('middlename', 'Фамилия Имя')->editable();
        $grid->column('type', 'Роль')->select(Staff::SHOW_TYPES)->sortable();
        Admin::script(<<<EOT
                $(document).ready(function() {
                    $('.ie-trigger-column-events_id').each(function() {
                        // Если внутри span нет текста, делаем иконку видимой
                        if ($(this).find('.ie-display').text().trim() === '') {
                            $(this).find('i.fa-edit').css('visibility', 'visible');
                        }
                    });
                });
        EOT);
        $grid->column('cost', 'Оплата в час')->editable();
        $grid->column('contact', 'Контакты')->editable();

        return $grid;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Staff);
        $form->hidden('owner_id', 'owner_id')->default(Admin::user()->id);
        $form->hidden('events_id', 'events_id');
        $form->text('middlename', 'Фамилия Имя');
        $events = Event::where('owner_id', Admin::user()->id)->pluck('title', 'id')->toArray();
        $form->multipleSelect('events_id', 'Соревы')->options($events);
        $form->select('type', 'Роль')->options(Staff::SHOW_TYPES);
        $form->text('cost', 'Оплата');
        $form->text('contact', 'Контакты');
        $form->footer(function ($footer) {
            $footer->disableReset();
            $footer->disableViewCheck();
            $footer->disableEditingCheck();
            $footer->disableCreatingCheck();
        });
        $form->tools(function (Form\Tools $tools) {
            $tools->disableList();
            $tools->disableDelete();
            $tools->disableView();
        });
        return $form;
    }
}
