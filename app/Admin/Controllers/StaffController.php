<?php

namespace App\Admin\Controllers;

use App\Exports\ExportDocumentJudges;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Staff;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

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
            $filter->in('judge_category', 'Судейская категория')->checkbox(Staff::JUDGE_CATEGORY);
        });
        $grid->disableExport();
        $grid->quickCreate(function (Grid\Tools\QuickCreate $create){
            $admin_id = \Encore\Admin\Facades\Admin::user()->id;
            $create->integer('owner_id', $admin_id)->default($admin_id)->style('display', 'None');
            $create->text('middlename', 'Фамилия Имя')->required();
            $create->select('type', 'Роль')->options(Staff::SHOW_TYPES)->required();
            $create->select('judge_category', 'Судейская категория')->options(Staff::JUDGE_CATEGORY)->required();
            $create->select('area', 'Территория');
            $create->integer('cost', 'Оплата в час');
            $create->text('contact', 'Контакты');
        });

        $grid->column('middlename', 'Фамилия Имя')->editable();
        $grid->column('type', 'Роль')->select(Staff::SHOW_TYPES)->sortable();
        $grid->column('judge_category', 'Судейская категория')->select(Staff::JUDGE_CATEGORY)->sortable();
        $grid->column('area', 'Территория')->editable();
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
        $form->text('middlename', 'Фамилия Имя');
        $events = Event::where('owner_id', Admin::user()->id)->pluck('title', 'id')->toArray();
        $form->multipleSelect('events_id', 'Соревы')->options($events);
        $form->select('type', 'Роль')->options(Staff::SHOW_TYPES);
        $form->select('judge_category', 'Судейская категория')->options(Staff::JUDGE_CATEGORY);
        $form->text('area', 'Территория');
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

    public function getJudgesExcel(Request $request)
    {
        $file_name = 'Справка о судьях.xlsx';
        $result = Excel::download(new ExportDocumentJudges($request->id), $file_name, \Maatwebsite\Excel\Excel::XLSX);
        return response()->download($result->getFile(), $file_name, [
            'Content-Type' => 'application/xlsx',
        ]);
    }
}
