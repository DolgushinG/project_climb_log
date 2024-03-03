<?php

namespace App\Admin\Controllers;

use App\Admin\CustomAction\ActionExport;
use App\Exports\AllResultExport;
use App\Exports\QualificationResultExport;
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
use Encore\Admin\Show;
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
        return $content
            ->body($this->grid());
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
        $grid->column('count_routes', 'Кол-во маршрутов');
        $grid->column('title', 'Название');
        $grid->column('subtitle', 'Надпись под названием');
        $grid->column('link', 'Ссылка')->link();
        $states = [
            'on' => ['value' => 1, 'text' => 'Да', 'color' => 'success'],
            'off' => ['value' => 0, 'text' => 'Нет', 'color' => 'default'],
        ];
        $grid->column('active', 'Опубликовать')->switch($states);

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

        $form->tools(function (Form\Tools $tools) {

            // Disable `List` btn.
            $tools->disableList();

            // Disable `Delete` btn.
            $tools->disableDelete();

            // Disable `Veiw` btn.
            $tools->disableView();
        });

        $form->footer(function ($footer) {

            // disable reset btn
            $footer->disableReset();

            // disable `View` checkbox
            $footer->disableViewCheck();

            // disable `Continue editing` checkbox
            $footer->disableEditingCheck();

            // disable `Continue Creating` checkbox
            $footer->disableCreatingCheck();

        });

        $form->hidden('id');
        $form->hidden('owner_id')->value(Admin::user()->id);
        $form->date('start_date', 'Дата старта')->placeholder('Дата старта')->required();
        $form->date('end_date', 'Дата окончания')->placeholder('Дата окончания')->required();
        $form->time('start_time', 'Время старта')->placeholder('Время старта')->required();
        $form->time('end_time', 'Время окончания')->placeholder('Время окончания')->required();
        $form->text('address', 'Адрес')->value(Admin::user()->address)->placeholder('Адрес')->required();
        $form->file('document', 'Прикрепить документ')->placeholder('Прикрепить документ');
        $form->image('image', 'Афиша')->value('images/dada')->placeholder('Афиша')->required();
        $form->text('climbing_gym_name', 'Название скалодрома')->value(Admin::user()->climbing_gym_name)->placeholder('Название скалодрома')->required();
        $form->hidden('climbing_gym_name_eng')->default('1');
        $form->text('city', 'Город')->value(Admin::user()->city)->placeholder('Город')->required();
        $form->number('count_routes', 'Кол-во трасс по умалчанию 30 трасс **(Кол-во трасс должно совпадать с Категориями и их кол-вом)
        ')->options(['max' => 150, 'min' => 10, 'step' => 1, 'postfix' => ' маршрутов'])->default(30)->placeholder('Кол-во трасс')->required();
        $routes = $this->getRoutes();

        $form->tablecustom('grade_and_amount', '', function ($table) {
            $grades = $this->getGrades();
            $table->select('Категория')->options($grades)->readonly();
            $table->text('Кол-во')->width('50px');
            $table->text('Ценность')->width('50px');
            $table->disableButton();
        })->value($routes);

        $form->text('title', 'Название')->placeholder('Введи название')->required();
        $form->hidden('title_eng')->default('1');
        $form->text('subtitle', 'Надпись под названием')->placeholder('Введи название')->required();
        $form->hidden('link', 'Ссылка на сореванование')->placeholder('Ссылка');
        $form->url('link_payment', 'Ссылка на оплату')->placeholder('Ссылка');
        $form->image('img_payment', 'QR код на оплату')->placeholder('Ссылка');
        $form->summernote('description', 'Описание')->placeholder('Описание')->required();
        $form->radio('is_semifinal','Настройка финалов')
            ->options([
                1 =>'С полуфиналом',
                0 =>'Без полуфинала',
            ])->when(1, function (Form $form) {
                $form->number('amount_routes_in_semifinal','Кол-во трасс в полуфинале')->value(5);
                $form->number('amount_routes_in_final','Кол-во трасс в финале')->value(4);
            })->when(0, function (Form $form) {
                $form->number('amount_routes_in_final','Кол-во трасс в финале')->value(4);
            })->value(0)->required();
        $form->radio('is_additional_final','Финалы для разных групп')
            ->options([
                1 =>'С финалами для разных групп',
                0 =>'Классика финал для лучших в квалификации',
            ])->value(0)->required();
        $form->list('categories', 'Категории участников')->value(['Новички', 'Общий зачет'])->rules('required|min:2')->required();
//        $form->radio('choice_transfer','Настройка перевода участников в другую категорию')
//            ->options([1 => 'Ручной перевод по необходимости',2 => 'Настройка авто перевода в другую категорию'])->when(1, function (Form $form) {
//            })->when(2, function (Form $form) {
//                $form->table('transfer_to_next_category', '', function ($table) use ($form){
//                    $table->select('Категория участника')->options($form->model()->categories)->readonly();
//                    $table->select('В какую категорию переводить')->options($form->model()->categories)->readonly();
//                    $table->select('От какой категории будет перевод')->options($this->getGrades())->width('30px');
//                    $table->number('Кол-во трасс для перевода')->width('50px');
//                });
//            })->required();
        $formats = Format::all()->pluck('format', 'id');
        $form->radio('mode','Настройка формата')
            ->options($formats)->when(1, function (Form $form) {
                $form->number('mode_amount_routes','Кол-во трасс лучших трасс для подсчета')->value(10);
            })->when(2, function (Form $form) {
            })->required();
//        $form->select('mode', 'Формат')->options([1 => '10 лучших трасс', 2 => 'Все трассы'])->required();
//        $form->switch('active', 'Опубликовать сразу?');
        $form->switch('active', 'Опубликовать сразу?');
        $form->saving(function (Form $form) {
            if ($form->active === "1" || $form->active === "on") {
                $events = Event::where('owner_id', '=', Admin::user()->id)->where('active', '=', 1)->first();
                if($events && $events->id != $form->model()->id){
                    throw new \Exception('Только одно соревнование может быть опубликовано');
                }
            }
            $count = 0;
            if($form->grade_and_amount){
                foreach ($form->grade_and_amount as $value){
                    $count += intval($value["Кол-во"]);
                }
                if (intval($form->count_routes) != $count) {
                    throw new \Exception('Кол-во трасс '.$form->count_routes. ' Категория и Кол-во '.$count.' должны быть одинаковыми');
                }
                $climbing_gym_name_eng = str_replace(' ', '-', (new \App\Models\Event)->translate_to_eng($form->climbing_gym_name));
                $title_eng = str_replace(' ', '-', (new \App\Models\Event)->translate_to_eng($form->title));
                $form->climbing_gym_name_eng =  $climbing_gym_name_eng;
                $form->title_eng = $title_eng;
                $form->link = '/event/'.$climbing_gym_name_eng.'/'.$title_eng;
            }

        });
        $form->saved(function (Form $form) {
            if($form->categories){
                foreach ($form->categories as $category){
                    foreach ($category as $c){
                        $category = ParticipantCategory::where('owner_id', '=', Admin::user()->id)
                            ->where('event_id', '=', $form->model()->id)
                            ->where('category', '=', $c)->first();
                        if(!$category){
                            $participant_categories = new ParticipantCategory;
                            $participant_categories->owner_id = Admin::user()->id;
                            $participant_categories->event_id = $form->model()->id;
                            $participant_categories->category = $c;
                            $participant_categories->save();
                        }
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
        $grades = ['5' => '5', '5+' => '5+','6A' => '6A','6A+' => '6A+', '6B' => '6B', '6B+' => '6B+','6C' => '6C',
            '6C+' => '6C+','7A' => '7A','7A+' => '7A+','7B' => '7B','7B+' => '7B+','7C' => '7C','7C+' => '7C+','8A' => '8A'];
        return $grades;
    }

    /**
     * @return array[]
     */
    protected function getRoutes(): array
    {
        $routes = [
            ['Категория' => '5', 'Кол-во' => 3, 'Ценность' => 100],
            ['Категория' => '5+', 'Кол-во' => 3, 'Ценность' => 150],
            ['Категория' => '6A', 'Кол-во' => 3, 'Ценность' => 200],
            ['Категория' => '6A+', 'Кол-во' => 3, 'Ценность' => 250],
            ['Категория' => '6B', 'Кол-во' => 3, 'Ценность' => 300],
            ['Категория' => '6B+', 'Кол-во' => 2, 'Ценность' => 350],
            ['Категория' => '6C', 'Кол-во' => 2, 'Ценность' => 400],
            ['Категория' => '6C+', 'Кол-во' => 2, 'Ценность' => 450],
            ['Категория' => '7A', 'Кол-во' => 2, 'Ценность' => 500],
            ['Категория' => '7A+', 'Кол-во' => 2, 'Ценность' => 550],
            ['Категория' => '7B', 'Кол-во' => 2, 'Ценность' => 600],
            ['Категория' => '7B+', 'Кол-во' => 1, 'Ценность' => 650],
            ['Категория' => '7C', 'Кол-во' => 1, 'Ценность' => 700],
            ['Категория' => '7C+', 'Кол-во' => 1, 'Ценность' => 750],
            ['Категория' => '8A', 'Кол-во' => 0, 'Ценность' => 800],
        ];
        return $routes;
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
