<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\BatchDisableSets;
use App\Models\Event;
use App\Models\Set;
use App\Http\Controllers\Controller;
use App\Models\User;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Show;

class SetsController extends Controller
{
    use HasResourceActions;


    const DAYS = [
        'Monday' => 'Понедельник',
        'Tuesday' => 'Вторник',
        'Wednesday' => 'Среда',
        'Thursday' => 'Четверг',
        'Friday' => 'Пятница',
        'Saturday' => 'Суббота',
        'Sunday' => 'Воскресенье',
    ];

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
                $event = Event::where('owner_id', '=', Admin::user()->id)->where('active', '=', 1)->first();
                if($event) {
                        $row->column(20, $this->grid());
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
        $grid->model()->where(function ($query) {
            $query->has('event.sets');
        });
        $grid->disableExport();
        $grid->disableCreateButton();
        $grid->disableColumnSelector();
        $grid->disableFilter();
        $grid->actions(function ($actions) {
            $actions->disableEdit();
            $actions->disableView();
            if(Admin::user()->is_delete_result == 0){
                $actions->disableDelete();
            }
        });
        $grid->tools(function (Grid\Tools $tools) {
            $tools->append(new BatchDisableSets);
        });
//        $grid->id('ID');
//        $grid->filter(function($filter){
//            $filter->disableIdFilter();
//            $filter->in('day_of_week', 'День слота')->checkbox(self::DAYS);
//        });

        $event = Event::where('owner_id', '=', \Encore\Admin\Facades\Admin::user()->id)->where('active', 1)->first();
        $grid->quickCreate(function (Grid\Tools\QuickCreate $create) use ($event) {
            $admin_id = \Encore\Admin\Facades\Admin::user()->id;
            $event_id = Event::where('owner_id', '=', Admin::user()->id)->where('active', '=', 1)->first()->id;
            $create->integer('event_id', $admin_id)->default($event_id)->style('display', 'None');
            $create->integer('owner_id', $admin_id)->default($admin_id)->style('display', 'None');
            $create->text('time', 'Время слота')->placeholder('например 10:00 - 12:00');
            if($event) {
                if ($event->is_input_birthday) {
                    $create->multipleSelect('allow_years', 'Возраста для регистрации')->options(User::ages);
                }
            }
            $create->integer('max_participants', 'Максимальное число участников')->placeholder('введите число');
            $create->select('day_of_week', 'День слота')->options(self::DAYS);
            $create->integer('number_set', 'Номер сета')->placeholder('введите номер сета');;
        });
        $grid->column('time', 'Время слота')->editable();
        $grid->column('max_participants', 'Макс. число участников')->editable();

        if($event){
            if($event->is_input_birthday){
                $grid->column('allow_years', 'Возраста для сета')->multipleSelect(User::ages);
                Admin::script(<<<EOT
                $(document).ready(function() {
                    $('.ie-trigger-column-allow_years').each(function() {
                        // Если внутри span нет текста, делаем иконку видимой
                        if ($(this).find('.ie-display').text().trim() === '') {
                            $(this).find('i.fa-edit').css('visibility', 'visible');
                        }
                    });
                });
        EOT
                );
            }
        }



        $grid->column('day_of_week', 'День слота')->help('Дни недели зависят от даты старта соревнований')->select(self::DAYS)->sortable();
        $grid->column('number_set', 'Номер сета')->editable();

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
        $show->allow_years('allow_years');
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
        $form->text('event_id', 'event_id');
        $form->text('owner_id', 'owner_id');
        $form->text('time', 'time');
        $form->multipleSelect('allow_years', 'allow_years');
        $form->text('max_participants', 'max_participants');
        $form->text('day_of_week', 'day_of_week');
        $form->text('number_set', 'number_set');
        $form->display(trans('admin.created_at'));
        $form->display(trans('admin.updated_at'));

        return $form;
    }

}
