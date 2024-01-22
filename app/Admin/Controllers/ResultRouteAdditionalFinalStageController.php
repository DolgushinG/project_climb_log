<?php

namespace App\Admin\Controllers;

use App\Admin\CustomAction\ActionExport;
use App\Exports\AdditionalFinalResultExport;
use App\Exports\FinalResultExport;
use App\Models\Event;
use App\Models\Participant;
use App\Models\ParticipantCategory;
use App\Models\ResultAdditionalFinalStage;
use App\Models\ResultFinalStage;
use App\Models\ResultParticipant;
use App\Models\ResultRouteAdditionalFinalStage;
use App\Models\ResultRouteFinalStage;
use App\Http\Controllers\Controller;
use App\Models\User;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Show;
use Illuminate\Http\Request;
use Jxlwqq\DataTable\DataTable;
use Maatwebsite\Excel\Facades\Excel;

class ResultRouteAdditionalFinalStageController extends Controller
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
                $row->column(10, $this->grid2());
                $row->column(10, $this->grid());
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

        $grid = new Grid(new ResultRouteAdditionalFinalStage);
        if (!Admin::user()->isAdministrator()){
            $grid->model()->where('owner_id', '=', Admin::user()->id);
        }
        $events_title = Event::where('owner_id', '=', Admin::user()->id)->where('active', '=', 1)->pluck('title','id')->toArray();
        $event = Event::where('owner_id', '=', Admin::user()->id)->where('active', '=', 1)->first();
        $grid->column('event_id','Соревнование')->select($events_title);
        $grid->column('final_route_id', __('Номер маршрута'))->editable();
        $grid->column('user_id', __('Участник'))->select($this->getUsersAdditionalFinal($event->id));
        $grid->column('amount_try_top', __('Кол-во попыток на топ'))->editable();
        $grid->column('amount_try_zone', __('Кол-во попыток на зону'))->editable();
        $grid->disableExport();
        $grid->disableColumnSelector();
        $grid->disableCreateButton();
        $grid->filter(function($filter){
            $event = Event::where('owner_id', '=', Admin::user()->id)->where('active', '=', 1)->first();
            $ev = Event::where('owner_id', '=', Admin::user()->id)->where('active', '=', 1)->pluck( 'title', 'id');
            $male_users_middlename = ResultFinalStage::better_of_participants_final_stage($event->id, 'male', 6)->pluck('middlename','id')->toArray();
            $female_users_middlename = ResultFinalStage::better_of_participants_final_stage($event->id, 'female', 6)->pluck('middlename','id')->toArray();
            $new = $male_users_middlename + $female_users_middlename;
            // Remove the default id filter
            $filter->disableIdFilter();

            // Add a column filter
            $filter->in('event.id', 'Соревнование')->checkbox(
                $ev
            );

            $filter->in('user.id', 'Участник')->checkbox(
                $new
            );

//            // Add a column filter
            $filter->in('user.gender', 'Пол')->checkbox([
                'male'    => 'Мужчина',
                'female'    => 'Женщина',
            ]);

        });
        $grid->quickCreate(function (Grid\Tools\QuickCreate $create)  {
            $events = Event::where('active', '=', 1)->where('owner_id', '=', Admin::user()->id)->pluck('title','id');
            $event = Event::where('active', '=', 1)->where('owner_id', '=', Admin::user()->id)->first();
            $create->select('event_id','Соревнование')->options($events);
            $create->integer('owner_id', Admin::user()->id)->default(Admin::user()->id)->style('display', 'None');
            $create->integer('final_route_id', 'Номер маршрута');
            $create->select('user_id', 'Участники')->options($this->getUsersAdditionalFinal($event->id));
            $create->integer('amount_try_top', 'Кол-во попыток на топ');
            $create->integer('amount_try_zone', 'Кол-во попыток на зону');
        });
        return $grid;
    }


    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid2()
    {
        $grid = new Grid(new Event);
        if (!Admin::user()->isAdministrator()){
            $grid->model()->where('owner_id', '=', Admin::user()->id);
        }
        $grid->actions(function ($actions) {
            $actions->disableDelete();
            $actions->disableEdit();
            $actions->disableView();
            $actions->append(new ActionExport($actions->getKey(), 'additional-final' , 'excel'));
            $actions->append(new ActionExport($actions->getKey(), 'additional-final', 'csv'));
            $actions->append(new ActionExport($actions->getKey(), 'additional-final', 'ods'));
        });
        $grid->disableExport();
        $grid->disableColumnSelector();
        $grid->disableCreateButton();
        $grid->disablePagination();
        $grid->disablePerPageSelector();
        $grid->disableBatchActions();
        $grid->disableFilter();
        $grid->column('title', 'Соревнование')->expand(function ($model) {
            $headers = ['Участник', 'Пол', 'Место с учетом квалы', 'Кол-во топ','Кол-во попыток на топ','Кол-во зон', 'Кол-во попыток на зону', ];
            $style = ['table-bordered','table-hover', 'table-striped'];
            $users_male = ResultFinalStage::better_of_participants_final_stage($model->id, 'male', 6);
            $users_female = ResultFinalStage::better_of_participants_final_stage($model->id, 'female', 6);
            $fields = ['firstname','id','category','active','team','city', 'email','year','lastname','skill','sport_category','email_verified_at', 'created_at', 'updated_at'];
            $male = ResultRouteFinalStageController::getUsersSorted($users_male, $fields, $model, 'additionalFinal');
            $female = ResultRouteFinalStageController::getUsersSorted($users_female, $fields, $model, 'additionalFinal');
            $final_all_users = array_merge($male, $female);
            $all_users = array_merge($male, $female);

            foreach ($final_all_users as $index => $user){
                $fields = ['owner_id', 'event_id', 'user_id'];
                $final_all_users[$index] = collect($user)->except($fields)->toArray();
            }
            foreach ($all_users as $index => $user){
                $fields = ['gender', 'middlename'];
                $all_users[$index] = collect($user)->except($fields)->toArray();

                $final_result_stage = ResultAdditionalFinalStage::where('event_id', '=', $all_users[$index]['event_id'])->where('user_id', '=', $all_users[$index]['user_id'])->first();
                if(!$final_result_stage){
                    $final_result_stage = new ResultAdditionalFinalStage;
                }
                $final_result_stage->amount_top = $all_users[$index]['amount_top'];
                $final_result_stage->amount_try_top = $all_users[$index]['amount_try_top'];
                $final_result_stage->amount_zone = $all_users[$index]['amount_zone'];
                $final_result_stage->place = $all_users[$index]['place'];
                $final_result_stage->save();
            }
            $options = [
                'responsive' => true,
                'paging' => true,
                'lengthChange' => true,
                'searching' => true,
                'ordering' => false,
                'info' => true,
                'autoWidth' => true,
                'deferRender' => true,
                'processing' => true,
            ];
            return new DataTable($headers, $final_all_users, $style, $options);
        });
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
        $show = new Show(ResultRouteAdditionalFinalStage::findOrFail($id));

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
        $form = new Form(new ResultRouteAdditionalFinalStage);

        $form->display('ID');
        $form->hidden('owner_id')->value(Admin::user()->id);
        $form->text('event_id', 'event_id');
        $form->text('user_id', 'user_id');
        $form->text('final_route_id', 'final_route_id');
        $form->text('amount_try_top', 'amount_try_top');
        $form->text('amount_try_zone', 'amount_try_zone');
        $form->hidden('amount_zone', 'amount_try_zone');
        $form->hidden('amount_top', 'amount_try_zone');
        $form->display(trans('admin.created_at'));
        $form->display(trans('admin.updated_at'));

        $form->saving(function (Form $form) {
            if($form->amount_try_top > 0){
                $form->amount_top  = 1;
            } else {
                $form->amount_top  = 0;
            }
            if($form->amount_try_zone > 0){
                $form->amount_zone  = 1;
            } else {
                $form->amount_zone  = 0;
            }
        });
        return $form;
    }

    protected function getUsersAdditionalFinal($event_id)
    {
        $participants_male = ResultFinalStage::better_of_participants_final_stage($event_id, 'male', 6);
        $participants_female = ResultFinalStage::better_of_participants_final_stage($event_id, 'female', 6);
        $new = $participants_female->merge($participants_male);
        return User::whereIn('id', $new->pluck('id'))->pluck('middlename', 'id')->toArray();
    }


    public function exportAdditionalFinalExcel(Request $request)
    {
        $file_name = 'Результаты дополнительного финала.xlsx';
        $result = Excel::download(new AdditionalFinalResultExport($request->id), $file_name, \Maatwebsite\Excel\Excel::XLSX);
        return response()->download($result->getFile(), $file_name, [
            'Content-Type' => 'application/xlsx',
        ]);
    }
    public function exportAdditionalFinalCsv(Request $request)
    {
        $file_name = 'Результаты дополнительного финала.csv';
        $result = Excel::download(new AdditionalFinalResultExport($request->id), $file_name, \Maatwebsite\Excel\Excel::CSV);
        return response()->download($result->getFile(), $file_name, [
            'Content-Type' => 'application/csv',
        ]);
    }
    public function exportAdditionalFinalOds(Request $request)
    {
        $file_name = 'Результаты дополнительного финала.ods';
        $result = Excel::download(new AdditionalFinalResultExport($request->id), $file_name, \Maatwebsite\Excel\Excel::ODS);
        return response()->download($result->getFile(), $file_name, [
            'Content-Type' => 'application/ods',
        ]);
    }

}
