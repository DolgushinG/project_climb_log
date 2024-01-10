<?php

namespace App\Admin\Controllers;

use App\Models\Event;
use App\Models\Participant;
use App\Models\ResultRouteFinalStage;
use App\Http\Controllers\Controller;
use App\Models\User;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class ResultRouteFinalStageController extends Controller
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


        $grid = new Grid(new ResultRouteFinalStage);
        $grid->id('ID');
        // The third column shows the director field, which is set by the display($callback) method to display the corresponding user name in the users table
//        $grid->event()->display(function($eventId) {
//            return Event::find($eventId)->title;
//        });
        if (!Admin::user()->isAdministrator()){
            $grid->model()->where('owner_id', '=', Admin::user()->id);
        }


//        $grid->event_id('event_id');
//        $grid->owner_id('owner_id');
//        $grid->user_id('user_id');
        $participant = Participant::where('owner_id', '=', Admin::user()->id)->pluck('user_id')->toArray();
        $users = User::whereIn('id', $participant)->pluck('middlename', 'id');
        $grid->column('final_route_id', __('Номер маршрута'))->editable();
        $grid->column('user_id', __('Участники'))->select($users->toArray());
        $grid->column('amount_try_top', __('Кол-во попыток на топ'))->editable();
        $grid->column('amount_try_zone', __('Кол-во попыток на зону'))->editable();
        $grid->quickCreate(function (Grid\Tools\QuickCreate $create) {
            $participant = Participant::where('owner_id', '=', Admin::user()->id)->pluck('user_id')->toArray();
            $users = User::whereIn('id', $participant)->pluck('middlename', 'id');
            $create->select('event_id','Соревнование')->options(Event::all()->pluck('title','id'));
            $create->integer('owner_id', Admin::user()->id)->default(Admin::user()->id)->style('display', 'None');
            $create->integer('final_route_id', 'Номер маршрута');
            $create->select('user_id', 'Участники')->options($users);
            $create->integer('amount_try_top', 'Кол-во попыток на топ');
            $create->integer('amount_try_zone', 'Кол-во попыток на зону');
        });
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
        $show = new Show(ResultRouteFinalStage::findOrFail($id));

        $show->id('ID');
        $show->event_id('event_id');
        $show->user_id('user_id');
        $show->final_route_id('final_route_id');
        $show->amount_try_top('amount_try_top');
        $show->amount_try_zone('amount_try_zone');
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
        $form = new Form(new ResultRouteFinalStage);

        $form->display('ID');
        $form->hidden('owner_id')->value(Admin::user()->id);
        $form->text('event_id', 'event_id');
        $form->text('user_id', 'user_id');
        $form->text('final_route_id', 'final_route_id');
        $form->text('amount_try_top', 'amount_try_top');
        $form->text('amount_try_zone', 'amount_try_zone');
        $form->display(trans('admin.created_at'));
        $form->display(trans('admin.updated_at'));

        return $form;
    }
}
