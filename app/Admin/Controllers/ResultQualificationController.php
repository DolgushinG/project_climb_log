<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\BatchForceRecouting;
use App\Admin\Actions\BatchForceRecoutingResultQualificationFranceGender;
use App\Admin\Actions\BatchForceRecoutingResultQualificationFranceGroup;
use App\Admin\Actions\BatchGenerateParticipant;
use App\Admin\Actions\BatchMergeResult;
use App\Admin\Actions\ResultQualification\BatchResultQualification;
use App\Admin\Actions\ResultRouteFranceSystemQualificationStage\BatchExportProtocolRouteParticipantsQualification;
use App\Admin\Actions\ResultRouteFranceSystemQualificationStage\BatchExportResultFranceSystemQualification;
use App\Admin\Actions\ResultRouteFranceSystemQualificationStage\BatchResultFranceSystemQualification;
use App\Admin\Actions\ResultRouteFranceSystemQualificationStage\BatchResultQualificationFranceCustomFillOneRoute;
use App\Exports\ExportCardParticipantFranceSystem;
use App\Exports\ExportCardParticipantFestival;
use App\Exports\ExportListParticipant;
use App\Exports\ExportProtocolRouteParticipant;
use App\Exports\FranceSystemQualificationResultExport;
use App\Exports\QualificationResultExport;
use App\Helpers\Helpers;
use App\Jobs\UpdateResultParticipants;
use App\Models\Event;
use App\Models\Grades;
use App\Models\OwnerPaymentOperations;
use App\Models\ResultQualificationClassic;
use App\Http\Controllers\Controller;
use App\Models\ParticipantCategory;
use App\Models\ResultRouteQualificationClassic;
use App\Models\ResultFranceSystemQualification;
use App\Models\ResultRouteFranceSystemQualification;
use App\Models\Route;
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
        $show->field('user.contact', __('Контакт'));
        $show->field('user.city', __('Город'));
        $show->field('user.team', __('Команда'));
        $show->field('user.sports_category', __('Разряд'));
        $show->field('bill', 'Чек')->image('', 600, 800);
        $show->field('document', 'Документ')->image('', 600, 800);
        $show->field('products_and_discounts', 'Мерч и скидка')->as(function ($content) {
            $str = '';
            if (isset($content['products'])) {
                foreach ($content['products'] as $pr) {
                    $str .= $pr . ' ,';
                }
            }
            if (isset($content['discount'])) {
                $str .= PHP_EOL . 'Скидка - ' . PHP_EOL . $content['discount'];
            }
            return $str;
        });
        $show->field('id', __('История результатов'))->as(
            function ($content) use ($event) {
                $str = '';
                if ($event->is_france_system_qualification) {
                    $res = ResultFranceSystemQualification::find($content);
                    if ($res) {
                        $user_id = $res->user_id;
                        $all_user_places = ResultFranceSystemQualification::where('user_id', $user_id)->get()->pluck('user_place')->toArray();
                        $all_categories = ResultFranceSystemQualification::where('user_id', $user_id)->get()->pluck('category_id', 'event_id')->toArray();
                    }

                } else {
                    $res = ResultQualificationClassic::find($content);
                    if ($res) {
                        $user_id = $res->user_id;
                        $all_user_places = ResultQualificationClassic::where('user_id', $user_id)->get()->pluck('user_place')->toArray();
                        $all_categories = ResultQualificationClassic::where('user_id', $user_id)->get()->pluck('category_id')->toArray();
                    }
                }
                $categories = [];
                if($all_categories){
                    foreach ($all_categories as $category){
                        $participant_category = ParticipantCategory::find($category);
                        $categories[] = $participant_category->category;
                    }
                }
                if($all_user_places){
                    foreach($all_user_places as $index => $place){
                        $str .= '['.$place.' место, '.$categories[$index].']';
                    }
                }
                return $str;
            }
        );

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
            ->body($this->form('create_france_system_result'));
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
        if ($request->category_id) {
            $type = 'update';
            return $this->form($type, $id)->update($id);
        }
        if ($request->is_paid) {
            $type = 'is_paid';
            return $this->form($type, $id)->update($id);
        }
        if ($request['name'] == 'amount_start_price') {
            $type = 'amount_start_price';
            return $this->form($type, $id)->update($id);
        }
        if ($request->result_for_edit_france_system_qualification) {
            $type = 'update';
            return $this->form($type, $id)->update($id);
        }
        if ($request->result_for_edit) {
            $type = 'update';
            return $this->form($type, $id)->update($id);
        }
        if ($request->gender) {
            $type = 'gender';
            return $this->form($type, $id)->update($id);
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id, Request $request)
    {
        $event = Event::where('owner_id', '=', Admin::user()->id)->where('active', '=', 1)->first();
        if ($event->is_france_system_qualification) {
            $result = ResultFranceSystemQualification::find($id);
            $result_route = ResultRouteFranceSystemQualification::where('user_id', $result->user_id)->where('event_id', $result->event_id)->first();
            if ($result_route) {
                ResultRouteFranceSystemQualification::where('user_id', $result->user_id)->where('event_id', $result->event_id)->delete();
                $model = ResultFranceSystemQualification::where('user_id', $result->user_id)->where('event_id', $result->event_id)->first();
                $model->amount_top = null;
                $model->amount_try_top = null;
                $model->amount_zone = null;
                $model->amount_try_zone = null;
                $model->save();

                $result->result_for_edit_france_system_qualification = null;
                $result->save();
            } else {
                ResultFranceSystemQualification::where('user_id', $result->user_id)->where('event_id', $result->event_id)->delete();
            }
        } else {
            $result = ResultQualificationClassic::find($id);
            $result_route = ResultRouteQualificationClassic::where('user_id', $result->user_id)->where('event_id', $result->event_id)->first();
            if ($result_route) {
                return Helpers::custom_response("Нельзя удалить юзера с результатами");
            } else {
                ResultQualificationClassic::where('user_id', $result->user_id)->where('event_id', $result->event_id)->delete();
            }

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
                if ($event->is_open_main_rating && $event->is_auto_categories) {
                    $selector->select('global_category_id', 'Категория', (new \App\Models\ParticipantCategory)->getUserCategory(Admin::user()->id));
                } else {
                    $selector->select('category_id', 'Категория', (new \App\Models\ParticipantCategory)->getUserCategory(Admin::user()->id));
                }
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
        $grid->tools(function (Grid\Tools $tools) use ($event) {
            $tools->append(new BatchResultQualification);
            if (!$event->is_registration_state && !$event->is_france_system_qualification) {
                $tools->append(new BatchMergeResult);
            }
            $event = Event::where('owner_id', '=', \Encore\Admin\Facades\Admin::user()->id)->where('active', 1)->first();
            $is_enabled = Route::where('event_id', $event->id)->first();
            if ($is_enabled && Admin::user()->username == "Tester2") {
                $tools->append(new BatchGenerateParticipant);
            }
            $tools->append(new BatchForceRecouting);
        });
        $grid->actions(function ($actions) use ($event) {
//            $actions->disableEdit();
//            $actions->append(new ActionRejectBill($actions->getKey(), $event->id));
//            $actions->disableView();
//            $actions->disableDelete();
        });
        $grid->column('user.middlename', __('Участник'));
        $grid->column('user.birthday', __('Дата Рождения'));
        $grid->column('gender', __('Пол'))
            ->help('Если случается перенос, из одного пола в другой, необходимо обязательно пересчитать результаты')
            ->select(['male' => 'Муж', 'female' => 'Жен']);
        $category = ParticipantCategory::whereIn('category', $event->categories)->where('event_id', $event->id)->pluck('id')->toArray();
        $p_categories = ResultQualificationClassic::where('event_id', $event->id)->whereIn('category_id', $category)->get();
        if ($p_categories->isNotEmpty()) {
            $grid->column('category_id', 'Категория')
                ->help('Если случается перенос, из одной категории в другую, необходимо обязательно пересчитать результаты')
                ->select((new \App\Models\ParticipantCategory)->getUserCategory(Admin::user()->id));
        }
//        $categories = (new \App\Models\ParticipantCategory)->getUserCategory(Admin::user()->id);
//        $grid->column('category_id', 'Общая Категория')->display(function ($category) use ($categories) {
//            return $categories[$category] ?? 'не определена';
//        });
        if (!$event->is_input_set) {
            $sets = Set::getParticipantSets($event->id);
            $grid->column('number_set_id', 'Номер сета')
                ->select($sets);
        }
        $grid->column('user_place', 'Место в квалификации')
            ->help('При некорректном раставлением мест, необходимо пересчитать результаты')
            ->sortable();
        $grid->column('points', 'Баллы')->sortable();
        if ($event->is_open_main_rating) {
            $grid->column('global_points', 'Общие Баллы');
            $grid->column('last_points_after_merged', 'Баллы со всех этапов')->display(function ($items) {
                $str = '';
                if ($items) {
                    foreach ($items as $item) {
                        if ($item) {
                            $str .= '[' . $item . ']';
                        }
                    }
                }
                return $str;
            });
            $grid->column('last_user_place_after_merged', 'Места со всех этапов')->display(function ($items) {
                $str = '';
                if ($items) {
                    foreach ($items as $item) {
                        if ($item) {
                            $str .= '[' . $item . ' место]';
                        }
                    }
                }
                return $str;
            });
            $grid->column('last_category_after_merged', 'Категории со всех этапов')->display(function ($items) {
                $str = '';
                if ($items) {
                    foreach ($items as $item) {
                        if ($item) {
                            $str .= '[' . $item . ']';
                        }
                    }
                }
                return $str;
            });
            $grid->column('user_global_place', 'Общее Место')->sortable();
            $categories = (new \App\Models\ParticipantCategory)->getUserCategory(Admin::user()->id);
            if ($event->is_auto_categories) {
                $grid->column('global_category_id', 'Общая Категория')->display(function ($category) use ($categories) {
                    return $categories[$category] ?? 'не определена';
                });
            }
        }
        $result = ResultRouteQualificationClassic::where('event_id', $event->id)->first();
        if ($event->is_auto_categories && $result) {
            $states = [
                'on' => ['value' => 1, 'text' => 'Внимание', 'color' => 'warning'],
                'off' => ['value' => 0, 'text' => 'OK', 'color' => 'success'],
            ];

            $grid->column('is_recheck', 'Результат с вопросом')->switch($states)->display(function ($state) {
                $result = ResultQualificationClassic::find($this->id);
                if ($result) {
                    $result = ResultRouteQualificationClassic::where('user_id', $result->user_id)->where('event_id', $result->event_id)->first();
                }
                if (!$result) {
                    return 'Нет результата';
                }
                return $state;
            });;
        }
        if ($event->is_open_main_rating) {
            $grid->column('is_other_event', 'Перенесен из других сорев')->display(function ($state) {
                if (intval($state) == 1) {
                    return 'Да';
                } else {
                    return 'Нет';
                }
            });
        }
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
            if ($event->options_amount_price) {
                foreach ($event->options_amount_price as $amount) {
                    $amounts[$count] = $amount['Сумма'];
                    $count++;
                }
                $amounts[0] = '0 р';
                $grid->column('amount_start_price', 'Сумма оплаты')->editable('select', $amounts);
            } else {
                if ($event->setting_payment == OwnerPaymentOperations::DINAMIC) {
                    $grid->column('amount_start_price', 'Сумма оплаты');
                } else {
                    $grid->column('amount_start_price', 'Сумма оплаты')->display(function ($amount_start_price) use ($event) {
                        if (!$event->amount_start_price) {
                            return '0 р';
                        } else {
                            return $event->amount_start_price;
                        }
                    });
                }

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
            $grid->column('document', 'Документ участника')->image('', 100, 100);
            if ($event->setting_payment == OwnerPaymentOperations::DINAMIC) {
                $grid->column('products_and_discounts', 'Мерч и скидка')->display(function ($title, $column) {
                    $str = '';
                    if (isset($title['helper'])) {
                        $str .= "<span style='color:green'>Участие {$title['helper']}</span><br>";
                    }
                    if (isset($title['products'])) {
                        foreach ($title['products'] as $pr) {
                            $str .= "<span style='color:blue'>$pr</span><br>";
                        }
                    }
                    if (isset($title['discount'])) {
                        $str .= PHP_EOL . 'Скидка - ' . PHP_EOL . $title['discount'];
                    }
                    return $str;
                });
            }
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
            $categories = ParticipantCategory::whereIn('category', $event->categories)->where('event_id', $event->id)->get();
            foreach ($categories as $category) {
                $tools->append(new BatchResultFranceSystemQualification($category));
                $tools->append(new BatchResultQualificationFranceCustomFillOneRoute($category));
            }
            $event = Event::where('owner_id', '=', \Encore\Admin\Facades\Admin::user()->id)->where('active', 1)->first();
            $is_enabled = Grades::where('event_id', $event->id)->first();
            if ($is_enabled && Admin::user()->username == "Tester2") {
                $tools->append(new BatchGenerateParticipant);
            }
            $tools->append(new BatchForceRecoutingResultQualificationFranceGender);
            $tools->append(new BatchForceRecoutingResultQualificationFranceGroup);
            $tools->append(new BatchExportProtocolRouteParticipantsQualification);
        });
        $grid->actions(function ($actions) {
//            $actions->disableEdit();
            $actions->disableDelete();
//            $actions->disableView();
        });

        $grid->disableExport();
        $grid->disableFilter();

        $grid->disableCreateButton();
        $grid->disableColumnSelector();
        $grid->column('user.middlename', __('Участник'));
        $grid->column('gender', __('Пол'))
            ->help('Если случается перенос, из одного пола в другой, необходимо обязательно пересчитать результаты')
            ->select(['male' => 'Муж', 'female' => 'Жен']);
        $grid->column('number_set_id', 'Номер сета')
            ->select(ResultQualificationClassic::number_sets($event->id));
        $grid->column('category_id', 'Категория')
            ->help('Если случается перенос, из одной категории в другую, необходимо обязательно пересчитать результаты')
            ->select((new \App\Models\ParticipantCategory)->getUserCategory(Admin::user()->id));
        $grid->column('user.sport_category', __('Разряд'));
        $grid->column('user.birthday', __('Год рождения'));
        $grid->column('place', __('Место'))->sortable();
        $grid->column('amount_top', __('Кол-во топов'));
        $grid->column('amount_try_top', __('Кол-во попыток на топ'));
        $grid->column('amount_zone', __('Кол-во зон'));
        $grid->column('amount_try_zone', __('Кол-во попыток на зону'));
        $states = [
            'on' => ['value' => 1, 'text' => 'Да', 'color' => 'success'],
            'off' => ['value' => 0, 'text' => 'Нет', 'color' => 'default'],
        ];
        if ($event->is_need_pay_for_reg && $event->amount_start_price || $event->options_amount_price) {

            $amounts = [];
            $count = 1;
            if ($event->options_amount_price) {
                foreach ($event->options_amount_price as $amount) {
                    $amounts[$count] = $amount['Сумма'];
                    $count++;
                }
                $amounts[0] = '0 р';
                $grid->column('amount_start_price', 'Сумма оплаты')->editable('select', $amounts);
            } else {
                if ($event->setting_payment == OwnerPaymentOperations::DINAMIC) {
                    $grid->column('amount_start_price', 'Сумма оплаты');
                } else {
                    $grid->column('amount_start_price', 'Сумма оплаты')->display(function ($amount_start_price) use ($event) {
                        if (!$event->amount_start_price) {
                            return '0 р';
                        } else {
                            return $event->amount_start_price;
                        }
                    });
                }

            }
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
            $grid->column('document', 'Документ участника')->image('', 100, 100);
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
            for ($i = 1; $i <= $count; $i++) {
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
            $form->table('result_for_edit_france_system_qualification', 'Таблица результата', function ($table) use ($event) {
                $table->text('Номер маршрута')->readonly();
                $table->number('Попытки на топ');
                $table->number('Попытки на зону');
            })->value($arr);
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
            $form->hidden('gender', 'gender');
            $form->table('result_for_edit', 'Таблица результата', function ($table) {
                $table->text('route_id', 'Номер маршрут')->readonly();
                $table->text('grade', 'Категория')->readonly();
                $table->select('attempt', 'Результат')->attribute('inputmode', 'none')->options([1 => 'FLASH', 2 => 'REDPOINT', 3 => 'ZONE', 0 => 'Не пролез'])->width('50px');
            });
        }
        $form->saving(function (Form $form) use ($type, $id) {
            if ($type == 'update') {
                $event = Event::find($form->model()->find($id)->event_id);
                $user_id = $form->model()->find($id)->user_id;
                $event_id = $form->model()->find($id)->event_id;
                if (intval($form->input('category_id')) > 0) {
                    $result = $form->model()->find($id);
                    $result->category_id = $form->input('category_id');
                    $result->save();
                }
                if ($form->result_for_edit) {
                    $routes = $form->result_for_edit;
                    foreach ($routes as $route) {
                        $result = ResultRouteQualificationClassic::where('user_id', $user_id)->where('event_id', $event_id)->where('route_id', $route['route_id'])->first();
                        $result->attempt = $route['attempt'];
                        $result->save();
                    }
                    $amount_users = ResultQualificationClassic::where('event_id', '=', $event_id)->where('active', '=', 1)->count();
                    Event::force_update_category_id($event, $user_id);
                    if ($amount_users > 100) {
                        UpdateResultParticipants::dispatch($event_id);
                    } else {
                        Event::refresh_final_points_all_participant($event);
                    }
                }
                if ($form->result_for_edit_france_system_qualification) {
                    $routes = $form->result_for_edit_france_system_qualification;
                    foreach ($routes as $route) {
                        $result = ResultRouteFranceSystemQualification::where('user_id', $user_id)->where('event_id', $event_id)->where('route_id', $route['Номер маршрута'])->first();
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
                        $result->amount_try_top = $route['Попытки на топ'];
                        $result->amount_top = $amount_top;
                        $result->amount_zone = $amount_zone;
                        $result->amount_try_zone = $route['Попытки на зону'];
                        $result->save();
                    }
                    Event::refresh_france_system_qualification_counting($event);
                }
                $categories = ParticipantCategory::where('event_id', $event_id)->get();
                foreach ($categories as $category) {
                    Cache::forget('result_male_cache_' . $category->category . '_event_id_' . $event_id);
                    Cache::forget('result_female_cache_' . $category->category . '_event_id_' . $event_id);
                }
                UpdateResultParticipants::dispatch($event_id);
                # Выяснить почему перерасчет стал таким долгим или он был таким?
//                Event::refresh_final_points_all_participant($event);
            }
            if (intval($form->input('amount_start_price')) > 0) {
                $result = $form->model()->find($id);
                $result->amount_start_price = $form->input('amount_start_price');
                $result->save();
            }

            if ($form->input('is_paid') == "0" || $form->input('is_paid') == "1") {
                $participant = $form->model()->find($id);
                $event = Event::find($participant->event_id);
                if (!$participant->amount_start_price && !$event->amount_start_price && $event->options_amount_price) {
                    $response = [
                        'status' => false,
                        'message' => "Перед оплатой надо выбрать сумму оплаты",
                    ];
                    return response()->json($response);
                }
                $amount_participant = $form->model()->where('event_id', $participant->event_id)->get()->count();
                $participant->is_paid = $form->input('is_paid');
                $participant->save();
                $admin = Admin::user();
                if ($form->input('is_paid') === "1") {
                    if ($event->options_amount_price) {
                        $amounts = [];
                        $names = [];
                        $count = 1;
                        foreach ($event->options_amount_price as $amount) {
                            if (!$amount['Сумма'] || $amount['Сумма'] < 0) {
                                $response = [
                                    'status' => false,
                                    'message' => "Сумма для оплаты не может быть 0 или меньше 0",
                                ];
                                return response()->json($response);
                            }
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
                    if (!$amount_start_price || $amount_start_price < 0) {
                        $response = [
                            'status' => false,
                            'message' => "Сумма для оплаты не может быть 0 или меньше 0",
                        ];
                        return response()->json($response);
                    }

                    OwnerPaymentOperations::execute_payment_operations($participant, $admin, $amount_start_price, $amount_name);
                    # Пересчитываем оплату за соревы
                    OwnerPaymentOperations::execute_payment($participant, $admin, $event, $amount_participant);

                    $user = User::find($participant->user_id);
                    ResultQualificationClassic::send_confirm_bill($event, $user);
                }
                if ($form->input('is_paid') === "0") {
                    $user_id = $form->model()->find($id)->user_id;
                    if ($event->is_france_system_qualification) {
                        $allow_delete = ResultRouteFranceSystemQualification::where('event_id', $event->id)->where('user_id', $user_id)->first();
                    } else {
                        $allow_delete = ResultRouteQualificationClassic::where('event_id', $event->id)->where('user_id', $user_id)->first();
                    }
                    # Не допускать отмену об оплате если результат уже внесен, так как так можно не платить за сервис
                    if (!$allow_delete) {
                        $transaction = OwnerPaymentOperations::where('event_id', $participant->event_id)
                            ->where('user_id', $participant->user_id)->first();
                        if ($admin && $transaction) {
                            $transaction->delete();
                            # Пересчитываем оплату за соревы
                            OwnerPaymentOperations::execute_payment($participant, $admin, $event, $amount_participant);
                        }
                    } else {
                        $participant = $form->model()->find($id);
                        $participant->is_paid = 1;
                        $participant->save();
                        $response = [
                            'status' => false,
                            'message' => "Отмена оплаты после внесения результатов участника невозможна",
                        ];
                        return response()->json($response);
                    }

                }

            }
            if ($form->input('is_recheck') == "0" || $form->input('is_recheck') == "1") {
                $participant = $form->model()->find($id);
                $participant->is_recheck = $form->input('is_recheck');
                $participant->save();
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
