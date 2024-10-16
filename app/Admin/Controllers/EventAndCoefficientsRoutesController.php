<?php

namespace App\Admin\Controllers;

use App\Models\EventAndCoefficientRoute;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;

class  EventAndCoefficientsRoutesController extends Controller
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
//
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
        $grid = new Grid(new EventAndCoefficientRoute);
        $grid->model()->orderBy('route_id', 'desc');
        $grid->disableActions();
        $grid->disableCreateButton();
        if (!Admin::user()->isAdministrator()){
            $grid->model()->where('owner_id', '=', Admin::user()->id);
        }
        $grid->column('event.title', 'Соревнование');
        $grid->column('route_id', 'Номер маршрута');
        $grid->column('coefficient_male', 'Коэффициент у мужчин');
        $grid->column('coefficient_female', 'Коэффициент у женщин');
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
//        $show = new Show(EventAndCoefficientRoute::findOrFail($id));
//
//        $show->id('ID');
//        $show->event_id('event_id');
//        $show->route_id('route_id');
//        $show->coefficient('coefficient');
//        $show->created_at(trans('admin.created_at'));
//        $show->updated_at(trans('admin.updated_at'));
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
//        $form = new Form(new EventAndCoefficientRoute);
//
//        $form->display('ID');
//        $form->hidden('owner_id')->value(Admin::user()->id);
//        $form->text('event_id', 'event_id');
//        $form->text('route_id', 'route_id');
//        $form->text('coefficient', 'coefficient');
//        $form->display(trans('admin.created_at'));
//        $form->display(trans('admin.updated_at'));
//
//        return $form;
//    }
}
