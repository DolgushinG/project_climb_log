<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\BatchForceRecouting;
use App\Admin\Actions\BatchGenerateParticipant;
use App\Admin\Actions\ResultQualification\BatchResultQualification;
use App\Admin\Actions\ResultRouteFranceSystemQualificationStage\BatchExportProtocolRouteParticipantsQualification;
use App\Admin\Actions\ResultRouteFranceSystemQualificationStage\BatchExportResultFranceSystemQualification;
use App\Admin\Actions\ResultRouteFranceSystemQualificationStage\BatchResultFranceSystemQualification;
use App\Exports\ExportCardParticipantFranceSystem;
use App\Exports\ExportCardParticipantFestival;
use App\Exports\ExportListParticipant;
use App\Exports\ExportProtocolRouteParticipant;
use App\Exports\FranceSystemQualificationResultExport;
use App\Exports\QualificationResultExport;
use App\Models\Event;
use App\Models\OwnerPaymentOperations;
use App\Models\OwnerPayments;
use App\Models\ResultQualificationClassic;
use App\Http\Controllers\Controller;
use App\Models\ParticipantCategory;
use App\Models\ResultRouteQualificationClassic;
use App\Models\ResultFranceSystemQualification;
use App\Models\ResultRouteFranceSystemQualification;
use App\Models\Set;
use App\Models\User;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Show;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\MessageBag;
use Maatwebsite\Excel\Facades\Excel;

class ResultQualificationController extends Controller
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
            ->row(function (Row $row) {
                $event = Event::where('owner_id', '=', Admin::user()->id)->where('active', '=', 1)->first();
                if ($event) {
                    if ($event->is_france_system_qualification) {
                        $row->column(20, $this->france_system_qualification_counting());
                    } else {
                        $row->column(20, $this->qualification_classic());
                    }
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
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $event = Event::where('owner_id', '=', Admin::user()->id)->where('active', '=', 1)->first();
        if ($event) {
            if ($event->is_france_system_qualification) {
                $show = new Show(ResultFranceSystemQualification::findOrFail($id));
                $show->panel()
                    ->tools(function ($tools) {
                        $tools->disableEdit();
                        $tools->disableList();
                        $tools->disableDelete();
                    });
            } else {
                $show = new Show(ResultQualificationClassic::findOrFail($id));
                $show->panel()
                    ->tools(function ($tools) {
//                        $tools->disableEdit();
                        $tools->disableList();
                        $tools->disableDelete();
                    });
            }
        }
        $show->field('user.middlename', __('Имя и Фамилия'));
        $show->field('user.birthday', __('Дата Рождения'));
        $show->field('user.email', __('Почта'));
        $show->field('user.city', __('Город'));
        $show->field('user.team', __('Команда'));
        $show->field('user.sports_category', __('Разряд'));
        $show->field('bill', 'Чек')->image('', 600, 800);

        return $show;
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
        if ($request->is_paid) {
            $type = 'is_paid';
        }
        if ($request['name'] == 'amount_start_price') {
            $type = 'amount_start_price';

        }
        if ($request->result_for_edit) {
            $type = 'update';
        }
        return $this->form($type, $id)->update($id);
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
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, Request $request)
    {
        $event = Event::where('owner_id', '=', Admin::user()->id)->where('active', '=', 1)->first();
        if ($event->is_france_system_qualification) {
            $result = ResultFranceSystemQualification::find($id);
            ResultRouteFranceSystemQualification::where('user_id', $result->user_id)->where('event_id', $result->event_id)->delete();
            $model = ResultFranceSystemQualification::where('user_id', $result->user_id)->where('event_id', $result->event_id)->first();
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
        $grid = new Grid(new ResultQualificationClassic);
        if (!Admin::user()->isAdministrator()) {
            $grid->model()->where('owner_id', '=', Admin::user()->id);
        }
        $grid->model()->where(function ($query) {
            $query->has('event.participant');
        });
        $event = Event::where('owner_id', '=', Admin::user()->id)->where('active', 1)->first();
        $grid->selector(function (Grid\Tools\Selector $selector) use ($event) {
            $category = ParticipantCategory::whereIn('category', $event->categories)->where('event_id', $event->id)->pluck('id')->toArray();
            $p_categories = ResultQualificationClassic::where('event_id', $event->id)->whereIn('category_id', $category)->get();
            if ($p_categories->isNotEmpty()) {
                $selector->select('category_id', 'Категория', (new \App\Models\ParticipantCategory)->getUserCategory(Admin::user()->id));
            }
            $selector->select('gender', 'Пол', ['male' => 'Муж', 'female' => 'Жен']);
            $selector->select('active', 'Результаты ', [1 => 'Добавил', 0 => 'Не добавил']);
            $selector->select('is_paid', 'Есть оплата', [1 => 'Да', 0 => 'Нет']);
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
//            $actions->append(new ActionRejectBill($actions->getKey(), $event->id));
//            $actions->disableView();
            $actions->disableDelete();
        });
        $grid->column('user.middlename', __('Участник'));
        $grid->column('user.birthday', __('Дата Рождения'));
        $grid->column('user.gender', __('Пол'))->display(function ($gender) {
            return trans_choice('somewords.' . $gender, 10);
        });
        $category = ParticipantCategory::whereIn('category', $event->categories)->where('event_id', $event->id)->pluck('id')->toArray();
        $p_categories = ResultQualificationClassic::where('event_id', $event->id)->whereIn('category_id', $category)->get();

        if ($p_categories->isNotEmpty()) {
            $grid->column('category_id', 'Категория')
                ->help('Если случается перенос, из одной категории в другую, необходимо обязательно пересчитать результаты')
                ->select((new \App\Models\ParticipantCategory)->getUserCategory(Admin::user()->id));
        }

        if (!$event->is_input_set) {
            $grid->column('number_set_id', 'Номер сета')
                ->select(Set::getParticipantSets(Admin::user()->id));
        }
        $grid->column('user_place', 'Место в квалификации')
            ->help('При некорректном раставлением мест, необходимо пересчитать результаты')
            ->sortable();
        $grid->column('points', 'Баллы')->sortable();
        $grid->column('active', 'Результаты')->using([0 => 'Не внес', 1 => 'Внес'])->display(function ($title, $column) {
            if ($this->active == 0) {
                return $column->label('default');
            } else {
                return $column->label('success');
            }
        });

        if ($event->is_need_pay_for_reg) {
            $amounts = [];
            $count = 1;
//            dd($event->options_amount_price);
            if($event->options_amount_price){
                foreach($event->options_amount_price as $amount)  {
                    $amounts[$count] = $amount['Сумма'];
                    $count++;
                }
                $amounts[0] = '0 р';
                $grid->column('amount_start_price', 'Сумма оплаты')->editable('select', $amounts);
            } else {
                $grid->column('amount_start_price', 'Сумма оплаты')->display(function ($amount_start_price) use ($event){
                    return $event->amount_start_price;
                });
            }

            $states = [
                'on' => ['value' => 1, 'text' => 'V', 'color' => 'success'],
                'off' => ['value' => 0, 'text' => 'X', 'color' => 'default'],
            ];

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
            $grid->column('bill', 'Чек участника')->image('', 100, 100);
        }
        return $grid;
    }


    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function france_system_qualification_counting()
    {
        $grid = new Grid(new ResultFranceSystemQualification);
        if (!Admin::user()->isAdministrator()) {
            $grid->model()->where('owner_id', '=', Admin::user()->id);
        }
        $grid->model()->where(function ($query) {
            $query->has('event.result_france_system_qualification');
        });
        $event = Event::where('owner_id', '=', Admin::user()->id)->where('active', 1)->first();
        $grid->selector(function (Grid\Tools\Selector $selector) {
            $selector->select('category_id', 'Категория', (new \App\Models\ParticipantCategory)->getUserCategory(Admin::user()->id));
            $selector->select('gender', 'Пол', ['male' => 'Муж', 'female' => 'Жен']);
            $selector->select('is_paid', 'Есть оплата', [1 => 'Да', 0 => 'Нет']);
        });
        $grid->tools(function (Grid\Tools $tools) use ($event) {
            $tools->append(new BatchExportResultFranceSystemQualification);
            $tools->append(new BatchResultFranceSystemQualification);
            $tools->append(new BatchGenerateParticipant);
            $tools->append(new BatchExportProtocolRouteParticipantsQualification);
        });
        $grid->actions(function ($actions) {
            $actions->disableEdit();
//            $actions->disableDelete();
//            $actions->disableView();
        });

        $grid->disableExport();
        $grid->disableFilter();
        $grid->disableCreateButton();
        $grid->disableColumnSelector();
        $grid->column('user.middlename', __('Участник'));
        $grid->column('user.gender', __('Пол'))->display(function ($gender) {
            return trans_choice('somewords.' . $gender, 10);
        });
        $grid->column('number_set_id', 'Номер сета')
            ->select(ResultQualificationClassic::number_sets(Admin::user()->id));
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
        if ($event->is_need_pay_for_reg) {

            $amounts = [];
            $count = 1;
            if($event->options_amount_price){
                foreach($event->options_amount_price as $amount)  {
                    $amounts[$count] = $amount['Сумма'];
                    $count++;
                }
                $amounts[0] = '0 р';
            } else {
                $amounts[0] = $event->amount_start_price;
            }
            $grid->column('amount_start_price', 'Сумма оплаты')->editable('select', $amounts);
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
            $grid->column('bill', 'Чек участника')->image('', 100, 100);
        }
        return $grid;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form($type, $id = null)
    {

        $event = Event::where('owner_id', '=', Admin::user()->id)->where('active', '=', 1)->first();
        if ($event->is_france_system_qualification) {
            $form = new Form(new ResultFranceSystemQualification);
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
            $form->switch('amount_start_price', 'amount_start_price');
            $form->saving(function (Form $form) {
                if ($form->amount_try_top > 0) {
                    $form->amount_top = 1;
                } else {
                    $form->amount_top = 0;
                }
                if ($form->amount_try_zone > 0) {
                    $form->amount_zone = 1;
                } else {
                    $form->amount_zone = 0;
                }
            });
        } else {
            $form = new Form(new ResultQualificationClassic);
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
            $form->table('result_for_edit', 'Таблица результата', function ($table) {
                $table->text('route_id', 'Номер маршрут')->readonly();
                $table->text('grade', 'Категория')->readonly();
                $table->select('attempt', 'Результат')->attribute('inputmode', 'none')->options([1 => 'FLASH', 2 => 'REDPOINT', 0 => 'Не пролез'])->width('50px');
            });
        }
        $form->saving(function (Form $form) use ($type, $id) {
            if ($type == 'update') {
                $user_id = $form->model()->find($id)->user_id;
                $event_id = $form->model()->find($id)->event_id;
                $routes = $form->result_for_edit;
                foreach ($routes as $route) {
                    $result = ResultRouteQualificationClassic::where('user_id', $user_id)->where('event_id', $event_id)->where('route_id', $route['route_id'])->first();
                    $result->attempt = $route['attempt'];
                    $result->save();
                }
                $categories = ParticipantCategory::where('event_id', $event_id)->get();
                foreach ($categories as $category) {
                    Cache::forget('result_male_cache_' . $category->category);
                    Cache::forget('result_female_cache_' . $category->category);
                }
                Event::refresh_final_points_all_participant($form->model()->find($id)->event_id);
            }
            if(intval($form->input('amount_start_price')) > 0){
                $result = $form->model()->find($id);
                $result->amount_start_price = $form->input('amount_start_price');
                $result->save();
            }
            if ($form->input('is_paid') == "0" || $form->input('is_paid') == "1") {
                $participant = $form->model()->find($id);
                $event = Event::find($participant->event_id);
                if(!$participant->amount_start_price && !$event->amount_start_price && $event->options_amount_price){
                    throw new \Exception('Перед оплатой надо выбрать сумму оплаты', code: 111);
                }

                $amount_participant = $form->model()->where('event_id', $participant->event_id)->get()->count();
                $participant->is_paid = $form->input('is_paid');
                $participant->save();
                $admin = Admin::user();
                if ($form->input('is_paid') === "1") {
                    if($event->options_amount_price){
                        $amounts = [];
                        $names = [];
                        $count = 1;
                        foreach($event->options_amount_price as $amount)  {
                            $amounts[$count] = $amount['Сумма'];
                            $names[$count] = $amount['Название'];
                            $count++;
                        }
                        $amounts[0] = '0 р';
                        $names[0] = 'Не оплачено';

                        $index = $participant->amount_start_price;
                        $amount_start_price = $amounts[$index];
                        $amount_name = $names[$index];
                    } else {
                        $amount_start_price = $event->amount_start_price;
                        $amount_name = 'Стартовый взнос';
                    }
                    $transaction = OwnerPaymentOperations::where('event_id', $participant->event_id)
                        ->where('user_id', $participant->user_id)->first();
                    if (!$transaction) {
                        $transaction = new OwnerPaymentOperations;
                    }
                    $transaction->owner_id = $admin->id;
                    $transaction->user_id = $participant->user_id;
                    $transaction->event_id = $participant->event_id;
                    $transaction->amount = Event::counting_amount_for_pay_participant($amount_start_price);
                    $transaction->type = $amount_name;
                    $transaction->save();

                    # Пересчитываем оплату за соревы
                    $payments = OwnerPayments::where('event_id', $participant->event_id)->first();
                    if (!$payments) {
                        $payments = new OwnerPayments;
                    }
                    $amount = OwnerPaymentOperations::where('event_id', $participant->event_id)->sum('amount');
                    $payments->owner_id = $admin->id;
                    $payments->event_id = $participant->event_id;
                    $payments->event_title = $event->title;
                    $payments->amount_for_pay = $amount;
                    $payments->amount_participant = $amount_participant;
                    $payments->amount_cost_for_service = Event::COST_FOR_EACH_PARTICIPANT;
                    $payments->save();

                    $user = User::find($participant->user_id);
                    ResultQualificationClassic::send_confirm_bill($event, $user);
                }
                if ($form->input('is_paid') === "0") {
                    $transaction = OwnerPaymentOperations::where('event_id', $participant->event_id)
                        ->where('user_id', $participant->user_id)->first();
                    if ($admin && $transaction) {
                        $transaction->delete();
                        # Пересчитываем оплату за соревы
                        $payments = OwnerPayments::where('event_id', $participant->event_id)->first();
                        if (!$payments) {
                            $payments = new OwnerPayments;
                        }
                        $amount = OwnerPaymentOperations::where('event_id', $participant->event_id)->sum('amount');
                        $payments->owner_id = $admin->id;
                        $payments->event_id = $participant->event_id;
                        $payments->event_title = $event->title;
                        $payments->amount_for_pay = $amount;
                        $payments->amount_participant = $amount_participant;
                        $payments->amount_cost_for_service = Event::COST_FOR_EACH_PARTICIPANT;
                        $payments->save();

                    }
                }

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

    public function exportFranceSystemQualificationExcel(Request $request)
    {
        $file_name = 'Результаты квалификации.xlsx';
        $result = Excel::download(new FranceSystemQualificationResultExport($request->id), $file_name, \Maatwebsite\Excel\Excel::XLSX);
        return response()->download($result->getFile(), $file_name, [
            'Content-Type' => 'application/xlsx',
        ]);
    }

    public function cardParticipantFestivalExcel(Request $request)
    {
        $file_name = 'Карточка участника с трассами.xlsx';
        $result = Excel::download(new ExportCardParticipantFestival($request->id), $file_name, \Maatwebsite\Excel\Excel::XLSX);
        return response()->download($result->getFile(), $file_name, [
            'Content-Type' => 'application/xlsx',
        ]);
    }

    public function listParticipantExcel(Request $request)
    {
        $file_name = 'Полный список участников.xlsx';
        $result = Excel::download(new ExportListParticipant($request->id), $file_name, \Maatwebsite\Excel\Excel::XLSX);
        return response()->download($result->getFile(), $file_name, [
            'Content-Type' => 'application/xlsx',
        ]);
    }

    public function cardParticipantFranceSystemExcel(Request $request)
    {
        $file_name = 'Карточка.xlsx';
        $result = Excel::download(new ExportCardParticipantFranceSystem($request->id), $file_name, \Maatwebsite\Excel\Excel::XLSX);
        return response()->download($result->getFile(), $file_name, [
            'Content-Type' => 'application/xlsx',
        ]);
    }

    public function protocolRouteExcel(Request $request)
    {
        $file_name = 'Протокол.xlsx';
        $result = Excel::download(new ExportProtocolRouteParticipant($request->event_id, $request->stage, $request->set_id, $request->gender, $request->category_id), $file_name, \Maatwebsite\Excel\Excel::XLSX);
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
        $participant = ResultQualificationClassic::where('owner_id', '=', Admin::user()->id)
            ->where('event_id', '=', $event_id)
            ->where('active', '=', 1)
            ->pluck('user_id')->toArray();
        return User::whereIn('id', $participant)->pluck('middlename', 'id');
    }


    public function rejectBill(Request $request)
    {
        $event = Event::find($request->event_id);
        if ($event->is_france_system_qualification) {
            $participant = ResultFranceSystemQualification::find($request->id);
        } else {
            $participant = ResultQualificationClassic::find($request->id);
        }
        $participant->bill = null;
        $participant->save();
    }


}
