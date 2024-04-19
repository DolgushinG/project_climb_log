<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\BatchForceRecouting;
use App\Admin\Actions\BatchGenerateParticipant;
use App\Admin\Actions\ResultQualification\BatchResultQualification;
use App\Admin\Actions\ResultRouteQualificationLikeFinalStage\BatchExportResultQualificationLikeFinal;
use App\Admin\Actions\ResultRouteQualificationLikeFinalStage\BatchResultQualificationLikeFinal;
use App\Admin\CustomAction\ActionExport;
use App\Admin\CustomAction\ActionRejectBill;
use App\Exports\ExportCardParticipant;
use App\Exports\QualificationLikeFinalResultExport;
use App\Exports\QualificationResultExport;
use App\Models\Event;
use App\Models\Grades;
use App\Models\Participant;
use App\Http\Controllers\Controller;
use App\Models\ParticipantCategory;
use App\Models\ResultFinalStage;
use App\Models\ResultParticipant;
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
use Encore\Admin\Widgets\Table;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Nette\Utils\Image;
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
                if($event) {
                    if($event->is_qualification_counting_like_final){
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
            ->body($this->form('create'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update($id, Request $request)
    {
        $type = 'edit';
        if($request->result_for_edit){
            $type = 'update';
        }
        return $this->form($type)->update($id);
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
            ->body($this->form('edit')->edit($id));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, Request $request)
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
        $event = Event::where('owner_id', '=', Admin::user()->id)->where('active', 1)->first();
        $grid->selector(function (Grid\Tools\Selector $selector) use ($event) {
            $category = ParticipantCategory::whereIn('category', $event->categories)->where('event_id', $event->id)->pluck('id')->toArray();
            $p_categories = Participant::where('event_id', $event->id)->whereIn('category_id', $category)->get();
            if($p_categories->isNotEmpty()){
                $selector->select('category_id', 'Категория', (new \App\Models\ParticipantCategory)->getUserCategory(Admin::user()->id));
            }
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
        $grid->actions(function ($actions) use ($event) {
//            $actions->disableEdit();
            $actions->append(new ActionRejectBill($actions->getKey(), $event->id));
            $actions->disableView();
            $actions->disableDelete();
        });
        $grid->column('user.middlename', __('Участник'));
        $grid->column('user.birthday', __('Дата Рождения'));
        $grid->column('user.gender', __('Пол'))->display(function ($gender) {
            return trans_choice('somewords.'.$gender, 10);
        });
        $category = ParticipantCategory::whereIn('category', $event->categories)->where('event_id', $event->id)->pluck('id')->toArray();
        $p_categories = Participant::where('event_id', $event->id)->whereIn('category_id', $category)->get();

        if($p_categories->isNotEmpty()){
            $grid->column('category_id', 'Категория')
                ->help('Если случается перенос, из одной категории в другую, необходимо обязательно пересчитать результаты')
                ->select((new \App\Models\ParticipantCategory)->getUserCategory(Admin::user()->id));
        }

        if(!$event->is_input_set){
            $grid->column('number_set_id', 'Номер сета')
                ->select(Set::getParticipantSets(Admin::user()->id));
        }
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
        if($event->is_need_pay_for_reg){
            $grid->column('is_paid', 'Оплата')->switch($states);
            \Encore\Admin\Admin::style('
                         img {
                            position: relative;
                            transition: transform 0.25s ease;
                        }

                        img:hover {
                            -webkit-transform: scale(5.5);
                            transform: scale(5.5);
                            position: absolute; /* или position: fixed; в зависимости от вашего предпочтения */
                            z-index: 9999;
                        }

            ');
            $grid->column('bill', 'Чек участника')->image('',100, 100);
        }
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
        $event = Event::where('owner_id', '=', Admin::user()->id)->where('active', 1)->first();
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
        if($event->is_need_pay_for_reg){
            $grid->column('is_paid', 'Оплата')->switch($states);

            \Encore\Admin\Admin::style('
                        @media only screen and (min-width: 1025px) {
                                         img {
                                        position: relative;
                                        transition: transform 0.25s ease;
        //                             transform-origin: center center;
                                }
                                img:hover {
                                    -webkit-transform: scale(5.5);
                                    transform: scale(5.5);
                                    margin-top: -50px; /* половина высоты изображения */
                                    margin-left: -50px; /* половина ширины изображения */
                                    z-index: 9999;
                                    position: absolute; /* или position: fixed; в зависимости от вашего предпочтения */
                                    z-index: 9999;
                                }
                        }

            ');
            $grid->column('bill', 'Чек участника')->image('',100, 100);
        }
        return $grid;
    }
    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form($type)
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
            Admin::style(".remove.btn.btn-warning.btn-sm.pull-right {
                display: None;
                }
                .add.btn.btn-success.btn-sm {
                display: None;
                }
                .input-group-addon{
                display: None;
                }
            ");

//            $form->hidden('owner_id')->value(Admin::user()->id);
//            $form->hidden('event_id');
//            $form->hidden('number_set', 'number_set');
//            $form->hidden('category_id', 'category_id');
//            $form->hidden('active', 'active');
//            $form->hidden('is_paid', 'is_paid');
            $form->tools(function (Form\Tools $tools) {
                $tools->disableList();
                $tools->disableDelete();
                $tools->disableView();
            });
            $form->footer(function ($footer) {
                $footer->disableReset();
                $footer->disableViewCheck();
                $footer->disableEditingCheck();
                $footer->disableCreatingCheck();
            });
            $form->table('result_for_edit', 'Таблица результата', function ($table){
                $table->text('route_id', 'Номер маршрут')->readonly();
                $table->text('grade', 'Категория')->readonly();
                $table->select('attempt', 'Результат')->attribute('inputmode', 'none')->options([1 => 'FLASH', 2 => 'REDPOINT', 0 => 'Не пролез'])->width('50px');
            });
        }
        $form->saving(function (Form $form) use ($type){
            if($type == 'update'){
              $user_id = $form->model()->first()->user_id;
              $event_id = $form->model()->first()->event_id;
              $routes = $form->result_for_edit;
              foreach ($routes as $route){
                  $result = ResultParticipant::where('user_id', $user_id)->where('event_id', $event_id)->where('route_id', $route['route_id'])->first();
                  $result->attempt = $route['attempt'];
                  $result->save();
              }
              Event::refresh_final_points_all_participant($form->model()->first()->event_id);
            }
        });
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


    public function rejectBill(Request $request)
    {
        $event = Event::find($request->event_id);
        if($event->is_qualification_counting_like_final){
            $participant = ResultQualificationLikeFinal::find($request->id);
        } else {
            $participant = Participant::find($request->id);
        }
        $participant->bill = null;
        $participant->save();
    }

}
