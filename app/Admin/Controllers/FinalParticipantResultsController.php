<?php

namespace App\Admin\Controllers;

use App\Models\Event;
use App\Models\FinalParticipantResult;
use App\Http\Controllers\Controller;
use App\Models\Participant;
use App\Models\User;
use Encore\Admin\Controllers\Dashboard;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Show;

class FinalParticipantResultsController extends Controller
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
//            ->body($this->grid())
            ->row(function(Row $row) {
                $row->column(8, $this->grid());
//                $row->column(8, function (Column $column) {
//                    $column->row('111');
//                    $column->row('222');
//                    $column->row('333');
//                });
            });

    }

//    /**
//     * Show interface.
//     *
//     * @param mixed $id
//     * @param Content $content
//     * @return Content
//     */
//    public function show($id, Content $content)
//    {
//        return $content
//            ->header(trans('admin.detail'))
//            ->description(trans('admin.description'))
//            ->body($this->detail($id));
//    }

//    /**
//     * Edit interface.
//     *
//     * @param mixed $id
//     * @param Content $content
//     * @return Content
//     */
//    public function edit($id, Content $content)
//    {
//        return $content
//            ->header(trans('admin.edit'))
//            ->description(trans('admin.description'))
//            ->body($this->form()->edit($id));
//    }
//
//    /**
//     * Create interface.
//     *
//     * @param Content $content
//     * @return Content
//     */
//    public function create(Content $content)
//    {
//        return $content
//            ->header(trans('admin.create'))
//            ->description(trans('admin.description'))
//            ->body($this->form());
//    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new FinalParticipantResult);
        $grid->model()->orderBy('final_points', 'desc');
        $grid->disableActions();
        $grid->disableCreateButton();
        if (!Admin::user()->isAdministrator()){
            $grid->model()->where('owner_id', '=', Admin::user()->id);
        }
        $grid->column('user.middlename', __('Участники'));
        $grid->column('event.title', 'Соревнование');
        $grid->column('final_points', 'Итоговый балл')->sortable();
//        $grid->created_at(trans('admin.created_at'));
//        $grid->updated_at(trans('admin.updated_at'));

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
//        $show = new Show(FinalParticipantResult::findOrFail($id));
//
//        $show->id('ID');
//        $show->event_id('event_id');
//        $show->user_id('user_id');
//        $show->final_points('final_points');
//        $show->created_at(trans('admin.created_at'));
//        $show->updated_at(trans('admin.updated_at'));
//
//        return $show;
//    }
//
//    /**
//     * Make a form builder.
//     *
//     * @return Form
//     */
//    protected function form()
//    {
//        $form = new Form(new FinalParticipantResult);
//
//        $form->display('ID');
//        $form->hidden('owner_id')->value(Admin::user()->id);
//        $form->text('event_id', 'event_id');
//        $form->text('user_id', 'user_id');
//        $form->text('final_points', 'final_points');
//        $form->display(trans('admin.created_at'));
//        $form->display(trans('admin.updated_at'));
//
//        return $form;
//    }
}
