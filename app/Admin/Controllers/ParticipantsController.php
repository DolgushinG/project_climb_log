<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\BatchForceRecouting;
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
use App\Models\ResultQualificationLikeFinal;
use App\Models\ResultRouteQualificationLikeFinal;
use App\Models\User;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Show;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

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
                if($event) {
                    if($event->is_qualification_counting_like_final){
                        $fields = ['firstname','id','category','active','team','city', 'email','year','lastname','skill','sport_category','email_verified_at', 'created_at', 'updated_at'];
                        $participant_users_id = Participant::where('event_id', '=', $event->id)->pluck('user_id')->toArray();
                        $participants = User::whereIn('id', $participant_users_id)->get();
                        ResultRouteSemiFinalStageController::getUsersSorted($participants, $fields, $event, 'qualification_like_final', Admin::user()->id);
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
            ResultQualificationLikeFinal::where('user_id', $result->user_id)->where('event_id', $result->event_id)->delete();
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
            $selector->select('active', 'Кто добавил', [ 1 => 'Добавил',  0 => 'Не добавил']);
            $selector->select('is_paid', 'Кто оплатил', [ 1 => 'Да',  0 => 'Нет']);
        });
        $grid->disableBatchActions();
        $grid->disableExport();
        $grid->disableCreateButton();
        $grid->disableColumnSelector();
        $grid->tools(function (Grid\Tools $tools) {
            $tools->append(new BatchResultQualification);
            $tools->append(new BatchForceRecouting);
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
        $grid->column('number_set', 'Номер сета')->editable();
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
    protected function qualification_counting_like_final()
    {
        $grid = new Grid(new ResultQualificationLikeFinal);
        if (!Admin::user()->isAdministrator()){
            $grid->model()->where('owner_id', '=', Admin::user()->id);
        }
        $grid->model()->where(function ($query) {
            $query->has('event.result_qualification_like_final');
        });
        $grid->tools(function (Grid\Tools $tools) {
            $tools->append(new BatchExportResultQualificationLikeFinal);
            $tools->append(new BatchResultQualificationLikeFinal);
        });
        $grid->actions(function ($actions) {
            $actions->disableEdit();
//            $actions->disableDelete();
            $actions->disableView();
        });

        $grid->disableExport();
        $grid->disableCreateButton();
        $grid->disableColumnSelector();
        $grid->disablePagination();
        $grid->disablePerPageSelector();
        $grid->column('user.middlename', __('Участник'));
        $grid->column('user.gender', __('Пол'))->display(function ($gender) {
            return trans_choice('somewords.'.$gender, 10);
        });
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
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $event = Event::where('owner_id', '=', Admin::user()->id)->where('active', '=', 1)->first();
        if($event->is_qualification_counting_like_final){
            $form = new Form(new ResultRouteQualificationLikeFinal);
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
            $form->text('category_id', 'number_set');
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
