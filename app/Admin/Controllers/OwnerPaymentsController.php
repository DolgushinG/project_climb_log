<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\BatchForceRecouting;
use App\Admin\Actions\BatchGenerateParticipant;
use App\Admin\Actions\ResultQualification\BatchResultQualification;
use App\Models\OwnerPayments;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Support\Facades\DB;

class OwnerPaymentsController extends Controller
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
        $grid = new Grid(new OwnerPayments);
        if (!Admin::user()->isAdministrator()){
            $grid->model()->where('owner_id', '=', Admin::user()->id);
        }
        $grid->model()->where(function ($query) {
            $query->has('event.ownerPayments');
        });
        $grid->disableBatchActions();
        $grid->disableFilter();
        $grid->disableExport();
        $grid->disableCreateButton();
        $grid->disableColumnSelector();
        $grid->actions(function ($actions){
            $actions->disableEdit();
            $actions->disableView();
            $actions->disableDelete();
        });
        if(Admin::user()->isAdministrator()){
            $grid->column('owner_id', 'Скалодром')->display(function ($id){
                $user = DB::table('admin_users')->find($id);
                return $user->climbing_gym_name;
            });
        }
        $grid->column('event_title', 'Соревнование');
        $grid->column('amount_for_pay', 'Сумма к оплате')->display(function ($amount){
            return $amount.' руб.';
        });
        $grid->column('amount_participant', 'Кол-во участников');
        $grid->column('amount_start_price', 'Стартовый взнос')->display(function ($amount){
            return $amount.' руб.';
        });;
        $grid->column('amount_cost_for_service', 'Процент за пользование сервисом')->display(function ($amount){
            return $amount.' % с каждого стартового взноса';
        });
        if(Admin::user()->isAdministrator()){
            $states = [
                'on' => ['value' => 1, 'text' => 'Да', 'color' => 'success'],
                'off' => ['value' => 0, 'text' => 'Нет', 'color' => 'default'],
            ];
            $grid->column('is_paid', 'Оплачено')->switch($states);
        } else {
            $grid->column('is_paid', 'Оплата')->using([0 => 'Не оплачено', 1 => 'Оплачено'])->display(function ($title, $column) {
                if($title !== "Оплачено") {
                    return $column->label('warning');
                } else {
                    return $column->label('success');
                }
            });
        }


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
        $show = new Show(OwnerPayments::findOrFail($id));

        $show->field('amount_for_pay', 'Сумма к оплате');
        $show->field('bill', 'Чек по оплате');
        $show->field('request_for_payment', 'Счет на оплату');
        $show->field('event_title', 'Соревнование');
        $show->field('amount_participant', 'Кол-во участников');
        $show->field('amount_start_price', 'Стартовый взнос');
        $show->field('amount_cost_for_service', 'Процент за пользование сервисом');
        $show->panel()
            ->tools(function ($tools) {
                $tools->disableEdit();
                $tools->disableList();
                $tools->disableDelete();
            });
        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new OwnerPayments);

        $form->display('ID');
        $form->number('owner_id', 'owner_id');
        $form->number('event_id', 'event_id');
        $form->number('amount_for_pay');
        $form->text('bill', 'bill');
        $form->text('request_for_payment');
        $form->text('event_title');
        $form->number('amount_participant');
        $form->switch('is_paid');

        return $form;
    }
}
