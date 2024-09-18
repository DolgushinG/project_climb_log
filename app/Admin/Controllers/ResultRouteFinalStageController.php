<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\BatchForceRecoutingResultFinalGender;
use App\Admin\Actions\BatchForceRecoutingResultFinalGroup;
use App\Admin\Actions\BatchGenerateResultFinalParticipant;
use App\Admin\Actions\ResultRouteFinalStage\BatchExportProtocolRouteParticipantFinal;
use App\Admin\Actions\ResultRouteFinalStage\BatchExportResultFinal;
use App\Admin\Actions\ResultRouteFinalStage\BatchResultFinal;
use App\Admin\Actions\ResultRouteFinalStage\BatchResultFinalCustomFillOneRoute;
use App\Admin\Actions\ResultRouteFinalStage\BatchResultFinalCustom;
use App\Exports\FinalProtocolCardsExport;
use App\Exports\FinalResultExport;
use App\Helpers\Helpers;
use App\Models\Event;
use App\Models\Grades;
use App\Models\ParticipantCategory;
use App\Models\ResultFinalStage;
use App\Models\ResultRouteFinalStage;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Show;
use Illuminate\Http\Request;
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
            ->row(function(Row $row) {
                $event = Event::where('owner_id', '=', Admin::user()->id)->where('active', 1)->first();
                if ($event) {
                    $row->column(12, $this->grid2());
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
            ->body($this->form('edit', $id)->edit($id));
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
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid2()
    {
        $grid = new Grid(new ResultFinalStage);
        if (!Admin::user()->isAdministrator()){
            $grid->model()->where('owner_id', '=', Admin::user()->id);
        }
        $grid->model()->where(function ($query) {
            $query->has('event.result_final_stage');
        });
        $grid->tools(function (Grid\Tools $tools) {
            $tools->append(new BatchExportResultFinal);
            $event = Event::where('owner_id', '=', Admin::user()->id)->where('active', 1)->first();
            if($event->is_sort_group_final){
                $categories = ParticipantCategory::whereIn('category', $event->categories)->where('event_id', $event->id)->get();
                foreach ($categories as $index => $category){
                    $index = $index + 1;
                    $script_one_route = <<<EOT
                             $(document).on("click", '[modal="app-admin-actions-resultroutefinalstage-batchresultfinalcustomfilloneroute-$index"]', function () {
                                            const allAttemptsInput = document.getElementById('all_attempts-$category->id');
                                            const incrementBtn = document.getElementById('increment-btn');
                                            const decrementBtn = document.getElementById('decrement-btn');
                                            if (!incrementBtn && !decrementBtn) {
                                                const inputGroupAppend = document.createElement('div');
                                                inputGroupAppend.className = 'input-group-append';
                                                const newIncrementBtn = document.createElement('button');
                                                newIncrementBtn.type = 'button';
                                                newIncrementBtn.className = 'btn btn-outline-secondary';
                                                newIncrementBtn.id = 'increment-btn';
                                                const incrementIcon = document.createElement('i');
                                                incrementIcon.className = 'fa fa-plus';
                                                const incrementText = document.createElement('span');
                                                incrementText.textContent = ' Попытка'; // Текст "Попытка"
                                                newIncrementBtn.appendChild(incrementIcon);
                                                newIncrementBtn.appendChild(incrementText);
                                                const newDecrementBtn = document.createElement('button');
                                                newDecrementBtn.type = 'button';
                                                newDecrementBtn.className = 'btn btn-danger';
                                                newDecrementBtn.id = 'decrement-btn';
                                                const decrementIcon = document.createElement('i');
                                                decrementIcon.className = 'fa fa-minus';
                                                newDecrementBtn.appendChild(decrementIcon);
                                                inputGroupAppend.appendChild(newDecrementBtn);
                                                inputGroupAppend.appendChild(newIncrementBtn);
                                                allAttemptsInput.parentNode.appendChild(inputGroupAppend);
                                                newIncrementBtn.addEventListener('click', function () {
                                                    let currentValue = parseInt(allAttemptsInput.value) || 0;
                                                    allAttemptsInput.value = currentValue + 1;
                                                });
                                                newDecrementBtn.addEventListener('click', function () {
                                                    let currentValue = parseInt(allAttemptsInput.value) || 0;
                                                    if (currentValue > 0) {
                                                        allAttemptsInput.value = currentValue - 1;
                                                    }
                                                });
                                                $('[data-all-attempts-id{$category->id}=all-attempts]').val('');
                                                $('[data-amount_try_top{$category->id}=amount_try_top]').val('');
                                                $('[data-amount_try_zone{$category->id}=amount_try_top]').val('');
                                                $('[data-user-id{$category->id}=user_id]').val('');
                                            }
                                    });
                                        $(document).on("change", '[data-user-id{$category->id}=user_id]', function () {
                                    var routeId = $('[data-final-route-id{$category->id}=final_route_id]').val(); // ID выбранного маршрута
                                    var userId = $('[data-user-id{$category->id}="user_id"]').select2('val')
                                    var eventId = $('[data-event-id{$category->id}=event_id]').val(); // ID выбранного участника
                                    if(routeId){
                                        $.get("/admin/api/final/get_attempts", // URL эндпоинта
                                            {
                                                route_id: routeId,
                                                user_id: userId,
                                                event_id: eventId
                                            }, // Передаем ID маршрута и участника в запросе
                                            function (data) {
                                                // Обновляем поля с количеством попыток
                                                $('[id=amount_try_top]').val(data.amount_try_top);
                                                $('[id=amount_try_zone]').val(data.amount_try_zone);
                                                $('[data-all-attempts-id{$category->id}=all-attempts]').val(data.all_attempts);
                                            }
                                        );
                                    }
                                });
                                $(document).on("change", '[data-final-route-id{$category->id}=final_route_id]', function () {
                                    var routeId = $(this).val(); // ID выбранного маршрута
                                    var userId = $('[data-user-id{$category->id}="user_id"]').select2('val')
                                    var eventId = $('[data-event-id{$category->id}=event_id]').val(); // ID выбранного участника
                                    $.get("/admin/api/final/get_attempts", // URL эндпоинта
                                        {
                                            route_id: routeId,
                                            user_id: userId,
                                            event_id: eventId
                                        },
                                        function (data) {
                                            // Обновляем поля с количеством попыток
                                            $('[data-all-attempts-id{$category->id}=all-attempts]').val(data.all_attempts);
                                            $('[id=amount_try_top]').val(data.amount_try_top);
                                            $('[id=amount_try_zone]').val(data.amount_try_zone);
                                        }
                                    );
                                });
                                let btn_close_modal_one_route_$category->id = '[id="app-admin-actions-resultroutefinalstage-batchresultfinalcustomfilloneroute-$index"] [data-dismiss="modal"][class="btn btn-default"]'
                                $(document).on("click", btn_close_modal_one_route_$category->id, function () {
                                    window.location.reload();
                                });
                        EOT;
                    $script_custom = <<<EOT
                        let btn_close_modal_custom{$category->id} = '[id="app-admin-actions-resultroutefinalstage-batchresultfinalcustom-{$index}"] [data-dismiss="modal"][class="btn btn-default"]'
                        $(document).on("click", btn_close_modal_custom{$category->id}, function () {
                            window.location.reload();
                        });
                    EOT;
                    $tools->append(new BatchResultFinalCustomFillOneRoute($category, $script_one_route));
                    $tools->append(new BatchResultFinalCustom($category, $script_custom));
                }
            } else {
                $tools->append(new BatchResultFinal);
            }
            $tools->append(new BatchForceRecoutingResultFinalGender);
            $tools->append(new BatchForceRecoutingResultFinalGroup);
            if(Admin::user()->username == "Tester2"){
                $tools->append(new BatchGenerateResultFinalParticipant);
            }
            $tools->append(new BatchExportProtocolRouteParticipantFinal);
        });
        $grid->selector(function (Grid\Tools\Selector $selector) {
            $event = Event::where('owner_id', '=', Admin::user()->id)->where('active', 1)->first();

            if($event->is_sort_group_final) {
                $selector->select('category_id', 'Категория', (new \App\Models\ParticipantCategory)->getUserCategory(Admin::user()->id));
            }
            $selector->select('gender', 'Пол', ['male' => 'Муж', 'female' => 'Жен']);
        });
        $grid->actions(function ($actions) {
            if(Admin::user()->is_delete_result == 0){
                $actions->disableDelete();
            }
//            $actions->disableEdit();
            $actions->disableView();
        });
        $grid->disableBatchActions();
        $grid->disableFilter();
        $grid->disableExport();
        $grid->disableCreateButton();
        $grid->disableColumnSelector();
        $grid->disablePagination();
        $grid->disablePerPageSelector();
        $grid->column('user.middlename', __('Участник'));
        $grid->column('user.gender', __('Пол'))->display(function ($gender) {
            return trans_choice('somewords.'.$gender, 10);
        });
        $event = Event::where('owner_id', '=', Admin::user()->id)->where('active', 1)->first();
        if($event->is_sort_group_final) {
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
     * Update the specified resource in storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update($id)
    {
        return $this->form('update', $id)->update($id);
    }
    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(ResultFinalStage::findOrFail($id));

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
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $result = ResultFinalStage::find($id);
        ResultRouteFinalStage::where('user_id', $result->user_id)->where('event_id', $result->event_id)->delete();
        ResultFinalStage::where('user_id', $result->user_id)->where('event_id', $result->event_id)->delete();
    }


    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form($type, $id = null)
    {
        $form = new Form(new ResultFinalStage());
        $event = Event::where('owner_id', '=', Admin::user()->id)->where('active', '=', 1)->first();
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
        $count = Grades::where('event_id', $event->id)->first()->count_routes;
        $arr = array();
        for($i = 1; $i <= $count; $i++){
            $arr[] = ['Номер маршрута' => $i, 'Попытки на топ' => 0, 'Попытки на зону' => 0];
        }
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
        $form->table('result_for_edit_final', 'Таблица результата', function ($table) use ($event){
            $table->text('Номер маршрута')->readonly();
            $table->number('Попытки на топ')->required();
            $table->number('Попытки на зону')->required();
        })->value($arr);

        $form->saving(function (Form $form) use ($type, $id) {
            if($form->result_for_edit_final){
                $user_id = $form->model()->find($id)->user_id;
                $event_id = $form->model()->find($id)->event_id;
                $routes = $form->result_for_edit_final;
                foreach ($routes as $route) {
                    $result = ResultRouteFinalStage::where('user_id', $user_id)->where('event_id', $event_id)->where('final_route_id', $route['Номер маршрута'])->first();
                    if (intval($route['Попытки на топ']) > 0) {
                        $amount_top = 1;
                    } else {
                        $amount_top = 0;
                    }
                    if (intval($route['Попытки на зону']) > 0) {
                        $amount_zone = 1;
                    } else {
                        $amount_zone = 0;
                    }
                    $result->amount_try_top = intval($route['Попытки на топ']);
                    $result->amount_top = $amount_top;
                    $result->amount_zone = $amount_zone;
                    $result->amount_try_zone = intval($route['Попытки на зону']);
                    $result->save();
                }
                Event::refresh_final_points_all_participant_in_final($event_id);
            }
        });
        return $form;
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
    public function finalParticipantExcel(Request $request)
    {
        $file_name = 'Карточка финала.xlsx';
        $result = Excel::download(new FinalProtocolCardsExport($request->event_id), $file_name, \Maatwebsite\Excel\Excel::XLSX);
        return response()->download($result->getFile(), $file_name, [
            'Content-Type' => 'application/xlsx',
        ]);
    }

}
