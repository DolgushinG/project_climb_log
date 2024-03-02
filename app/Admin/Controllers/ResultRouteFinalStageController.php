<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\BatchForceRecouting;
use App\Admin\Actions\ResultQualification\BatchResultQualification;
use App\Admin\Actions\ResultRouteFinalStage\BatchExportResultFinal;
use App\Admin\Actions\ResultRouteFinalStage\BatchResultFinal;
use App\Admin\Actions\ResultRouteSemiFinalStage\BatchResultSemiFinal;
use App\Admin\CustomAction\ActionExport;
use App\Admin\Extensions\Tools\UserGender;
use App\Exports\FinalResultExport;
use App\Models\Event;
use App\Models\Participant;
use App\Models\ParticipantCategory;
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
use Encore\Admin\Widgets\Box;
use Encore\Admin\Widgets\Tab;
use Encore\Admin\Widgets\Table;
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
                $fields = ['firstname','id','category','active','team','city', 'email','year','lastname','skill','sport_category','email_verified_at', 'created_at', 'updated_at'];
                if ($event) {
                    if($event->is_additional_final){
                        # Если выбран режим что финал для всех то отдаем лучшех 6 участников каждый категории
                        $all_group_participants = array();
                        $all_users = array();
                        $users = array();
                        foreach ($event->categories as $category){
                            $category_id = ParticipantCategory::where('category', $category)->where('event_id', $event->id)->first()->id;
                            $all_group_participants['male'][$category] = Participant::better_participants($event->id, 'male', 6, $category_id);
                            $all_group_participants['female'][$category] = Participant::better_participants($event->id, 'female', 6, $category_id);
                        }
//                        dd($all_group_participants);
                        foreach ($all_group_participants as $group_participants){
                            foreach ($group_participants as $participants){
                                $user = ResultRouteSemiFinalStageController::getUsersSorted($participants, $fields, $event, 'final', Admin::user()->id);
                                if($user !== []){
                                    $users[] = $user;
                                }
                            }
                        }
                        foreach ($users as $user){
                            foreach ($user as $a){
                                $all_users[] = $a;
                            }
                        }
                    } else {
                        if($event->is_semifinal){
                            $users_male = ResultSemiFinalStage::better_of_participants_semifinal_stage($event->id, 'male', 6);
                            $users_female = ResultSemiFinalStage::better_of_participants_semifinal_stage($event->id, 'female', 6);
                        } else {
                            $users_male = Participant::better_participants($event->id, 'male', 6);
                            $users_female = Participant::better_participants($event->id, 'female', 6);
                        }

                        $male = ResultRouteSemiFinalStageController::getUsersSorted($users_male, $fields, $event, 'final', Admin::user()->id);
                        $female = ResultRouteSemiFinalStageController::getUsersSorted($users_female, $fields, $event, 'final', Admin::user()->id);
                        $all_users = array_merge($male, $female);
                    }
//                    dd($all_users);
                    foreach ($all_users as $index => $user){
                        $fields = ['gender', 'middlename', 'avatar','telegram_id','yandex_id','vkontakte_id'];
                        $all_users[$index] = collect($user)->except($fields)->toArray();

                        $final_result_stage = ResultFinalStage::where('event_id', '=', $all_users[$index]['event_id'])->where('user_id', '=', $all_users[$index]['user_id'])->first();
                        if(!$final_result_stage){
                            $final_result_stage = new ResultFinalStage;
                        }
                        $category_id = ParticipantCategory::where('id', $all_users[$index]['category_id'])->where('event_id', $event->id)->first()->id;
                        $final_result_stage->event_id = $all_users[$index]['event_id'];
                        $final_result_stage->user_id = $all_users[$index]['user_id'];
                        $final_result_stage->category_id = $category_id;
                        $final_result_stage->owner_id = $all_users[$index]['owner_id'];
                        $final_result_stage->amount_top = $all_users[$index]['amount_top'];
                        $final_result_stage->amount_zone = $all_users[$index]['amount_zone'];
                        $final_result_stage->amount_try_top = $all_users[$index]['amount_try_top'];
                        $final_result_stage->amount_try_zone = $all_users[$index]['amount_try_zone'];
                        $final_result_stage->place = $all_users[$index]['place'];
                        $final_result_stage->save();
                    }
                    $row->column(10, $this->grid2());
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
        $grid->actions(function ($actions) {
            $actions->disableEdit();
            $actions->disableView();
        });
//        $events_title = Event::where('owner_id', '=', Admin::user()->id)->where('active', '=', 1)->pluck('title','id')->toArray();
        $event = Event::where('owner_id', '=', Admin::user()->id)->where('active', '=', 1)->first();
//        $grid->column('event_id','Соревнование')->select($events_title);
        $grid->column('final_route_id', __('Номер маршрута'));
        if($event->is_additional_final){
            $grid->column('user.middlename', __('Участник'));
        } else {
            if($event->is_semifinal){
                $grid->column('user.middlename', __('Участник'));
            } else {
                $grid->column('user_id', __('Участник'))->select($this->getUsersPartipants($event->id));
            }
        }

        $grid->column('amount_try_top', __('Кол-во попыток на топ'));
        $grid->column('amount_try_zone', __('Кол-во попыток на зону'));
        $grid->disableExport();
        $grid->disableColumnSelector();
        $grid->disableCreateButton();
        $grid->filter(function($filter){
            $filter->disableIdFilter();
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
        $grid = new Grid(new ResultFinalStage());
        if (!Admin::user()->isAdministrator()){
            $grid->model()->where('owner_id', '=', Admin::user()->id);
        }
        $grid->model()->where(function ($query) {
            $query->has('event.result_final_stage');
        });
        $grid->tools(function (Grid\Tools $tools) {
            $tools->append(new BatchExportResultFinal);
            $tools->append(new BatchResultFinal);
            $tools->append(new BatchForceRecouting);
        });
        $grid->actions(function ($actions) {
            $actions->disableEdit();
            $actions->disableView();
        });
        $grid->disableExport();
        $grid->disableCreateButton();
        $grid->disableColumnSelector();
        $grid->disablePagination();
        $grid->disablePerPageSelector();
        $grid->disableBatchActions();
        $grid->column('user.middlename', __('Участник'));
        $grid->column('category_id', 'Категория')->display(function ($category_id) {
            $owner_id = Admin::user()->id;
            $event = Event::where('owner_id', '=', $owner_id)
                ->where('active', 1)->first();
            return ParticipantCategory::where('id', '=', $category_id)->where('event_id', $event->id)->first()->category;
        })->sortable();
        $grid->column('place', __('Место'))->sortable();
        $grid->column('amount_top', __('Кол-во топов'));
        $grid->column('amount_try_top', __('Кол-во попыток на топ'));
        $grid->column('amount_zone', __('Кол-во зон'));
        $grid->column('amount_try_zone', __('Кол-во попыток на зону'));
        $grid->filter(function($filter){
            $filter->disableIdFilter();
            $filter->in('user.gender', 'Пол')->checkbox([
                'male'    => 'Мужчина',
                'female'    => 'Женщина',
            ]);
            $filter->in('category_id', 'Категория')->checkbox((new \App\Models\ParticipantCategory)->getUserCategory(Admin::user()->id));

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
    protected function getUsersPartipants($event_id)
    {
        $participants_male = Participant::better_participants($event_id, 'male', 6);
        $participants_female = Participant::better_participants($event_id, 'female', 6);
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
