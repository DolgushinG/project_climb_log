<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\BatchForceRecouting;
use App\Admin\Actions\BatchGenerateParticipant;
use App\Admin\Actions\ResultQualification\BatchResultQualification;
use App\Admin\Actions\ResultRouteQualificationLikeFinalStage\BatchExportResultQualificationLikeFinal;
use App\Admin\Actions\ResultRouteQualificationLikeFinalStage\BatchResultQualificationLikeFinal;
use App\Exports\ExportCardParticipant;
use App\Exports\QualificationLikeFinalResultExport;
use App\Exports\QualificationResultExport;
use App\Models\Event;
use App\Models\Participant;
use App\Http\Controllers\Controller;
use App\Models\ParticipantCategory;
use App\Models\ResultFinalStage;
use App\Models\ResultQualificationLikeFinal;
use App\Models\ResultRouteQualificationLikeFinal;
use App\Models\Set;
use App\Models\User;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use function Symfony\Component\String\s;

class ParticipantsController extends Controller
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
                $event = Event::where('owner_id', '=', Admin::user()->id)->where('active', '=', 1)->first();
                $fields = ['firstname','id','category','active','team','city', 'email','year','lastname','skill','sport_category','email_verified_at', 'created_at', 'updated_at'];
                if($event) {
                    if($event->is_qualification_counting_like_final){
                        if($event->is_additional_final) {
                            $all_group_participants = array();
                            $all_users = array();
                            $users = array();
                            foreach ($event->categories as $category) {
                                $category_id = ParticipantCategory::where('category', $category)->where('event_id', $event->id)->first()->id;
                                $part_nt = ResultRouteQualificationLikeFinal::where('event_id', '=', $event->id)->where('category_id', $category_id)->distinct()->pluck('user_id');
                                $all_group_participants['male'][$category] = User::whereIn('id', $part_nt)->where('gender','=', 'male')->get();
                                $all_group_participants['female'][$category] = User::whereIn('id', $part_nt)->where('gender','=', 'female')->get();
                            }
                            foreach ($all_group_participants as $group_participants) {
                                foreach ($group_participants as $participants) {
                                    $user = ResultRouteSemiFinalStageController::getUsersSorted($participants, $fields, $event, 'qualification_like_final', Admin::user()->id);
                                    if ($user !== []) {
                                        $users[] = $user;
                                    }
                                }
                            }
                            foreach ($users as $user) {
                                foreach ($user as $a) {
                                    $all_users[] = $a;
                                }
                            }
                            foreach ($all_users as $index => $user){
                                $fields = ['middlename', 'avatar','telegram_id','yandex_id','vkontakte_id'];
                                $all_users[$index] = collect($user)->except($fields)->toArray();

                                $final_result_stage = ResultFinalStage::where('event_id', '=', $all_users[$index]['event_id'])->where('user_id', '=', $all_users[$index]['user_id'])->first();
                                if(!$final_result_stage){
                                    $final_result_stage = new ResultFinalStage;
                                }
                                $category_id = ParticipantCategory::where('id', $all_users[$index]['category_id'])->where('event_id', $event->id)->first()->id;
                                $final_result_stage->event_id = $all_users[$index]['event_id'];
                                $final_result_stage->user_id = $all_users[$index]['user_id'];
                                $final_result_stage->gender = trans_choice('somewords.'.$all_users[$index]['gender'], 10);;
                                $final_result_stage->category_id = $category_id;
                                $final_result_stage->owner_id = $all_users[$index]['owner_id'];
                                $final_result_stage->amount_top = $all_users[$index]['amount_top'];
                                $final_result_stage->amount_zone = $all_users[$index]['amount_zone'];
                                $final_result_stage->amount_try_top = $all_users[$index]['amount_try_top'];
                                $final_result_stage->amount_try_zone = $all_users[$index]['amount_try_zone'];
                                $final_result_stage->place = $all_users[$index]['place'];
                                $final_result_stage->save();
                            }
                        } else {
                            $participant_users_id = ResultQualificationLikeFinal::where('event_id', '=', $event->id)->pluck('user_id')->toArray();
                            $participants_female = User::whereIn('id', $participant_users_id)->where('gender', 'female')->get();
                            $participants_male = User::whereIn('id', $participant_users_id)->where('gender', 'male')->get();
                            ResultRouteSemiFinalStageController::getUsersSorted($participants_female, $fields, $event, 'qualification_like_final', Admin::user()->id);
                            ResultRouteSemiFinalStageController::getUsersSorted($participants_male, $fields, $event, 'qualification_like_final', Admin::user()->id);
                        }
                        $row->column(20, $this->qualification_counting_like_final());
                    } else {
                        $row->column(20, $this->qualification_classic());
                    }
                }
            });
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
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $event = Event::where('owner_id', '=', Admin::user()->id)->where('active', '=', 1)->first();
        if ($event->is_qualification_counting_like_final) {
            $result = ResultQualificationLikeFinal::find($id);
            ResultRouteQualificationLikeFinal::where('user_id', $result->user_id)->where('event_id', $result->event_id)->delete();
            $model = ResultQualificationLikeFinal::where('user_id', $result->user_id)->where('event_id', $result->event_id)->first();
            $model->amount_top = null;
            $model->amount_try_top = null;
            $model->amount_zone = null;
            $model->amount_try_zone = null;
            $model->save();
        }
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function qualification_classic()
    {
        $grid = new Grid(new Participant);
        if (!Admin::user()->isAdministrator()){
            $grid->model()->where('owner_id', '=', Admin::user()->id);
        }
        $grid->model()->where(function ($query) {
            $query->has('event.participant');
        });
        $grid->selector(function (Grid\Tools\Selector $selector) {
            $selector->select('category_id', 'Категория', (new \App\Models\ParticipantCategory)->getUserCategory(Admin::user()->id));
            $selector->select('gender', 'Пол', ['male' => 'Муж', 'female' => 'Жен']);
            $selector->select('active', 'Кто добавил', [ 1 => 'Добавил',  0 => 'Не добавил']);
            $selector->select('is_paid', 'Есть оплата', [ 1 => 'Да',  0 => 'Нет']);
        });
        $grid->disableBatchActions();
        $grid->disableFilter();
        $grid->disableExport();
        $grid->disableCreateButton();
        $grid->disableColumnSelector();
        $grid->tools(function (Grid\Tools $tools) {
            $tools->append(new BatchResultQualification);
            $tools->append(new BatchForceRecouting);
            $tools->append(new BatchGenerateParticipant);
        });
        $grid->actions(function ($actions) {
            $actions->disableEdit();
            $actions->disableView();
            $actions->disableDelete();
        });
        $grid->column('user.middlename', __('Участник'));
        $grid->column('user.birthday', __('Дата Рождения'));
        $grid->column('user.gender', __('Пол'))->display(function ($gender) {
            return trans_choice('somewords.'.$gender, 10);
        });
        $grid->column('category_id', 'Категория')
            ->help('Если случается перенос, из одной категории в другую, необходимо обязательно пересчитать результаты')
            ->select((new \App\Models\ParticipantCategory)->getUserCategory(Admin::user()->id));
        $grid->column('number_set_id', 'Номер сета')
            ->select(Set::getParticipantSets(Admin::user()->id));
        $grid->column('user_place', 'Место в квалификации')
            ->help('При некорректном раставлением мест, необходимо пересчитать результаты')
            ->sortable();
        $grid->column('points', 'Баллы')->sortable();
        $grid->column('active', 'Статус')->using([0 => 'Не внес', 1 => 'Внес'])->display(function ($title, $column) {
            If ($this->active == 0) {
                return $column->label('default');
            } else {
                return $column->label('success');
            }
        });
        $states = [
            'on' => ['value' => 1, 'text' => 'Да', 'color' => 'success'],
            'off' => ['value' => 0, 'text' => 'Нет', 'color' => 'default'],
        ];
        $grid->column('is_paid', 'Оплата')->switch($states);
        return $grid;
    }


    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function qualification_counting_like_final()
    {
        $grid = new Grid(new ResultQualificationLikeFinal);
        if (!Admin::user()->isAdministrator()){
            $grid->model()->where('owner_id', '=', Admin::user()->id);
        }
        $grid->model()->where(function ($query) {
            $query->has('event.result_qualification_like_final');
        });
        $grid->selector(function (Grid\Tools\Selector $selector) {
            $selector->select('category_id', 'Категория', (new \App\Models\ParticipantCategory)->getUserCategory(Admin::user()->id));
            $selector->select('gender', 'Пол', ['male' => 'Муж', 'female' => 'Жен']);
            $selector->select('is_paid', 'Есть оплата', [ 1 => 'Да',  0 => 'Нет']);
        });
        $grid->tools(function (Grid\Tools $tools) {
            $tools->append(new BatchExportResultQualificationLikeFinal);
            $tools->append(new BatchResultQualificationLikeFinal);
            $tools->append(new BatchGenerateParticipant);
        });
        $grid->actions(function ($actions) {
            $actions->disableEdit();
//            $actions->disableDelete();
            $actions->disableView();
        });

        $grid->disableExport();
        $grid->disableFilter();
        $grid->disableCreateButton();
        $grid->disableColumnSelector();
        $grid->disablePagination();
        $grid->disablePerPageSelector();
        $grid->column('user.middlename', __('Участник'));
        $grid->column('user.gender', __('Пол'))->display(function ($gender) {
            return trans_choice('somewords.'.$gender, 10);
        });
        $grid->column('number_set_id', 'Номер сета')
            ->select(Participant::number_sets(Admin::user()->id));
        $grid->column('category_id', 'Категория')
            ->help('Если случается перенос, из одной категории в другую, необходимо обязательно пересчитать результаты')
            ->select((new \App\Models\ParticipantCategory)->getUserCategory(Admin::user()->id));
        $grid->column('place', __('Место'))->sortable();
        $grid->column('amount_top', __('Кол-во топов'));
        $grid->column('amount_try_top', __('Кол-во попыток на топ'));
        $grid->column('amount_zone', __('Кол-во зон'));
        $grid->column('amount_try_zone', __('Кол-во попыток на зону'));
        $states = [
            'on' => ['value' => 1, 'text' => 'Да', 'color' => 'success'],
            'off' => ['value' => 0, 'text' => 'Нет', 'color' => 'default'],
        ];
        $grid->column('is_paid', 'Оплата')->switch($states);
        return $grid;
    }
    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {

        $event = Event::where('owner_id', '=', Admin::user()->id)->where('active', '=', 1)->first();
        if($event->is_qualification_counting_like_final){
            $form = new Form(new ResultQualificationLikeFinal);
            $form->display('ID');
            $form->hidden('owner_id')->value(Admin::user()->id);
            $form->text('event_id', 'event_id');
            $form->text('user_id', 'user_id');
            $form->text('route_id', 'final_route_id');
            $form->text('amount_try_top', 'amount_try_top');
            $form->text('amount_try_zone', 'amount_try_zone');
            $form->hidden('amount_zone', 'amount_zone');
            $form->hidden('amount_top', 'amount_top');
            $form->display(trans('admin.created_at'));
            $form->display(trans('admin.updated_at'));
            $form->text('number_set_id', 'number_set');
            $form->text('category_id', 'category_id');
            $form->switch('active', 'active');
            $form->switch('is_paid', 'is_paid');
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
        } else {
            $form = new Form(new Participant);
            $form->display('ID');
            $form->hidden('owner_id')->value(Admin::user()->id);
            $form->text('event_id');
            $form->text('number_set', 'number_set');
            $form->text('category_id', 'category_id');
            $form->switch('active', 'active');
            $form->switch('is_paid', 'is_paid');
            $form->display(trans('admin.created_at'));
            $form->display(trans('admin.updated_at'));
        }

        return $form;
    }
    public function exportQualificationExcel(Request $request)
    {
        $file_name = 'Результаты квалификации.xlsx';
        $result = Excel::download(new QualificationResultExport($request->id), $file_name, \Maatwebsite\Excel\Excel::XLSX);
        return response()->download($result->getFile(), $file_name, [
            'Content-Type' => 'application/xlsx',
        ]);
    }
    public function exportQualificationLikeFinalExcel(Request $request)
    {
        $file_name = 'Результаты квалификации.xlsx';
        $result = Excel::download(new QualificationLikeFinalResultExport($request->id), $file_name, \Maatwebsite\Excel\Excel::XLSX);
        return response()->download($result->getFile(), $file_name, [
            'Content-Type' => 'application/xlsx',
        ]);
    }
    public function cardParticipantExcel(Request $request)
    {
        $file_name = 'Карточка участника с трассами.xlsx';
        $result = Excel::download(new ExportCardParticipant($request->id), $file_name, \Maatwebsite\Excel\Excel::XLSX);
        return response()->download($result->getFile(), $file_name, [
            'Content-Type' => 'application/xlsx',
        ]);
    }
    public function exportQualificationCsv(Request $request)
    {
        $file_name = 'Результаты квалификации.csv';
        $result = Excel::download(new QualificationResultExport($request->id), $file_name, \Maatwebsite\Excel\Excel::CSV);
        return response()->download($result->getFile(), $file_name, [
            'Content-Type' => 'application/csv',
        ]);
    }
    public function exportQualificationOds(Request $request)
    {
        $file_name = 'Результаты квалификации.ods';
        $result = Excel::download(new QualificationResultExport($request->id), $file_name, \Maatwebsite\Excel\Excel::ODS);
        return response()->download($result->getFile(), $file_name, [
            'Content-Type' => 'application/ods',
        ]);
    }

    protected function getUsers($event_id)
    {
        $participant = Participant::where('owner_id', '=', Admin::user()->id)
            ->where('event_id', '=', $event_id)
            ->where('active', '=', 1)
            ->pluck('user_id')->toArray();
        return User::whereIn('id', $participant)->pluck('middlename', 'id');
    }


}
