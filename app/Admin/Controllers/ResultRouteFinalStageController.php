<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\ResultRouteFinalStage\BatchResultFinal;
use App\Admin\Actions\ResultRouteSemiFinalStage\BatchResultSemiFinal;
use App\Admin\CustomAction\ActionExport;
use App\Exports\FinalResultExport;
use App\Models\Event;
use App\Models\Participant;
use App\Models\ResultFinalStage;
use App\Models\ResultRouteFinalStage;
use App\Models\ResultSemiFinalStage;
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
            ->row(function(Row $row) {
                $event = Event::where('owner_id', '=', Admin::user()->id)->where('active', '=', 1)->first();
                if($event) {
                    $row->column(10, $this->grid2());
                    $row->column(10, $this->grid());
                } else {
                    $row->column(10, $this->grid2());
                    $row->column(10, $this->grid3());
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

        $grid = new Grid(new ResultRouteFinalStage);
        if (!Admin::user()->isAdministrator()){
            $grid->model()->where('owner_id', '=', Admin::user()->id);
        }
        $grid->model()->where(function ($query) {
            $query->has('event.result_final_stage');
        });
        $grid->tools(function (Grid\Tools $tools) {
            $tools->append(new BatchResultFinal);
        });

        $events_title = Event::where('owner_id', '=', Admin::user()->id)->where('active', '=', 1)->pluck('title','id')->toArray();
        $event = Event::where('owner_id', '=', Admin::user()->id)->where('active', '=', 1)->first();
        $grid->column('event_id','Соревнование')->select($events_title);
        $grid->column('final_route_id', __('Номер маршрута'))->editable();
        $grid->column('user_id', __('Участник'))->select($this->getUsersFinal($event->id));
        $grid->column('amount_try_top', __('Кол-во попыток на топ'))->editable();
        $grid->column('amount_try_zone', __('Кол-во попыток на зону'))->editable();
        $grid->disableExport();
        $grid->disableColumnSelector();
        $grid->disableCreateButton();
        $grid->filter(function($filter){
            $event = Event::where('owner_id', '=', Admin::user()->id)->where('active', '=', 1)->first();
            $ev = Event::where('owner_id', '=', Admin::user()->id)->where('active', '=', 1)->pluck( 'title', 'id');
            $male_users_middlename = ResultSemiFinalStage::better_of_participants_semifinal_stage($event->id, 'male', 6)->pluck('middlename','id')->toArray();
            $female_users_middlename = ResultSemiFinalStage::better_of_participants_semifinal_stage($event->id, 'female', 6)->pluck('middlename','id')->toArray();
            $new = $male_users_middlename + $female_users_middlename;
            $filter->disableIdFilter();
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
            $actions->append(new ActionExport($actions->getKey(), 'final' , 'excel'));
            $actions->append(new ActionExport($actions->getKey(), 'final', 'csv'));
            $actions->append(new ActionExport($actions->getKey(), 'final', 'ods'));
        });
        $grid->disableExport();
        $grid->disableCreateButton();
        $grid->disableColumnSelector();
        $grid->disablePagination();
        $grid->disablePerPageSelector();
        $grid->disableBatchActions();
        $grid->disableFilter();
        $grid->column('title', 'Соревнование')->expand(function ($model) {
            $headers = ['Участник', 'Пол', 'Место с учетом квалы', 'Кол-во топ','Кол-во попыток на топ','Кол-во зон', 'Кол-во попыток на зону', ];
            $style = ['table-bordered','table-hover', 'table-striped'];

            if($model->is_semifinal){
                $users_male = ResultSemiFinalStage::better_of_participants_semifinal_stage($model->id, 'male', 6);
                $users_female = ResultSemiFinalStage::better_of_participants_semifinal_stage($model->id, 'female', 6);
                $type = 'final';
            } else {
                $users_male = Participant::better_participants($model->id, 'male', 6);
                $users_female = Participant::better_participants($model->id, 'female', 6);
                $type = 'semifinal';
            }
            $fields = ['firstname','id','category','active','team','city', 'email','year','lastname','skill','sport_category','email_verified_at', 'created_at', 'updated_at'];
            $male = ResultRouteSemiFinalStageController::getUsersSorted($users_male, $fields, $model, $type, Admin::user()->id);
            $female = ResultRouteSemiFinalStageController::getUsersSorted($users_female, $fields, $model, $type, Admin::user()->id);
//            dd($male);
            $final_all_users = array_merge($male, $female);
            $all_users = array_merge($male, $female);
            foreach ($final_all_users as $index => $user){
                $fields = ['owner_id', 'event_id', 'user_id'];
                $final_all_users[$index] = collect($user)->except($fields)->toArray();
            }
            foreach ($all_users as $index => $user){
                $fields = ['gender', 'middlename'];
                $all_users[$index] = collect($user)->except($fields)->toArray();

                $final_result_stage = ResultFinalStage::where('event_id', '=', $all_users[$index]['event_id'])->where('user_id', '=', $all_users[$index]['user_id'])->first();
                if(!$final_result_stage){
                    $final_result_stage = new ResultFinalStage;
                }
                $final_result_stage->event_id = $all_users[$index]['event_id'];
                $final_result_stage->user_id = $all_users[$index]['user_id'];
                $final_result_stage->owner_id = $all_users[$index]['owner_id'];
                $final_result_stage->amount_top = $all_users[$index]['amount_top'];
                $final_result_stage->amount_try_top = $all_users[$index]['amount_try_top'];
                $final_result_stage->amount_zone = $all_users[$index]['amount_zone'];
                $final_result_stage->amount_try_zone = $all_users[$index]['amount_try_zone'];
                $final_result_stage->place = $all_users[$index]['place'];
                $final_result_stage->save();
            }
//            dd($final_all_users);
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
        $grid->column('active', 'Статус')->using([0 => 'Не активно', 1 => 'Активно'])->display(function ($title, $column) {
            If ($this->active == 0) {
                return $column->label('default');
            } else {
                return $column->label('success');
            }
        });

        return $grid;
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid3()
    {
        $grid = new Grid(new Event);
        $grid->disableExport();
        $grid->disableColumnSelector();
        $grid->disableCreateButton();
        $grid->disablePagination();
        $grid->disablePerPageSelector();
        $grid->disableBatchActions();
        $grid->disableFilter();
        $grid->disableActions();
        $grid->setTitle('Нет активных соревнований');
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
        $form->hidden('amount_zone', 'amount_zone');
        $form->hidden('amount_top', 'amount_top');
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

    protected function getUsersFinal($event_id)
    {
        $participants_male = ResultSemiFinalStage::better_of_participants_semifinal_stage($event_id, 'male', 6);
        $participants_female = ResultSemiFinalStage::better_of_participants_semifinal_stage($event_id, 'female', 6);
        $new = $participants_female->merge($participants_male);
        return User::whereIn('id', $new->pluck('id'))->pluck('middlename', 'id')->toArray();
    }


    public function exportFinalExcel(Request $request)
    {
        $file_name = 'Результаты финала.xlsx';
        $result = Excel::download(new FinalResultExport($request->id), $file_name, \Maatwebsite\Excel\Excel::XLSX);
        return response()->download($result->getFile(), $file_name, [
            'Content-Type' => 'application/xlsx',
        ]);
    }
    public function exportFinalCsv(Request $request)
    {
        $file_name = 'Результаты финала.csv';
        $result = Excel::download(new FinalResultExport($request->id), $file_name, \Maatwebsite\Excel\Excel::CSV);
        return response()->download($result->getFile(), $file_name, [
            'Content-Type' => 'application/csv',
        ]);
    }
    public function exportFinalOds(Request $request)
    {
        $file_name = 'Результаты финала.ods';
        $result = Excel::download(new FinalResultExport($request->id), $file_name, \Maatwebsite\Excel\Excel::ODS);
        return response()->download($result->getFile(), $file_name, [
            'Content-Type' => 'application/ods',
        ]);
    }

}
