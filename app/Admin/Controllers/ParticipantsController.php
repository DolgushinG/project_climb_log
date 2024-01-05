<?php

namespace App\Admin\Controllers;

use App\Models\Event;
use App\Models\Participant;
use App\Http\Controllers\Controller;
use App\Models\User;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Show;

class  ParticipantsController extends Controller
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
        $grid = new Grid(new Participant);
        if (!Admin::user()->isAdministrator()){
            $grid->model()->where('owner_id', '=', Admin::user()->id);
        }
//        $grid->column('progress', 'Сет 1')->default(count(Participant::where('set', '=', 1)->get()->toArray()))->progressBar();
//        $grid->column('progress', 'Сет 2')->default(count(Participant::where('set', '=', 2)->get()->toArray()))->progressBar();
        $grid->filter(function($filter){

            // Remove the default id filter
            $filter->disableIdFilter();

            // Add a column filter
            $filter->in('user.gender', 'Пол')->checkbox([
                'male'    => 'Мужчина',
                'female'    => 'Женщина',
            ]);

            // Add a column filter
            $filter->in('set', 'Номер сета')->checkbox([
                1    => 'Первый сет',
                2    => 'Второй сет',
            ]);

            // Add a column filter
            $filter->in('active', 'Внесли результат')->checkbox([
                1    => 'Те кто внес',
                0    => 'Не внесли',
            ]);

        });

//        $grid->column('user.gender', 'Фильтр по полу')->filter([
//            'male' => 'Мужчины',
//            'female' => 'Женщины',
//        ]);
//        $participant = Participant::where('owner_id', '=', Admin::user()->id)->pluck('user_id')->toArray();
//        $users = User::whereIn('id', $participant)->pluck('middlename', 'id');
//        $grid->id('ID');
//        $grid->event()->title();
        $grid->disableCreateButton();
        $grid->disableActions();
        $grid->column('set', 'Номер сета');
        $grid->column('user.year', 'Год рождения');
        $grid->column('user.firstname', 'Имя');
        $grid->column('user.lastname', 'Фамилия');
        $grid->column('user.age', 'Возраст');
        $grid->column('user.gender', 'Пол');
        $grid->column('user.city', 'Город');
        $grid->column('user.team', 'Команда');
        $grid->column('user.skill', 'Квалификация');
        $grid->column('user.sports_category', 'Спортивная категория');
        $grid->column('active', 'Внес результаты')->bool();
//        $grid->created_at(trans('admin.created_at'));
//        $grid->updated_at(trans('admin.updated_at'));

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
        $show = new Show(Participant::findOrFail($id));

        $show->id('ID');
        $show->event_id('event_id');
        $show->set('set');
        $show->firstname('firstname');
        $show->lastname('lastname');
        $show->year('year');
        $show->city('city');
        $show->team('team');
        $show->skill('skill');
        $show->sports_category('sports_category');
        $show->age('age');
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
        $form = new Form(new Participant);

        $form->display('ID');
        $form->hidden('owner_id')->value(Admin::user()->id);
        $form->text('event_id');
        $form->text('set', 'set');
        $form->text('firstname', 'firstname');
        $form->text('lastname', 'lastname');
        $form->text('gender', 'gender');
        $form->text('year', 'year');
        $form->text('city', 'city');
        $form->text('team', 'team');
        $form->text('skill', 'skill');
        $form->text('sports_category', 'sports_category');
        $form->text('age', 'age');
        $form->switch('active', 'active');
        $form->display(trans('admin.created_at'));
        $form->display(trans('admin.updated_at'));

        return $form;
    }
}
