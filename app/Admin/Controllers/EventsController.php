<?php

namespace App\Admin\Controllers;

use App\Models\Event;
use App\Http\Controllers\Controller;
use App\Models\Grades;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

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
            ->header(trans('admin.index'))
            ->description(trans('admin.description'))
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
        if (!Admin::user()->isAdministrator()){
            $grid->model()->where('owner_id', '=', Admin::user()->id);
        } else {
            $grid->column('owner_id', 'Owner')->editable();
        }
        $event = new Event;
        $grid->column('start_date', 'Дата старта')->editable('date');
        $grid->column('end_date', 'Дата окончания')->editable('date');
        $grid->column('start_time', 'Время старта')->editable('time');
        $grid->column('end_time', 'Время окончания')->editable('time');
        $grid->column('address', 'Адрес')->editable();
        $grid->column('document', 'Документ');
        $grid->column('image', 'Афиша')->image($event->image,100, 100);
        $grid->column('climbing_gym_name', 'Название скалодрома');
        $grid->column('city', 'Город')->editable();
        $grid->column('count_routes', 'Кол-во маршрутов')->editable();
        $grid->column('title', 'Название')->editable();
        $grid->column('subtitle', 'Надпись под названием')->editable();
        $grid->column('link', 'Ссылка')->editable();
        $grid->column('mode')->editable('select', [1 => '10 лучших трасс', 2 => 'Все трассы']);
        $grid->column('active', 'Опубликовать')->switch();

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

        $show = new Show(Event::findOrFail($id));
        $show->id('ID');
        $show->date('date');
        $show->datetime('datetime');
        $show->time('time');
        $show->location('location');
        $show->document('document');
        $show->image('image');
        $show->climbing_gym_name('climbing_gym_name');
        $show->city('city');
        $show->count_routes('count_routes');
        $show->title('title');
        $show->subtitle('subtitle');
        $show->link('link');
        $show->sponsor_image_1('sponsor_image_1');
        $show->sponsor_image_2('sponsor_image_2');
        $show->sponsor_image_3('sponsor_image_3');
        $show->sponsor_link_1('sponsor_link_1');
        $show->sponsor_link_2('sponsor_link_2');
        $show->sponsor_link_3('sponsor_link_3');
        $show->description('description');
        $show->mode('mode');
        $show->active('active');
        $show->created_at(trans('admin.created_at'));
        $show->updated_at(trans('admin.updated_at'));
        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Event);

        $form->display('id')->style('display', 'None');
        $form->hidden('owner_id')->value(Admin::user()->id);
        $form->date('start_date', 'Дата старта')->placeholder('Дата старта')->required();
        $form->date('end_date', 'Дата окончания')->placeholder('Дата окончания')->required();
        $form->time('start_time', 'Время старта')->placeholder('Время старта')->required();
        $form->time('end_time', 'Время окончания')->placeholder('Время окончания')->required();
        $form->text('address', 'Адрес')->placeholder('Адрес')->required();
        $form->file('document', 'Прикрепить документ')->placeholder('Прикрепить документ');
        $form->image('image', 'Афиша')->placeholder('Афиша')->required();
        $form->text('climbing_gym_name', 'Название скалодрома')->placeholder('Название скалодрома')->required();
        $form->hidden('climbing_gym_name_eng')->default('1');
        $form->text('city', 'Город')->placeholder('Город')->required();
        $form->number('count_routes', 'Кол-во трасс')->placeholder('Кол-во трасс')->required();
        $form->text('title', 'Название')->placeholder('Введи название')->required();
        $form->hidden('title_eng')->default('1');;
        $form->text('subtitle', 'Надпись под названием')->placeholder('Введи название')->required();
        $form->url('link', 'Ссылка')->placeholder('Ссылка')->default('http://127.0.0.1:8000/')->required();
//        $form->textarea('description', 'description');
        $form->summernote('description', 'Описание')->placeholder('Описание')->required();
        $form->select('mode', 'Формат')->options([ 1 => '10 лучших трасс', 2 => 'Все трассы'])->required();
        $form->switch('active', 'Опубликовать сразу?');
//        $form->display(trans('admin.created_at'));
//        $form->display(trans('admin.updated_at'));
        $form->saving(function (Form $form) {
            $form->climbing_gym_name_eng = str_replace(' ', '-', (new \App\Models\Event)->translate_to_eng($form->climbing_gym_name));
            $form->title_eng = str_replace(' ', '-', (new \App\Models\Event)->translate_to_eng($form->title));
            if (Grades::all()->count() == 0){
                Event::generation_route(Admin::user()->id, $form->count_routes);
            }
        });
        return $form;
    }
}
