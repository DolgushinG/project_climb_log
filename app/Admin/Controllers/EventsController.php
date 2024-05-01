<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\BatchNotificationOfParticipant;
use App\Admin\CustomAction\ActionExport;
use App\Admin\CustomAction\ActionExportCardParticipant;
use App\Admin\Extensions\CustomButton;
use App\Exports\AllResultExport;
use App\Helpers\Helpers;
use App\Models\Event;
use App\Http\Controllers\Controller;
use App\Models\Format;
use App\Models\Grades;
use App\Models\Participant;
use App\Models\ParticipantCategory;
use App\Models\ResultParticipant;
use App\Models\ResultQualificationLikeFinal;
use App\Models\ResultRouteQualificationLikeFinal;
use App\Models\Route;
use App\Models\Set;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Widgets\Box;
use Encore\Admin\Widgets\InfoBox;
use Encore\Admin\Widgets\Tab;
use Encore\Admin\Widgets\Table;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\MessageBag;
use Maatwebsite\Excel\Facades\Excel;

class EventsController extends Controller
{
    use HasResourceActions;

    const STATES_BTN = [
                'on' => ['value' => 1, 'text' => 'Да', 'color' => 'success'],
                'off' => ['value' => 0, 'text' => 'Нет', 'color' => 'default'],
            ];
    const STATES_BTN_OPEN_AND_CLOSE = [
                'on' => ['value' => 1, 'text' => 'Открыта', 'color' => 'success'],
                'off' => ['value' => 0, 'text' => 'Закрыта', 'color' => 'default'],
            ];

    /**
     * Index interface.
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        return $content->row(function ($row) {
                $event = Event::where('owner_id', '=', Admin::user()->id)->where('active', 1)->first();
                if($event){
                    if($event->is_qualification_counting_like_final){
                        $participant = ResultQualificationLikeFinal::where('event_id', $event->id);
                    } else {
                        $participant = Participant::where('event_id', $event->id);
                    }
                    $sum_participant = $participant->count();
                    $participant_is_paid = $participant->where('is_paid', 1)->count();
                    $participant_is_not_paid = $participant->where('is_paid', 0)->count();
                    $participant_is_not_active = $participant->where('active', 0)->count();
                    $participant_is_active = $participant->where('active', 1)->count();
                    $sum = $participant_is_not_paid + $participant_is_not_active;
                }
                $row->column(3, new InfoBox('Кол-во участников', 'users', 'aqua', '/admin/participants', $sum_participant ?? 0));
                $row->column(3, new InfoBox('Оплачено', 'money', 'green', '/admin/participants', $participant_is_paid ?? 0));
                if($event){
                    if(!$event->is_qualification_counting_like_final){
                        $row->column(3, new InfoBox('Внесли результат', 'book', 'yellow', '/admin/participants', $participant_is_active ?? 0));
                        $row->column(3, new InfoBox('Не оплаченых и без результата', 'money', 'red', '/admin/participants', $sum ?? 0));
                    }
                } else {
                    $row->column(3, new InfoBox('Внесли результат', 'book', 'yellow', '/admin/participants', $participant_is_active ?? 0));
                    $row->column(3, new InfoBox('Не оплаченых и без результата', 'money', 'red', '/admin/participants', $sum ?? 0));
                }


            })->body($this->grid());

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
            ->body($this->form($id)->edit($id));
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
            ->body($this->form());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Event);
        if (!Admin::user()->isAdministrator()) {
            $grid->model()->where('owner_id', '=', Admin::user()->id);
        } else {
            $grid->column('owner_id', 'Owner')->editable();
        }
        $grid->actions(function ($actions) {
            $actions->disableView();
            $actions->append(new ActionExport($actions->getKey(), 'all', 'excel'));
            $actions->append(new ActionExportCardParticipant($actions->getKey(), 'Карточка участника'));
        });
        $event = Event::where('owner_id', '=', \Encore\Admin\Facades\Admin::user()->id)->where('active', 1)->first();
        if($event){
            $grid->tools(function (Grid\Tools $tools) {
                $tools->append(new BatchNotificationOfParticipant);
            });
        }
        $grid->disableFilter();
        $grid->disableExport();
        $grid->column('title', 'Название');
        $grid->column('link', 'Ссылка для всех')->link();
        $grid->column('admin_link', 'Ссылка на предпросмотр')->link();
        $grid->column('active', 'Активировать соревнование для просмотра и управления')->using([0 => 'Нет', 1 => 'Да'])->display(function ($title, $column) {
            If ($this->active == 0) {
                return $column->label('default');
            } else {
                return $column->label('success');
            }
        });
        $grid->column('is_registration_state', 'Регистрация')->using([0 => 'Закрыта', 1 => 'Открыта'])->display(function ($title, $column) {
            If ($this->is_registration_state == 0) {
                return $column->label('default');
            } else {
                return $column->label('success');
            }
        });
        $grid->column('is_send_result_state', 'Отправка результатов')->using([0 => 'Закрыта', 1 => 'Открыта'])->display(function ($title, $column) {
            If ($this->is_send_result_state == 0) {
                return $column->label('default');
            } else {
                return $column->label('success');
            }
        });
        $grid->column('is_public', 'Опубликовать для всех')->using([0 => 'Нет', 1 => 'Да'])->display(function ($title, $column) {
            If ($this->is_public == 0) {
                return $column->label('default');
            } else {
                return $column->label('success');
            }
        });
        return $grid;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form($id = null)
    {

        $form = new Form(new Event);
        $form->tab('Общая информация о соревновании', function ($form) {

            $this->install_admin_script();
            $form->footer(function ($footer) {
//                $footer->disableReset();
                $footer->disableSubmit();
                $footer->disableViewCheck();
                $footer->disableEditingCheck();
                $footer->disableCreatingCheck();

            });
            $form->hidden('owner_id')->value(Admin::user()->id);
            $form->text('title', 'Название соревнования')->placeholder('Введи название')->required();
//            $form->text('subtitle', 'Надпись под названием')->placeholder('Введи название');
            $form->hidden('title_eng')->default('1');
            $form->text('climbing_gym_name', 'Название скалодрома')->value(Admin::user()->climbing_gym_name)->placeholder('Название скалодрома')->required();
            $form->hidden('climbing_gym_name_eng')->default('1');
            $form->text('city', 'Город')->value(Admin::user()->city)->placeholder('Город')->required();
            $form->text('address', 'Адрес')->value(Admin::user()->address)->placeholder('Адрес')->required();
            $form->date('start_date', 'Дата старта')->attribute('inputmode', 'none')->placeholder('гггг:мм:дд')->required();
            # Добавить в будущем автоматическое открытие регистрации
//            $form->date('start_open_registration_date', 'Дата открытия регистрации')->attribute('inputmode', 'none')->placeholder('гггг:мм:дд');
            $form->date('end_date', 'Дата окончания')->attribute('inputmode', 'none')->placeholder('гггг:мм:дд')->required();
//            $form->time('start_time', 'Время старта')->attribute('inputmode', 'none')->placeholder('Время старта')->required();
//            $form->time('end_time', 'Время окончания')->attribute('inputmode', 'none')->placeholder('Время окончания')->required();
            $form->image('image', 'Афиша')->placeholder('Афиша')->attribute('inputmode', 'none');
            $form->summernote('description', 'Описание')->placeholder('Описание')->required();
            $form->text('contact', 'Телефон')->required();
            $form->text('contact_link', 'Ссылка на соц.сеть');
            $form->hidden('link', 'Ссылка на сореванование')->placeholder('Ссылка');
            $form->hidden('admin_link', 'Ссылка на сореванование')->placeholder('Ссылка');
//            $form->disableSubmit();
        })->tab('Оплата', function ($form) {
            $form->url('link_payment', 'Ссылка на оплату')->placeholder('Ссылка');
            $form->image('img_payment', 'QR код на оплату')->attribute('inputmode', 'none')->placeholder('QR');
            $form->text('amount_start_price', 'Сумма стартового взноса')->placeholder('сумма')->required();
            $form->textarea('info_payment', 'Доп инфа об оплате')->rows(10)->placeholder('Инфа...');
        })->tab('Параметры соревнования', function ($form) use ($id) {
            $form->radio('is_qualification_counting_like_final','Настройка подсчета квалификации')
                ->options([
                    0 =>'Фестивальная система(Баллы и коэффициенты)',
                    1 =>'Француская система(Топ и Зона)',
                ])->when(0, function (Form $form) {
                    $formats = Format::all()->pluck('format', 'id');
                    $form->radio('mode','Настройка формата')
                        ->options($formats)->when(1, function (Form $form) {
                            $form->number('mode_amount_routes','Кол-во трасс лучших трасс для подсчета')->attribute('inputmode', 'none')->value(10);
                        })->when(2, function (Form $form) {
                            $form->text('amount_point_flash','Балл за флэш')->value(1);
                            $form->text('amount_point_redpoint','Балл за редпоинт')->value(0.9);
                        });
                    $form->radio('is_semifinal','Настройка кол-ва стадий соревнований')
                        ->options([
                            1 =>'С полуфиналом',
                            0 =>'Без полуфинала',
                        ])->when(1, function (Form $form) {
                            $form->number('amount_the_best_participant','Кол-во лучших участников идут в след раунд полуфинал')
                                ->help('Если указано число например 6, то это 6 мужчин и 6 женщин')->value(6);
                            $form->number('amount_routes_in_semifinal','Кол-во трасс в полуфинале')->attribute('inputmode', 'none')->value(5);
                            $form->radio('is_additional_semifinal','Полуфиналы для разных групп')
                                ->options([
                                    1 =>'Подсчет результатов полуфинала по полу и по категории участников',
                                    0 =>'Подсчет результатов полуфинала по полу',
                                ])->default(0)->required();
                        })->when(0, function (Form $form) {

                        })->value(1)->required();
                })->when(1, function (Form $form) {
                    $form->number('amount_routes_in_qualification_like_final','Кол-во трасс в квалификации')->attribute('inputmode', 'none')->value(10);
                })->value(1)->required();
            $form->radio('is_additional_final','Финалы для разных групп')
                ->options([
                    1 =>'Подсчет результатов финала по полу и по категории участников',
                    0 =>'Подсчет результатов финала по полу',
                ])->value(0)->required();
            $form->number('amount_the_best_participant_to_go_final','Кол-во лучших участников идут в след раунд финал')
                ->help('Если указано число например 6, то это 6 мужчин и 6 женщин')->value(6);
            $form->number('amount_routes_in_final','Кол-во трасс в финале')->attribute('inputmode', 'none')->value(4);
            if($this->is_fill_results(intval($id))){
                $form->html('<h5> Доступно только изменение категорий, так как были добавлены результаты, нельзя удалить или добавить новые</h5>');
                $form->customlist('categories', 'Категории участников');
            } else {
                $form->list('categories', 'Категории участников')->rules('required|min:2');

                $form->radio('is_auto_categories','Настройка категорий')
                    ->options([0 => 'Сами участники выбирают категорию при регистрации', 1 => 'Автоопределение категории по параметрам'])
                    ->when(0, function (Form $form) {
                    })->when(1, function (Form $form) {
                        $form->html('<h4 style="color: red" >Автоопределение категории по параметрам работает только при фестивальной системе</h4>');
                        $form->table('options_categories', '', function ($table) use ($form){
                            $table->select('Категория участника')->options($form->model()->categories)->readonly();
                            $table->select('От какой категории сложности определять эту категорию')->options(Grades::getGrades())->width('30px');
                            $table->select('До какой категории сложности определять эту категорию')->options(Grades::getGrades())->width('30px');
                        });
                    })->value(0)->required();
                $form->html('<h4 id="warning-category" style="color: red" >Обязательно проверьте заполнение категорий и обязательных полей</h4>');
            }
            $form->switch('is_input_birthday', 'Обязательное наличие возраста участника')->states(self::STATES_BTN);
            $form->switch('is_need_sport_category', 'Обязательное наличие разряда')->states(self::STATES_BTN);

        })->tab('Управление соревнованием', function ($form) use ($id){
            $form->switch('is_registration_state', 'Регистрация ')->help('Закрыть вручную')->states(self::STATES_BTN_OPEN_AND_CLOSE);
            $form->switch('is_need_pay_for_reg', 'Включить оплату для регистрации')
                ->help('Например оплата будет происходит в другом месте или оплачивается только вход')
                ->states(self::STATES_BTN)->default(1);
            $form->number('registration_time_expired', 'Через сколько дней сгорит регистрации без оплаты')->help('Если 0 то сгорать не будет')->default(0);
            $form->datetime('datetime_registration_state', 'Дата закрытия регистрации [AUTO]')->help('Обновление статуса каждый час, например время закрытия 21:40 статусы обновятся в 22:00')->attribute('inputmode', 'none')->placeholder('дата и время');
            $form->switch('is_send_result_state', 'Отправка результатов')->help('Закрыть вручную')->states(self::STATES_BTN_OPEN_AND_CLOSE);
            $form->datetime('datetime_send_result_state', 'Дата закрытия отправки результатов [AUTO]')->help('Обновление статуса каждый час, например время закрытия 21:40 статусы обновятся в 22:00')->attribute('inputmode', 'none')->placeholder('дата и время');
            $event = Event::where('owner_id', '=', Admin::user()->id)->where('active', '=', 1)->first();
            if(!$event){
                $help = 'Самая важная функция, чтобы текущие сореванование отображались во вкладках "Квалификация,полуфинал,финал"
                нужно активировать';
                $form->switch('active', 'Активировать соревнование для просмотра и управления')
                    ->help($help)
                    ->states(self::STATES_BTN);
            } else {
                if($event->id != intval($id)){
                    $help = 'Только одно соревнование может быть активировано для управление, если нельзя нажать значит какое-то соревнование уже активировано';
                    $form->switch('active', 'Активировать соревнование для просмотра и управления')
                        ->help($help)
                        ->states(self::STATES_BTN)
                        ->readOnly();
                } else {
                    $help = 'Самая важная функция, чтобы текущие сореванование отображались во вкладках "Квалификация,полуфинал,финал"
                нужно активировать';
                    $form->switch('active', 'Активировать соревнование для просмотра и управления')
                        ->help($help)
                        ->states(self::STATES_BTN);
                }
            }
            $exist_routes = Route::where('event_id', $id)->first();
            if($exist_routes){
                $form->switch('is_public', 'Опубликовать для всех')
                    ->help('После включения, все смогут зайти на страницу с соревнованиями')
                    ->states(self::STATES_BTN);
            } else {
                $help = 'Необходимо добавить трассы в разделе настройка трасс, после этого можно опубликовать для всех';
                $form->switch('is_public', 'Опубликовать для всех')
                    ->help($help)
                    ->states(self::STATES_BTN)->readOnly();
            }
            $form->html('<button class="btn btn-primary" id="create-event" type="submit" data-bs-toggle="tooltip" data-bs-placement="top" title="Заполните обязательные поля">Отправить</button>');
            $form->html('<h5 id="create-warning" style="color: red" >Если кнопка некликабельна заполните обязательные поля</h5>');
        });

        $form->tools(function (Form\Tools $tools) {
            $tools->disableList();
            $tools->disableDelete();
            $tools->disableView();
        });
        $form->saving(function (Form $form) {
            if(!$form->is_semifinal){
                $form->is_semifinal = 0;
            }
            if ($form->active === "1" || $form->active === "on") {
                $events = Event::where('owner_id', '=', Admin::user()->id)->where('active', '=', 1)->first();
                if($events && $events->id != $form->model()->id){
                    throw new \Exception('Только одно соревнование может быть опубликовано');
                }
            }
            if($form->climbing_gym_name){
                $climbing_gym_name_eng = str_replace(' ', '-', (new \App\Models\Event)->translate_to_eng($form->climbing_gym_name));
                $title_eng = str_replace(' ', '-', (new \App\Models\Event)->translate_to_eng($form->title));
                $form->climbing_gym_name_eng =  $climbing_gym_name_eng;
                $form->title_eng = $title_eng;
                $form->link = '/event/'.$form->start_date.'/'.$climbing_gym_name_eng.'/'.$title_eng;
                $form->admin_link = '/admin/event/'.$form->start_date.'/'.$climbing_gym_name_eng.'/'.$title_eng;
            }
        });
        $form->saved(function (Form $form) {
            if($form->categories){
                $categories = ParticipantCategory::where('owner_id', '=', Admin::user()->id)
                    ->where('event_id', '=', $form->model()->id)->get();
                #  Заменяем если категории которые уже были не изменяя ID
                if($categories->isNotEmpty()){
                    foreach ($form->categories['values'] as $index => $category){
                        if(isset($categories[$index])){
                            $categories[$index]->category = $category;
                        } else {
                            $participant_category = new ParticipantCategory;
                            $participant_category->category = $category;
                            $categories->push($participant_category);
                        }
                    }
                    foreach ($categories as $category){
                        $participant_category = ParticipantCategory::where('owner_id', '=', Admin::user()->id)
                            ->where('event_id', '=', $form->model()->id)->where('id', '=', $category->id)->first();
                        # Если в входящей форме пришли значение которых нет в БД до значит их удалили
                        if(array_search($category->category, $form->categories['values']) === false){
                            ParticipantCategory::where('owner_id', '=', Admin::user()->id)
                                ->where('event_id', '=', $form->model()->id)->where('category', '=', $category->category)->delete();
                        }
                        if(!$participant_category){
                            $participant_category = new ParticipantCategory;
                            $participant_category->owner_id = Admin::user()->id;
                            $participant_category->event_id = $form->model()->id;
                        }
                        $participant_category->category = $category->category;
                        $participant_category->save();
                    }
                } else {
                    foreach ($form->categories as $category){
                        foreach ($category as $c){
                            $participant_categories = new ParticipantCategory;
                            $participant_categories->owner_id = Admin::user()->id;
                            $participant_categories->event_id = $form->model()->id;
                            $participant_categories->category = $c;
                            $participant_categories->save();
                         }
                    }

                }
                $exist_sets = Set::where('owner_id', '=', Admin::user()->id)->first();
                if(!$exist_sets) {
                    $this->install_set(Admin::user()->id);
                }
                return back()->isRedirect('events');
            }
            return $form;
        });
        return $form;
    }



    public function exportAllExcel(Request $request)
    {
        $file_name = 'Полные результаты.xlsx';
        $result = Excel::download(new AllResultExport($request->id), $file_name, \Maatwebsite\Excel\Excel::XLSX);
        return response()->download($result->getFile(), $file_name, [
            'Content-Type' => 'application/xlsx',
        ]);
    }
    public function exportAllnCsv(Request $request)
    {
        $file_name = 'Полные результаты.csv';
        $result = Excel::download(new AllResultExport($request->id), $file_name, \Maatwebsite\Excel\Excel::CSV);
        return response()->download($result->getFile(), $file_name, [
            'Content-Type' => 'application/csv',
        ]);
    }
    public function exportAllOds(Request $request)
    {
        $file_name = 'Полные результаты.ods';
        $result = Excel::download(new AllResultExport($request->id), $file_name, \Maatwebsite\Excel\Excel::ODS);
        return response()->download($result->getFile(), $file_name, [
            'Content-Type' => 'application/ods',
        ]);
    }

    public function is_fill_results($event_id)
    {
        $result = false;
        if(Participant::where('event_id',$event_id)->first()){
            $result = true;
        }
        if(ResultRouteQualificationLikeFinal::where('event_id',$event_id)->first()){
            $result = true;
        }
        return $result;
    }
    public function install_set($owner_id){
        $sets = array(
            ['owner_id' => $owner_id, 'time' => '10:00-12:00','max_participants' => 35, 'day_of_week' => 'Friday','number_set' => 1],
            ['owner_id' => $owner_id, 'time' => '13:00-15:00','max_participants' => 35, 'day_of_week' => 'Friday','number_set' => 2],
            ['owner_id' => $owner_id, 'time' => '13:00-15:00','max_participants' => 35, 'day_of_week' => 'Saturday','number_set' => 6],
            ['owner_id' => $owner_id, 'time' => '16:00-18:00','max_participants' => 35, 'day_of_week' => 'Friday','number_set' => 3],
            ['owner_id' => $owner_id, 'time' => '16:00-18:00','max_participants' => 35, 'day_of_week' => 'Saturday','number_set' => 7],
            ['owner_id' => $owner_id, 'time' => '20:00-22:00','max_participants' => 35, 'day_of_week' => 'Friday','number_set' => 4],
            ['owner_id' => $owner_id, 'time' => '20:00-22:00','max_participants' => 35, 'day_of_week' => 'Saturday','number_set' => 8],
            ['owner_id' => $owner_id, 'time' => '10:00-12:00','max_participants' => 35, 'day_of_week' => 'Saturday','number_set' => 5],
        );
        DB::table('sets')->insert($sets);
    }

    public function install_admin_script()
    {
        Admin::style(".select2-selection__arrow {
                display: None;
            }");
        Admin::script("

            $(document).ready(function() {

         if(window.location.href.indexOf(\"edit\") > -1)
            {
                 return
            }


      const submitButton = document.querySelector('#create-event');
      const requiredInputs = document.querySelectorAll('input[required]');
      const requiredRadio = document.querySelectorAll('radio[required]');
      if(!submitButton || !requiredInputs || !requiredRadio){
        return;
      }
      if(submitButton){
         submitButton.disabled = true;
      }
      function checkInputs() {
        let isValid = true;
        requiredInputs.forEach(input => {
          if (input.value.trim() === '') {
            isValid = false;
          }
        });
        requiredRadio.forEach(input => {
          if (input.value.trim() === '') {
            isValid = false;
          }
        });

        if (isValid) {
          submitButton.disabled = false;
          let input_warning = document.querySelector('#create-warning');
          if (input_warning) {
            input_warning.remove();
                }
        } else {
          submitButton.disabled = true;
        }
      }

      requiredInputs.forEach(input => {
        input.addEventListener('input', checkInputs);
        input.addEventListener('click', checkInputs);
      });

    });");
        Admin::script(

            "
            $(document).ready(function() {

            if(window.location.href.indexOf(\"edit\") > -1)
            {
                const warning = document.querySelector('#warning-category');
                const input_warning = document.querySelector('#create-warning');
                if (warning) {
                    warning.remove();
                }
                if (input_warning) {
                    input_warning.remove();
                }
                 return
            }
    const radioButtonHandler = function() {
        const inputName = $(this).attr('id');
        const inputClass = $(this).attr('class');
        const existingValue = Cookies.get(inputName);
        if (existingValue !== inputClass) {
            Cookies.set(inputName, inputClass, { expires: 7 });
        }
    };

    const restoreSwitch = function(name) {
        const value = Cookies.get(name);
        if (value === 'on') {
            const xpath = `//input[contains(@class, \"\${name}\")]`;
            const switchElements = getElementsByXPath(xpath);
            if (switchElements.length > 0) {
                switchElements[0].click(); // Кликаем на первый найденный элемент
            }
        }
    };

    const restoreRadioButtons = function(name) {
        const inputClass = Cookies.get(name);
        const radio0 = name + '0';
        const radio1 = name + '1';
        const radio1Element = document.querySelector('.' + radio1);
        const radio0Element = document.querySelector('.' + radio0);

        if (!inputClass) {
            if (radio1Element) {
                radio1Element.click();
            }
        } else {
            if (radio1Element) {
                radio1Element.click();
            }
            if (radio0Element && inputClass === radio0) {
                radio0Element.click();
            }
        }
    };

    const restoreInputValues = function() {
        $('form').find('input:not([type=\"file\"]), select, textarea').each(function() {
            const inputName = $(this).attr('name');
            if (!inputName) return; // Проверяем, существует ли атрибут name
            const savedValue = Cookies.get(inputName);
            if (savedValue) {
                $(this).val(savedValue);
            }
        });
    };

    restoreSwitch('active');
    restoreSwitch('is_input_birthday');
    restoreSwitch('is_need_sport_category');
    restoreRadioButtons('is_semifinal');
    restoreRadioButtons('is_qualification_counting_like_final');
    restoreRadioButtons('is_additional_final');
    restoreRadioButtons('is_additional_semifinal');
    restoreRadioButtons('mode');
    restoreInputValues();

    $('.categories-add').on('click', function() {
        document.querySelector('[type=submit][class=\"btn btn-primary\"]').removeAttribute('disabled');
        const warning = document.querySelector('#warning-category');
        if (warning) {
            warning.remove();
        }

        $('.list-categories-table').find('input').on('input change', function() {
            let index = 0;
            getElementsByXPath('//input[contains(@name, \"categories[values][]\")]')
                .forEach(input => {
                    const existingValue = getCookieValue(input.name);
                    if (existingValue !== input.name) {
                        const inputName = input.name + index;
                        Cookies.set(inputName, input.value, { expires: 7 });
                    }
                    index++;
                });
        });
    });

    $('form').find('input, select').on('input change click', function() {
        const inputName = $(this).attr('name');
        if (!inputName || inputName.startsWith(\"categories\") || inputName.startsWith(\" categories\")) {
            return;
        }
        const inputValue = $(this).val();
        Cookies.set(inputName, inputValue, { expires: 7 });
    });

    $('#start_time, #start_date, #end_time, #end_date').on('click', function() {
        const inputName = $(this).attr('name');
        const inputValue = $(this).val();
        Cookies.set(inputName, inputValue, { expires: 7 });
    });

    $('.categories-remove').on('click', function() {
        const dataId = $(this).attr('data-id').trim();
        Cookies.remove(dataId);
    });

    $('form').submit(function() {
        if (window.location.href.indexOf(\"edit\") === -1) {
            clearDraft();
        }
    });

    $('[type=submit]').on('click', function() {
        if (window.location.href.indexOf(\"edit\") === -1) {
            clearDraft();
        }
    });

    function clearDraft() {
        document.cookie.split(';').forEach(cookie => {
            const cookieName = cookie.split('=')[0].trim();
            Cookies.remove(cookieName);
        });
    }

    function getCookieValue(name) {
        const allcookies = document.cookie;
        const cookiearray = allcookies.split(';');
        for (let i = 0; i < cookiearray.length; i++) {
            const get_name = cookiearray[i].split('=')[0].trim();
            if ((get_name.startsWith(\"categories\") || get_name.startsWith(\" categories\")) && name === get_name) {
                return get_name;
            }
        }
    }

    function getElementsByXPath(xpath) {
        const results = [];
        const query = document.evaluate(xpath, document, null, XPathResult.UNORDERED_NODE_SNAPSHOT_TYPE, null);
        for (let i = 0; i < query.snapshotLength; i++) {
            results.push(query.snapshotItem(i));
        }
        return results;
    }
});

");
    }
}
