<?php

namespace App\Admin\Controllers;

use App\Admin\CustomAction\ActionExport;
use App\Admin\Extensions\CustomButton;
use App\Exports\AllResultExport;
use App\Models\Event;
use App\Http\Controllers\Controller;
use App\Models\Format;
use App\Models\Grades;
use App\Models\ParticipantCategory;
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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\MessageBag;
use Maatwebsite\Excel\Facades\Excel;

class EventsController extends Controller
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
        return $content->row(function ($row) {
                $row->column(3, new InfoBox('New Users', 'users', 'aqua', '/admin/users', '1024'));
                $row->column(3, new InfoBox('New Orders', 'shopping-cart', 'green', '/admin/orders', '150%'));
                $row->column(3, new InfoBox('Articles', 'book', 'yellow', '/admin/articles', '2786'));
                $row->column(3, new InfoBox('Documents', 'file', 'red', '/admin/files', '698726'));
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
//            $actions->append(new ActionExport($actions->getKey(), 'all', 'csv'));
//            $actions->append(new ActionExport($actions->getKey(), 'all', 'ods'));
        });
        $grid->disableFilter();
        $grid->disableExport();
//        $grid->disableColumnSelector();
        $grid->column('title', 'Название');
        $grid->column('link', 'Ссылка')->link();
        $states = [
            'on' => ['value' => 1, 'text' => 'Да', 'color' => 'success'],
            'off' => ['value' => 0, 'text' => 'Нет', 'color' => 'default'],
        ];
        $grid->column('active', 'Активно')->switch($states);

        return $grid;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Event);
        $form->tab('Общая информация о соревновании', function ($form) {
            Admin::style(".select2-selection__arrow {
                display: None;
            }");
            Admin::script("$(document).ready(function() {
      const submitButton = document.querySelector('.pull-right [type=\"submit\"]');
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
        } else {
          submitButton.disabled = true;
        }
      }

      requiredInputs.forEach(input => {
        input.addEventListener('input', checkInputs);
        input.addEventListener('click', checkInputs);
      });

    });");
            Admin::script("$(document).ready(function() {
    var editingAreas = $('.note-editable');

    editingAreas.each(function(index) {
        $(this).attr('data-id', 'editableArea_' + (index + 1));
    });

    // Отслеживание изменений в тексте каждой редактируемой области
    editingAreas.on('input', function() {
        var content = $(this).html();
        var areaId = $(this).attr('data-id');
        saveDraft(areaId, content);
    });

    // Восстановление данных редактируемой области из cookies при загрузке страницы
    editingAreas.each(function() {
        var areaId = $(this).attr('data-id');
        var savedContent = getCookie(areaId);
        if (savedContent) {
            $(this).html(savedContent); // Восстановление данных
        }
    });

    document.querySelectorAll('#is_qualification_counting_like_final').forEach(input => {
            input.addEventListener('click', radio_button);
      });
      function radio_button() {
            var inputName = $(this).attr('id');
            var inputClass = $(this).attr('class');
            var existingValue = getCookie(inputName);
            if (existingValue !== inputClass) {
                saveDraft(inputName, inputClass);
            }
        };
    document.querySelectorAll('#is_semifinal').forEach(input => {
            input.addEventListener('click', radio_button);
            });
    document.querySelectorAll('#is_additional_final').forEach(input => {
            input.addEventListener('click', radio_button);
            });
    document.querySelectorAll('#mode').forEach(input => {
            input.addEventListener('click', radio_button);
            });
     function radio_button() {
            var inputName = $(this).attr('id');
            var inputClass = $(this).attr('class');
            var existingValue = getCookie(inputName);
            if (existingValue !== inputClass) {
                saveDraft(inputName, inputClass);
            }
        };
    restoreSwitch('active')
    restoreSwitch('is_input_birthday')
    restoreSwitch('is_need_sport_category')
    function getElementByXpath(path) {
        return document.evaluate(path, document, null, XPathResult.FIRST_ORDERED_NODE_TYPE, null).singleNodeValue;
    }
    function restoreSwitch(name){
        var value = getCookie(name);
        if(value === 'on'){
            getElementByXpath('//input[contains(@class, \"'+name+'\")]/..//span[contains(@class, \"bootstrap-switch-handle-off bootstrap-switch-default\")]').click()
        }
    }
    restoreRadioButtons('is_semifinal')
    restoreRadioButtons('is_qualification_counting_like_final')
    restoreRadioButtons('is_additional_final')
    restoreRadioButtons('mode')
    function restoreRadioButtons (name) {
        var inputClass = getCookie(name)
        let radio0 = name+'0'
        let radio1 = name+'1'
         if(!inputClass){
            document.querySelector('.'+ radio1).click()
        } else {
            if(inputClass == radio1){
                document.querySelector('.'+ radio1).click()
            } else {
                document.querySelector('.'+ radio1).click()
                let r = document.querySelector('.'+ radio0)
                if(r){
                    document.querySelector('.'+ radio0).click()
                }
            }
        }
    }
    // Отслеживание изменений в input и select элементах формы
    $('form').find('input, select').on('input change click', function() {
        var inputName = $(this).attr('name');
        var inputValue = $(this).val();
        saveDraft(inputName, inputValue);
    });
    $('form').find('textarea').on('input change', function() {
        var inputName = $(this).attr('name');
        var inputValue = $(this).val();
        saveDraft(inputName, inputValue);
    });
    $('#start_time').on('click', function() {
        var inputName = $(this).attr('name');
        var inputValue = $(this).val();
        saveDraft(inputName, inputValue);
    });
    // Отслеживание кликов по input элементам для выбора дат и других выборов
      $('#start_time').on('click', function() {
        var inputName = $(this).attr('name');
        var inputValue = $(this).val();
        saveDraft(inputName, inputValue);
    });
    $('#start_date').on('click', function() {
        var inputName = $(this).attr('name');
        var inputValue = $(this).val();
        saveDraft(inputName, inputValue);
    });
    $('#end_time').on('click', function() {
        var inputName = $(this).attr('name');
        var inputValue = $(this).val();
        saveDraft(inputName, inputValue);
    });
    $('#end_date').on('click', function() {
        var inputName = $(this).attr('name');
        var inputValue = $(this).val();
        saveDraft(inputName, inputValue);
    });

    // Функция для сохранения данных каждого инпута и селекта в cookies
    function saveDraft(inputName, inputValue) {
        if(inputName === 'categories[values][]'){
            return;
        }
        var existingValue = getCookie(inputName);
        if (existingValue !== inputValue) {
            document.cookie = encodeURIComponent(inputName) + '=' + encodeURIComponent(inputValue);
        }
    }
    // Восстановление данных каждого инпута и селекта из cookies при загрузке страницы
    $('form').find('input:not([type=\"file\"]), input:not([type=\"radio\"]), select, textarea').each(function() {
        var inputName = $(this).attr('name');
        if(inputName === 'is_qualification_counting_like_final'){
            return;
        }
        if(inputName === 'is_semifinal'){
            return;
        }
        if(inputName === 'is_additional_final'){
            return;
        }
        if(inputName === 'mode'){
            return;
        }
        var savedValue = getCookie(inputName);
        if (savedValue) {
            $(this).val(savedValue); // Восстановление данных
        }
    });
    // Очистка данных черновика при успешной отправке формы
    $('form').submit(function() {
        clearDraft();
    });
     $('[type=submit]').on('click', function() {
       clearDraft();
    });
    // Функция для очистки данных черновика

    function clearDraft() {
        var cookies = document.cookie.split(';');
        for (var i = 0; i < cookies.length; i++) {
            var cookie = cookies[i];
            var eqPos = cookie.indexOf('=');
            var name = eqPos > -1 ? cookie.substr(0, eqPos) : cookie;
            document.cookie = name.trim() + '=;expires=Thu, 01 Jan 1970 00:00:00 GMT';
        }
    }
    // Вспомогательная функция для получения значения cookie по имени
    function getCookie(name) {
        var match = document.cookie.match(new RegExp('(^| )' + encodeURIComponent(name) + '=([^;]+)'));
        if (match) {
            return decodeURIComponent(match[2]);
        }
        return null;
    }
    if(getCookie('title') !== null){
        document.getElementById('create-events-link').textContent = 'Черновик соревнования'
    }
});");
            $form->footer(function ($footer) {
//                $footer->disableReset();
                $footer->disableViewCheck();
                $footer->disableEditingCheck();
                $footer->disableCreatingCheck();

            });
            $form->hidden('owner_id')->value(Admin::user()->id);
           // $form->html('<h4 style="color: orange">Внимание!</h4>');
           // $form->html('<h4 style="color: red">Уход со страницы на другие страницы может повлечь потерю данных в форме на некоторых полях</h4>');
            $form->text('title', 'Название соревнования')->placeholder('Введи название')->required();
//            $form->text('subtitle', 'Надпись под названием')->placeholder('Введи название');
            $form->hidden('title_eng')->default('1');
            $form->text('climbing_gym_name', 'Название скалодрома')->value(Admin::user()->climbing_gym_name)->placeholder('Название скалодрома')->required();
            $form->hidden('climbing_gym_name_eng')->default('1');
            $form->text('city', 'Город')->value(Admin::user()->city)->placeholder('Город')->required();
            $form->text('address', 'Адрес')->value(Admin::user()->address)->placeholder('Адрес')->required();
            $form->date('start_date', 'Дата старта')->placeholder('Дата старта')->required();
            $form->date('end_date', 'Дата окончания')->placeholder('Дата окончания')->required();
            $form->time('start_time', 'Время старта')->placeholder('Время старта')->required();
            $form->time('end_time', 'Время окончания')->placeholder('Время окончания')->required();

            $form->image('image', 'Афиша')->placeholder('Афиша')->required();
            $form->summernote('description', 'Описание')->placeholder('Описание')->required();
            $form->text('contact', 'Контактная информация')->required();

            $form->hidden('link', 'Ссылка на сореванование')->placeholder('Ссылка');


//            $form->disableSubmit();
        })->tab('Оплата', function ($form) {
            $form->url('link_payment', 'Ссылка на оплату')->placeholder('Ссылка');
            $form->image('img_payment', 'QR код на оплату')->placeholder('QR');
            $form->text('amount_start_price', 'Сумма стартового взноса')->placeholder('сумма')->required();
            $form->textarea('info_payment', 'Доп инфа об оплате')->rows(10)->placeholder('Инфа...');
        })->tab('Настройка Трасс', function ($form) {
            $routes = Grades::getRoutes();
            $form->html('<h4>Ценность трассы учитывается только в формате соревнований n лучших трасс, там необходимо искать лучшие трассы по баллам <br>
                                Для других режимов ценность не учитывается, можно просто игнорировать это поле</h4>');
            $form->tablecustom('grade_and_amount', '', function ($table) {
                $grades = $this->getGrades();
                $table->select('Категория')->options($grades)->readonly();
                $table->number('Кол-во')->width('50px');
                $table->text('Ценность')->width('50px');
                $table->disableButton();
            })->value($routes);

        })->tab('Параметры соревнования', function ($form) {
            $form->html('<p>*Классика - квалификация и полуфинал/финал для лучших в квалификации, </p>');
            $form->html('<p>*Как финальный раунд - то есть квалификация будет считаться как по кол-ву топов и зон </p>');
            $form->radio('is_qualification_counting_like_final','Настройка подсчета квалификации')
                ->options([
                    0 =>'Считаем по классике',
                    1 =>'Считаем как финальный раунд (кол-во топов и зон)',
                ])->when(0, function (Form $form) {
                    $form->number('amount_the_best_participant','Кол-во лучших участников идут в след раунд')
                        ->help('Если указано число например 6, то это 6 мужчин и 6 женщин')->value(6);
                    $form->text('amount_point_flash','Балл за флэш')->value(1);
                    $form->text('amount_point_redpoint','Балл за редпоинт')->value(0.9);
                    $formats = Format::all()->pluck('format', 'id');
                    $form->radio('mode','Настройка формата')
                        ->options($formats)->when(1, function (Form $form) {
                            $form->number('mode_amount_routes','Кол-во трасс лучших трасс для подсчета')->value(10);
                        })->when(2, function (Form $form) {
                        })->required();
                })->when(1, function (Form $form) {
                    $form->number('amount_routes_in_qualification_like_final','Кол-во трасс в квалификации')->value(10);
                    $form->number('amount_the_best_participant','Кол-во лучших участников идут в след раунд')
                        ->help('Если указано число например 6, то это 6 мужчин и 6 женщин')->value(6);
                })->required();
            $form->radio('is_semifinal','Настройка финалов')
                ->options([
                    1 =>'С полуфиналом',
                    0 =>'Без полуфинала',
                ])->when(1, function (Form $form) {
                    $form->number('amount_routes_in_semifinal','Кол-во трасс в полуфинале')->value(5);
                    $form->number('amount_routes_in_final','Кол-во трасс в финале')->value(4);
                })->when(0, function (Form $form) {
                    $form->number('amount_routes_in_final','Кол-во трасс в финале')->value(4);
                })->required();
            $form->radio('is_additional_final','Финалы для разных групп')
                ->options([
                    1 =>'С финалами для каждой категории групп',
                    0 =>'Классика финал для лучших в квалификации',
                ])->required();
            $form->list('categories', 'Категории участников')->value(['Новички', 'Общий зачет'])->rules('required|min:2')->required();
            $states = [
                'on' => ['value' => 1, 'text' => 'Да', 'color' => 'success'],
                'off' => ['value' => 0, 'text' => 'Нет', 'color' => 'default'],
            ];
            $form->switch('is_input_birthday', 'Обязательное наличие возраста участника')->states($states);
            $form->switch('is_need_sport_category', 'Обязательное наличие разряда')->states($states);
            $form->switch('active', 'Опубликовать')
                ->help('Не обязательно сразу опубликовывать, после сохранения будет ссылка по которой можно будет посмотреть')
                ->states($states);
        });
        $form->tools(function (Form\Tools $tools) {

            // Disable `List` btn.
            $tools->disableList();

            // Disable `Delete` btn.
            $tools->disableDelete();

            // Disable `Veiw` btn.
            $tools->disableView();
        });
        $form->saving(function (Form $form) {
            if ($form->active === "1" || $form->active === "on") {
                $events = Event::where('owner_id', '=', Admin::user()->id)->where('active', '=', 1)->first();
                if($events && $events->id != $form->model()->id){
                    throw new \Exception('Только одно соревнование может быть опубликовано');
                }
            }
            if($form->grade_and_amount){
                $main_count = 0;
                foreach ($form->grade_and_amount as $route){
                    for ($count = 1; $count <= $route['Кол-во']; $count++){
                        $main_count++;
                    }
                }
                $form->count_routes = $main_count;
                $count = 0;
                foreach ($form->grade_and_amount as $value){
                    $count += intval($value["Кол-во"]);
                }
                if (intval($form->count_routes) != $count) {
                    throw new \Exception('Кол-во трасс '.$form->count_routes. ' Категория и Кол-во '.$count.' должны быть одинаковыми');
                }
            }
            if($form->climbing_gym_name){
                $climbing_gym_name_eng = str_replace(' ', '-', (new \App\Models\Event)->translate_to_eng($form->climbing_gym_name));
                $title_eng = str_replace(' ', '-', (new \App\Models\Event)->translate_to_eng($form->title));
                $form->climbing_gym_name_eng =  $climbing_gym_name_eng;
                $form->title_eng = $title_eng;
                $form->link = '/event/'.$climbing_gym_name_eng.'/'.$title_eng;
            }
        });
        $form->saved(function (Form $form) {
            ParticipantCategory::where('owner_id', '=', Admin::user()->id)
                ->where('event_id', '=', $form->model()->id)->delete();
            if($form->categories){
                foreach ($form->categories as $category){
                    foreach ($category as $c){
                        $participant_categories = new ParticipantCategory;
                        $participant_categories->owner_id = Admin::user()->id;
                        $participant_categories->event_id = $form->model()->id;
                        $participant_categories->category = $c;
                        $participant_categories->save();
                    }
                }
                $exist_routes_list = Grades::where('owner_id', '=', Admin::user()->id)
                    ->where('event_id', '=', $form->model()->id)->first();
                if(!$exist_routes_list){
                    if ($form->grade_and_amount){
                        Event::generation_route(Admin::user()->id, $form->model()->id, $form->grade_and_amount);
                    }
                }
                $exist_sets = Set::where('owner_id', '=', Admin::user()->id)->first();
                if(!$exist_sets) {
                    $this->install_set(Admin::user()->id);
                }
                $success = new MessageBag([
                    'title'   => 'Соревнование успешно сохранено',
                    'message' => '',
                ]);

                return back()->with(compact('success'));
            }
            return $form;
        });
        return $form;
    }

    /**
     * @return array[]
     */
    protected function getGrades(): array
    {
        $grades = ['4' => '4','5' => '5', '5+' => '5+','6A' => '6A','6A+' => '6A+', '6B' => '6B', '6B+' => '6B+','6C' => '6C',
            '6C+' => '6C+','7A' => '7A','7A+' => '7A+','7B' => '7B','7B+' => '7B+','7C' => '7C','7C+' => '7C+','8A' => '8A'];
        return $grades;
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

    public function install_set($owner_id){
        $sets = array(
            ['owner_id' => $owner_id, 'time' => '10:00-12:00','max_participants' => 35, 'day_of_week' => 'friday','number_set' => 1],
            ['owner_id' => $owner_id, 'time' => '13:00-15:00','max_participants' => 35, 'day_of_week' => 'friday','number_set' => 2],
            ['owner_id' => $owner_id, 'time' => '13:00-15:00','max_participants' => 35, 'day_of_week' => 'saturday','number_set' => 6],
            ['owner_id' => $owner_id, 'time' => '16:00-18:00','max_participants' => 35, 'day_of_week' => 'friday','number_set' => 3],
            ['owner_id' => $owner_id, 'time' => '16:00-18:00','max_participants' => 35, 'day_of_week' => 'saturday','number_set' => 7],
            ['owner_id' => $owner_id, 'time' => '20:00-22:00','max_participants' => 35, 'day_of_week' => 'friday','number_set' => 4],
            ['owner_id' => $owner_id, 'time' => '20:00-22:00','max_participants' => 35, 'day_of_week' => 'saturday','number_set' => 8],
            ['owner_id' => $owner_id, 'time' => '10:00-12:00','max_participants' => 35, 'day_of_week' => 'saturday','number_set' => 5],
        );
        DB::table('sets')->insert($sets);
    }
}
