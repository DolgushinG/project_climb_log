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
use App\Helpers\Helpers;
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
            ->body($this->form('edit', $id)->edit($id));
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
            $event = Event::where('owner_id', '=', Admin::user()->id)->where('active', 1)->first();
            if($event->is_sort_group_semifinal){
                $categories = ParticipantCategory::whereIn('category', $event->categories)->where('event_id', $event->id)->get();
                foreach ($categories as $index => $category){
                    $index = $index + 1;
                    $script_one_route = <<<EOT
                            function set_attempt_$category->id(){
                                var routeId = $('[data-semifinal-route-id-$category->id=final_route_id]').val(); // ID выбранного маршрута
                                var userId = $('[data-semifinal-user-id-$category->id="user_id"]').select2('val')
                                var eventId = $('[data-semifinal-event-id-$category->id=event_id]').val(); // ID выбранного участника
                                var attempt = $('[data-all-attempts-id-$category->id=all-attempts]').val();
                                var amount_try_top = $('[id=amount_try_top_$category->id]').val();
                                var amount_try_zone = $('[id=amount_try_zone_$category->id]').val();
                                if(routeId){
                                    $.get("/admin/api/semifinal/set_attempts",
                                        {
                                            route_id: routeId,
                                            user_id: userId,
                                            event_id: eventId,
                                            attempt: attempt,
                                            amount_try_top: amount_try_top,
                                            amount_try_zone: amount_try_zone
                                        },
                                        function (data) {
                                            $('[data-amount_try_top-$category->id=amount_try_top]').val(data.amount_try_top);
                                            $('[data-amount_try_zone-$category->id=amount_try_zone]').val(data.amount_try_zone);
                                            $('[data-all-attempts-id-$category->id=all-attempts]').val(data.all_attempts);
                                        }
                                    );
                                }
                            }
                         $(document).on("click", '[modal="app-admin-actions-resultroutesemifinalstage-batchresultsemifinalcustomfilloneroute-$index"]', function () {
                            const allAttemptsInput = document.getElementById('all_attempts-$category->id');
                            const incrementBtn = document.getElementById('increment-btn-$category->id');
                            const decrementBtn = document.getElementById('decrement-btn-$category->id');
                            const topInput = document.getElementById('amount_try_top_$category->id');
                            const zoneInput = document.getElementById('amount_try_zone_$category->id');
                            const zoneBtn = document.getElementById('zone-btn-final-$category->id');
                            const topBtn = document.getElementById('top-btn-final-$category->id');

                                if (!incrementBtn && !decrementBtn && !zoneBtn && !topBtn) {
                                    const inputGroupAppend = document.createElement('div');
                                    const inputGroupAppend2 = document.createElement('div');
                                    const inputGroupAppend3 = document.createElement('div');
                                    inputGroupAppend.className = 'input-group-append';
                                    inputGroupAppend2.className = 'input-group-append';
                                    inputGroupAppend3.className = 'input-group-append';

                                    // Создаем кнопку для увеличения
                                    const newIncrementBtn = document.createElement('button');
                                    newIncrementBtn.type = 'button';
                                    newIncrementBtn.className = 'btn btn-warning';
                                    newIncrementBtn.id = 'increment-btn-$category->id';

                                    const newZoneBtn = document.createElement('button');
                                    newZoneBtn.type = 'button';
                                    newZoneBtn.className = 'btn btn-success';
                                    newZoneBtn.id = 'zone-btn-final-$category->id';
                                    const newTopBtn = document.createElement('button');
                                    newTopBtn.type = 'button';
                                    newTopBtn.className = 'btn btn-success';
                                    newTopBtn.id = 'top-btn-final-$category->id';
                                    // Создаем иконку для увеличения
                                    const incrementIcon = document.createElement('i');
                                    incrementIcon.className = 'fa fa-plus';

                                    // Создаем текст для увеличения
                                    const incrementText = document.createElement('span');
                                    const zoneText = document.createElement('span');
                                    const topText = document.createElement('span');
                                    zoneText.textContent = 'Зона'; // Текст "Попытка"
                                    incrementText.textContent = ' Попытка'; // Текст "Попытка"
                                    topText.textContent = 'Топ'; // Текст "Попытка"

                                    // Добавляем иконку и текст в кнопку увеличения
                                    newIncrementBtn.appendChild(incrementIcon);
                                    newIncrementBtn.appendChild(incrementText);
                                    newZoneBtn.appendChild(zoneText);
                                    newTopBtn.appendChild(topText);

                                    // Создаем кнопку для удаления
                                    const newDecrementBtn = document.createElement('button');
                                    newDecrementBtn.type = 'button';
                                    newDecrementBtn.className = 'btn btn-danger';
                                    newDecrementBtn.id = 'decrement-btn-$category->id';

                                    // Создаем иконку для удаления
                                    const decrementIcon = document.createElement('i');
                                    decrementIcon.className = 'fa fa-minus';

                                    // Добавляем иконку в кнопку удаления
                                    newDecrementBtn.appendChild(decrementIcon);

                                    // Добавляем кнопки в группу ввода
                                    inputGroupAppend.appendChild(newDecrementBtn);
                                    inputGroupAppend.appendChild(newIncrementBtn);
                                    inputGroupAppend2.appendChild(newZoneBtn);
                                    inputGroupAppend3.appendChild(newTopBtn);

                                    // Находим родительский элемент и добавляем группу ввода после поля
                                    allAttemptsInput.parentNode.appendChild(inputGroupAppend);
                                    zoneInput.parentNode.appendChild(inputGroupAppend2);
                                    topInput.parentNode.appendChild(inputGroupAppend3);

                                    newZoneBtn.addEventListener('click', function () {
                                        let currentValue = parseInt(allAttemptsInput.value) || 0;
                                         $('[data-amount-try-zone-$category->id=amount_try_zone]').val(currentValue);
                                        set_attempt_$category->id()
                                    });
                                    newTopBtn.addEventListener('click', function () {
                                        let currentValue = parseInt(allAttemptsInput.value) || 0;
                                         $('[data-amount-try-top-$category->id=amount_try_top]').val(currentValue);
                                        set_attempt_$category->id()
                                    });
                                    // Обработчик клика на кнопку увеличения
                                    newIncrementBtn.addEventListener('click', function () {
                                        let currentValue = parseInt(allAttemptsInput.value) || 0;
                                        allAttemptsInput.value = currentValue + 1;
                                        set_attempt_$category->id()
                                    });

                                    // Обработчик клика на кнопку удаления
                                    newDecrementBtn.addEventListener('click', function () {
                                        let currentValue = parseInt(allAttemptsInput.value) || 0;
                                        if (currentValue > 0) {
                                            allAttemptsInput.value = currentValue - 1;
                                        }
                                        set_attempt_$category->id()
                                    });
                                    $('[data-all-attempts-id-$category->id=all-attempts]').val('');
                                    $('[data-amount-try-top-$category->id=amount_try_top]').val('');
                                    $('[data-amount-try-zone-$category->id=amount_try_zone]').val('');
                                    $('[data-semifinal-user-id-$category->id=user_id]').val('');
                                }
                            });
                            $(document).on("change", '[data-semifinal-user-id-$category->id=user_id]', function () {
                                var routeId = $('[data-semifinal-route-id-$category->id=final_route_id]').val(); // ID выбранного маршрута
                                var userId = $('[data-semifinal-user-id-$category->id="user_id"]').select2('val')
                                var eventId = $('[data-semifinal-event-id-$category->id=event_id]').val(); // ID выбранного участника
                                if(routeId){
                                    $.get("/admin/api/semifinal/get_attempts", // URL эндпоинта
                                        {
                                            route_id: routeId,
                                            user_id: userId,
                                            event_id: eventId
                                        }, // Передаем ID маршрута и участника в запросе
                                        function (data) {
                                            // Обновляем поля с количеством попыток
                                            $('[data-amount-try-top-$category->id=amount_try_top]').val(data.amount_try_top);
                                            $('[data-amount-try-zone-$category->id=amount_try_zone]').val(data.amount_try_zone);
                                            $('[data-all-attempts-id-$category->id=all-attempts]').val(data.all_attempts);
                                        }
                                    );
                                }
                            });
                            $(document).on("change", '[data-semifinal-route-id-$category->id=final_route_id]', function () {
                                var routeId = $(this).val(); // ID выбранного маршрута
                                var userId = $('[data-semifinal-user-id-$category->id="user_id"]').select2('val')
                                var eventId = $('[data-semifinal-event-id-$category->id=event_id]').val(); // ID выбранного участника
                                $.get("/admin/api/semifinal/get_attempts", // URL эндпоинта
                                    {
                                        route_id: routeId,
                                        user_id: userId,
                                        event_id: eventId
                                    },
                                    function (data) {
                                        // Обновляем поля с количеством попыток
                                        $('[data-all-attempts-id-$category->id=all-attempts]').val(data.all_attempts);
                                        $('[data-amount-try-top-$category->id=amount_try_top]').val(data.amount_try_top);
                                        $('[data-amount-try-zone-$category->id=amount_try_zone]').val(data.amount_try_zone);
                                    }
                                );
                            });
                            let btn_close_modal_one_route_$category->id = '[id="app-admin-actions-resultroutesemifinalstage-batchresultsemifinalcustomfilloneroute-$index"] [data-dismiss="modal"][class="btn btn-default"]'
                            $(document).on("click", btn_close_modal_one_route_$category->id, function () {
                                window.location.reload();
                            });
                        EOT;
                    $script_custom = <<<EOT
                        let btn_close_modal_custom{$category->id} = '[id="app-admin-actions-resultroutesemifinalstage-batchresultsemifinalcustom-{$index}"] [data-dismiss="modal"][class="btn btn-default"]'
                        $(document).on("click", btn_close_modal_custom{$category->id}, function () {
                            window.location.reload();
                        });
                    EOT;
                    $tools->append(new BatchResultSemiFinalCustomFillOneRoute($category, $script_one_route));
                    $tools->append(new BatchResultSemiFinalCustom($category, $script_custom));
                }
            } else {
                $tools->append(new BatchResultSemiFinal);
            }
            $tools->append(new BatchForceRecoutingSemiFinalResultGroup);
            $tools->append(new BatchForceRecoutingSemiFinalResultGender);
            if(Admin::user()->username == "Tester2"){
                $tools->append(new BatchGenerateResultSemiFinalParticipant);
            }
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
//            $actions->disableEdit();
//            $actions->disableDelete();
            $actions->disableView();
            if(Admin::user()->is_delete_result == 0){
                $actions->disableDelete();
            }
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
    protected function form($type, $id = null)
    {
        $form = new Form(new ResultSemiFinalStage());
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
        $count = $event->amount_routes_in_semifinal;
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
        $form->table('result_for_edit_semifinal', 'Таблица результата', function ($table) use ($event){
            $table->text('Номер маршрута')->readonly();
            $table->number('Попытки на топ');
            $table->number('Попытки на зону');
        })->value($arr);
        $form->saving(function (Form $form) use ($type, $id) {
            if($form->result_for_edit_semifinal){
                $user_id = $form->model()->find($id)->user_id;
                $event_id = $form->model()->find($id)->event_id;
                $routes = $form->result_for_edit_semifinal;
                foreach ($routes as $route) {
                    $result = ResultRouteSemiFinalStage::where('user_id', $user_id)->where('event_id', $event_id)->where('final_route_id', $route['Номер маршрута'])->first();
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
              Event::refresh_final_points_all_participant_in_semifinal($event_id);
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
