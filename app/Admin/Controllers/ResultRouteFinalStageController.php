<?php

namespace App\Admin\Controllers;

use App\Admin\CustomAction\ActionExport;
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

        $grid = new Grid(new ResultRouteFinalStage);
        if (!Admin::user()->isAdministrator()){
            $grid->model()->where('owner_id', '=', Admin::user()->id);

        }
        $grid->column('event_id','Соревнование')->select(Event::where('owner_id', '=', Admin::user()->id)->pluck('title','id')->toArray());
        $grid->column('final_route_id', __('Номер маршрута'))->editable();
        $grid->column('user_id', __('Участник'))->select($this->getUsers()->toArray());
        $grid->column('amount_try_top', __('Кол-во попыток на топ'))->editable();
        $grid->column('amount_try_zone', __('Кол-во попыток на зону'))->editable();
        $grid->disableExport();
        $grid->disableColumnSelector();
        $grid->disableCreateButton();
        $grid->filter(function($filter){
            $event = Event::where('owner_id', '=', Admin::user()->id)->where('active', '=', 1)->first();
            $ev = Event::where('owner_id', '=', Admin::user()->id)->where('active', '=', 1)->pluck( 'title', 'id');
            $male_users_middlename = Participant::better_participants($event->id, 'male', 10)->pluck('middlename','id')->toArray();
            $female_users_middlename = Participant::better_participants($event->id, 'female', 10)->pluck('middlename','id')->toArray();
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
            $create->select('event_id','Соревнование')->options($events);
            $create->integer('owner_id', Admin::user()->id)->default(Admin::user()->id)->style('display', 'None');
            $create->integer('final_route_id', 'Номер маршрута');
            $create->select('user_id', 'Участники')->options($this->getUsers()->toArray());
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
            $actions->append(new ActionExport($actions->getKey(), 'final' , 'excel'));
            $actions->append(new ActionExport($actions->getKey(), 'final', 'csv'));
            $actions->append(new ActionExport($actions->getKey(), 'final', 'ods'));
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
            $users_male = Participant::better_participants($model->id, 'male', 10);
            $users_female = Participant::better_participants($model->id, 'female', 10);
            $fields = ['firstname','id','category','active','team','city', 'email','year','lastname','skill','sport_category','email_verified_at', 'created_at', 'updated_at'];
            $male = self::getUsersSorted($users_male, $fields, $model, 'final');
            $female = self::getUsersSorted($users_female, $fields, $model, 'final');
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

    /**
     * @return object
     */
    protected function getUsers(): object
    {
        $participant = Participant::where('owner_id', '=', Admin::user()->id)
            ->where('active', '=', 1)
            ->pluck('user_id')->toArray();
        return User::whereIn('id', $participant)->pluck('middlename', 'id');
    }

    /**
     * @param $users
     * @param $fields
     * @param $model
     * @param $type
     * @return array
     */
    public static function getUsersSorted($users, $fields, $model, $type): array
    {
        if (count($users->toArray()) == 0){
            return [];
        }
        $users_with_result = [];
        foreach ($users as $index => $user){
            if($type == 'final'){
                $result_user = ResultRouteFinalStage::where('owner_id', '=', Admin::user()->id)
                    ->where('event_id', '=', $model->id)
                    ->where('user_id', '=', $user->id)
                    ->get();
            } else {
                # additional final stage
                $result_user = ResultRouteAdditionalFinalStage::where('owner_id', '=', Admin::user()->id)
                    ->where('event_id', '=', $model->id)
                    ->where('user_id', '=', $user->id)
                    ->get();
            }
            $result = ResultRouteFinalStage::merge_result_user_in_final_stage($result_user);
            if($result['amount_top'] != null && $result['amount_try_top'] != null && $result['amount_zone'] != null && $result['amount_try_zone'] != null){
                $users_with_result[$index] = collect($user->toArray())->except($fields);
                $users_with_result[$index]['result'] = $result;
                $users_with_result[$index]['place'] = null;
                $users_with_result[$index]['owner_id'] = Admin::user()->id;
                $users_with_result[$index]['user_id'] = $user->id;
                $users_with_result[$index]['event_id'] = $model->id;
                $users_with_result[$index]['gender'] = trans_choice('somewords.'.$user->gender, 10);
                $users_with_result[$index]['amount_top'] = $result['amount_top'];
                $users_with_result[$index]['amount_try_top'] = $result['amount_try_top'];
                $users_with_result[$index]['amount_zone'] = $result['amount_zone'];
                $users_with_result[$index]['amount_try_zone'] = $result['amount_try_zone'];
            }

        }
        $users_sorted = Participant::counting_final_place($model->id, $users_with_result);

        foreach ($users_sorted as $index => $user){
            $fields = ['result'];
            $users_sorted[$index] = collect($user)->except($fields)->toArray();
            if($type == 'final'){
                $result = ResultFinalStage::where('user_id', '=', $users_sorted[$index]['user_id'])->where('event_id', '=', $model->id)->first();
                if (!$result){
                    $result = new ResultFinalStage;
                }
            } else {
                $result = ResultAdditionalFinalStage::where('user_id', '=', $users_sorted[$index]['user_id'])->where('event_id', '=', $model->id)->first();
                if (!$result){
                    $result = new ResultAdditionalFinalStage;
                }
            }
            $result->event_id = $users_sorted[$index]['event_id'];
            $result->user_id = $users_sorted[$index]['user_id'];
            $result->owner_id = $users_sorted[$index]['owner_id'];
            $result->amount_top = $users_sorted[$index]['amount_top'];
            $result->amount_try_top = $users_sorted[$index]['amount_try_top'];
            $result->amount_zone = $users_sorted[$index]['amount_zone'];
            $result->amount_try_zone = $users_sorted[$index]['amount_try_zone'];
            $result->place = $users_sorted[$index]['place'];
            $result->save();
        }
        return $users_sorted;
    }

    public function exportFinalExcel(Request $request)
    {
        $file_name = 'Результаты финалов.xlsx';
        $result = Excel::download(new FinalResultExport($request->id), $file_name, \Maatwebsite\Excel\Excel::XLSX);
        return response()->download($result->getFile(), $file_name, [
            'Content-Type' => 'application/xlsx',
        ]);
    }
    public function exportFinalCsv(Request $request)
    {
        $file_name = 'Результаты финалов.csv';
        $result = Excel::download(new FinalResultExport($request->id), $file_name, \Maatwebsite\Excel\Excel::CSV);
        return response()->download($result->getFile(), $file_name, [
            'Content-Type' => 'application/csv',
        ]);
    }
    public function exportFinalOds(Request $request)
    {
        $file_name = 'Результаты финалов.ods';
        $result = Excel::download(new FinalResultExport($request->id), $file_name, \Maatwebsite\Excel\Excel::ODS);
        return response()->download($result->getFile(), $file_name, [
            'Content-Type' => 'application/ods',
        ]);
    }

}
