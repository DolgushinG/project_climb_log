<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\BatchForceRecoutingSemiFinalResultGender;
use App\Admin\Actions\BatchForceRecoutingSemiFinalResultGroup;
use App\Admin\Actions\BatchGenerateResultSemiFinalParticipant;
use App\Admin\Actions\ResultRouteSemiFinalStage\BatchExportProtocolRouteParticipantSemiFinal;
use App\Admin\Actions\ResultRouteSemiFinalStage\BatchExportResultSemiFinal;
use App\Admin\Actions\ResultRouteSemiFinalStage\BatchResultSemiFinal;
use App\Admin\Actions\ResultRouteSemiFinalStage\BatchResultSemiFinalCustom;
use App\Admin\Actions\ResultRouteSemiFinalStage\BatchResultSemiFinalCustomFillOneRoute;
use App\Exports\SemiFinalProtocolCardsExport;
use App\Exports\SemiFinalResultExport;
use App\Models\Event;
use App\Models\ResultQualificationClassic;
use App\Models\ParticipantCategory;
use App\Models\ResultFinalStage;
use App\Models\ResultFranceSystemQualification;
use App\Models\ResultRouteFinalStage;
use App\Models\ResultRouteFranceSystemQualification;
use App\Models\ResultSemiFinalStage;
use App\Models\ResultRouteSemiFinalStage;
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
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class ResultRouteSemiFinalStageController extends Controller
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
            ->row(function(Row $row) {
                $event = Event::where('owner_id', '=', Admin::user()->id)
                    ->where('active', '=', 1)
                    ->where('is_semifinal', '=', 1)
                    ->first();
                if($event) {
                    $row->column(12, $this->grid());
                }
            });
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $result = ResultSemiFinalStage::find($id);
        ResultRouteSemiFinalStage::where('user_id', $result->user_id)->where('event_id', $result->event_id)->delete();
        ResultSemiFinalStage::where('user_id', $result->user_id)->where('event_id', $result->event_id)->delete();
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
        $grid = new Grid(new ResultSemiFinalStage());
        if (!Admin::user()->isAdministrator()){
            $grid->model()->where('owner_id', '=', Admin::user()->id);
        }
        $grid->model()->where(function ($query) {
            $query->has('event.result_semifinal_stage');
        });
        $grid->tools(function (Grid\Tools $tools) {
            $tools->append(new BatchExportResultSemiFinal);
//            $tools->append(new BatchResultSemiFinal);
            $event = Event::where('owner_id', '=', Admin::user()->id)->where('active', 1)->first();
            if($event->is_sort_group_semifinal){
                $categories = ParticipantCategory::whereIn('category', $event->categories)->where('event_id', $event->id)->get();
                foreach ($categories as $category){
                    $tools->append(new BatchResultSemiFinalCustomFillOneRoute($category));
                    $tools->append(new BatchResultSemiFinalCustom($category));
                }
            } else {
                $tools->append(new BatchResultSemiFinal);
            }
            $tools->append(new BatchForceRecoutingSemiFinalResultGroup);
            $tools->append(new BatchForceRecoutingSemiFinalResultGender);
            $tools->append(new BatchGenerateResultSemiFinalParticipant);
            $tools->append(new BatchExportProtocolRouteParticipantSemiFinal);
        });
        $grid->selector(function (Grid\Tools\Selector $selector) {
            $event = Event::where('owner_id', '=', Admin::user()->id)->where('active', 1)->first();
            if($event->is_sort_group_semifinal) {
                $selector->select('category_id', 'Категория', (new \App\Models\ParticipantCategory)->getUserCategory(Admin::user()->id));
            }
            $selector->select('gender', 'Пол', ['male' => 'Муж', 'female' => 'Жен']);
        });
        $grid->actions(function ($actions) {
            $actions->disableEdit();
//            $actions->disableDelete();
            $actions->disableView();
        });

        $grid->disableFilter();
        $grid->disableExport();
        $grid->disableCreateButton();
        $grid->disableColumnSelector();
        $grid->disablePagination();
        $grid->disablePerPageSelector();
        $grid->disableBatchActions();
        $grid->column('user.middlename', __('Участник'));
        $grid->column('user.gender', __('Пол'))->display(function ($gender) {
            return trans_choice('somewords.'.$gender, 10);
        });
        $event = Event::where('owner_id', '=', Admin::user()->id)->where('active', 1)->first();
        if($event->is_sort_group_semifinal) {
            $grid->column('category_id', 'Категория')->display(function ($category_id) {
                $owner_id = Admin::user()->id;
                $event = Event::where('owner_id', '=', $owner_id)
                    ->where('active', 1)->first();
                return ParticipantCategory::where('id', '=', $category_id)->where('event_id', $event->id)->first()->category;
            })->sortable();
        }
        $grid->column('place', __('Место'))->sortable();
        $grid->column('amount_top', __('Кол-во топов'));
        $grid->column('amount_try_top', __('Кол-во попыток на топ'));
        $grid->column('amount_zone', __('Кол-во зон'));
        $grid->column('amount_try_zone', __('Кол-во попыток на зону'));
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
        $show = new Show(ResultRouteSemiFinalStage::findOrFail($id));

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
        $form = new Form(new ResultRouteSemiFinalStage);

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
        $participant = ResultQualificationClassic::where('owner_id', '=', Admin::user()->id)
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
    public static function getUsersSorted($users, $fields, $model, $type, $owner_id): array
    {
        if (count($users->toArray()) == 0){
            return [];
        }
        $users_with_result = [];
        foreach ($users as $index => $user){
            switch ($type) {
                case 'final':
                    $result_user = ResultRouteFinalStage::where('owner_id', '=', $owner_id)
                        ->where('event_id', '=', $model->id)
                        ->where('user_id', '=', $user->id)
                        ->get();
                    break;
                case 'france_system_qualification':
                    $result_user = ResultRouteFranceSystemQualification::where('owner_id', '=', $owner_id)
                        ->where('event_id', '=', $model->id)
                        ->where('user_id', '=', $user->id)
                        ->get();
                    break;
                case 'semifinal':
                    $result_user = ResultRouteSemiFinalStage::where('owner_id', '=', $owner_id)
                        ->where('event_id', '=', $model->id)
                        ->where('user_id', '=', $user->id)
                        ->get();
            }
            $result = ResultRouteSemiFinalStage::merge_result_user_in_stage($result_user);
            if($result['amount_top'] !== null && $result['amount_try_top'] !== null && $result['amount_zone'] !== null && $result['amount_try_zone'] !== null){
                $users_with_result[$index] = collect($user->toArray())->except($fields);
                $users_with_result[$index]['result'] = $result;
                $users_with_result[$index]['place'] = null;
                $users_with_result[$index]['category_id'] = $result['category_id'];
                $users_with_result[$index]['owner_id'] = $owner_id;
                $users_with_result[$index]['user_id'] = $user->id;
                $users_with_result[$index]['event_id'] = $model->id;
                $users_with_result[$index]['gender'] = trans_choice('somewords.'.$user->gender, 10);
                $users_with_result[$index]['amount_top'] = $result['amount_top'];
                $users_with_result[$index]['amount_zone'] = $result['amount_zone'];
                $users_with_result[$index]['amount_try_top'] = $result['amount_try_top'];
                $users_with_result[$index]['amount_try_zone'] = $result['amount_try_zone'];
            }
        }
        $users_sorted = ResultQualificationClassic::counting_final_place($model->id, $users_with_result, $type);
//        $users_sorted = Participant::counting_final_place($model->id, $users_sorted, 'qualification');
        ### ПРОВЕРИТЬ НЕ СОХРАНЯЕМ ЛИ МЫ ДВА РАЗА ЗДЕСЬ И ПОСЛЕ КУДА ВОЗРАЩАЕТ $users_sorted
        foreach ($users_sorted as $index => $user){
            $fields = ['result'];
            $users_sorted[$index] = collect($user)->except($fields)->toArray();
            if($type == 'final' || $type == 'france_system_qualification'){
                if($type == 'france_system_qualification'){
                    $result = ResultFranceSystemQualification::where('user_id', '=', $users_sorted[$index]['user_id'])->where('event_id', '=', $model->id)->first();
                    if (!$result){
                        $result = new ResultFranceSystemQualification;
                    }
                } else {
                    $result = ResultFinalStage::where('user_id', '=', $users_sorted[$index]['user_id'])->where('event_id', '=', $model->id)->first();
                    if (!$result){
                        $result = new ResultFinalStage;
                    }
                }
            } else {
                $result = ResultSemiFinalStage::where('user_id', '=', $users_sorted[$index]['user_id'])->where('event_id', '=', $model->id)->first();
                if (!$result){
                    $result = new ResultSemiFinalStage;
                }
            }
            $category_id = ParticipantCategory::where('id', $users_sorted[$index]['category_id'])->where('event_id', $model->id)->first();
            if($category_id){
                $category_id = $category_id->id;
                $result->category_id = $category_id;
            } else {
                Log::error('It has not found category_id '.$users_sorted[$index]['category_id'].' '.$model->id);
            }
            $result->event_id = $users_sorted[$index]['event_id'];
            $result->user_id = $users_sorted[$index]['user_id'];
            $result->gender = trans_choice('somewords.'.$users_sorted[$index]['gender'], 10);
            $result->owner_id = $users_sorted[$index]['owner_id'];
            $result->amount_top = $users_sorted[$index]['amount_top'];
            $result->amount_zone = $users_sorted[$index]['amount_zone'];
            $result->amount_try_top = $users_sorted[$index]['amount_try_top'];
            $result->amount_try_zone = $users_sorted[$index]['amount_try_zone'];
            $result->place = $users_sorted[$index]['place'];


            $result->save();
        }
        return $users_sorted;
    }

    public function exportSemiFinalExcel(Request $request)
    {
        $file_name = 'Результаты полуфиналов.xlsx';
        $result = Excel::download(new SemiFinalResultExport($request->id), $file_name, \Maatwebsite\Excel\Excel::XLSX);
        return response()->download($result->getFile(), $file_name, [
            'Content-Type' => 'application/xlsx',
        ]);
    }
    public function exportSemiFinalCsv(Request $request)
    {
        $file_name = 'Результаты полуфиналов.csv';
        $result = Excel::download(new SemiFinalResultExport($request->id), $file_name, \Maatwebsite\Excel\Excel::CSV);
        return response()->download($result->getFile(), $file_name, [
            'Content-Type' => 'application/csv',
        ]);
    }
    public function exportSemiFinalOds(Request $request)
    {
        $file_name = 'Результаты полуфиналов.ods';
        $result = Excel::download(new SemiFinalResultExport($request->id), $file_name, \Maatwebsite\Excel\Excel::ODS);
        return response()->download($result->getFile(), $file_name, [
            'Content-Type' => 'application/ods',
        ]);
    }
    public function semifinalParticipantExcel(Request $request)
    {
        $file_name = 'Карточки полуфинала.xlsx';
        $result = Excel::download(new SemiFinalProtocolCardsExport($request->event_id), $file_name, \Maatwebsite\Excel\Excel::XLSX);
        return response()->download($result->getFile(), $file_name, [
            'Content-Type' => 'application/xlsx',
        ]);
    }

}
