<?php

namespace App\Admin\Controllers;

use App\Models\Event;
use App\Models\Participant;
use App\Models\ResultRouteFinalStage;
use App\Http\Controllers\Controller;
use App\Models\User;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Show;
use Illuminate\Database\Eloquent\Collection;
use Jxlwqq\DataTable\DataTable;

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
            ->header(trans('admin.index'))
            ->description(trans('admin.description'))
            ->row(function(Row $row) {
                $row->column(10, $this->grid2());
                $row->column(10, $this->grid());
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

        $grid = new Grid(new ResultRouteFinalStage);
        if (!Admin::user()->isAdministrator()){
            $grid->model()->where('owner_id', '=', Admin::user()->id);
        }

        $grid->column('event_id','Соревнование')->select(Event::all()->pluck('title','id')->toArray());
        $grid->column('final_route_id', __('Номер маршрута'))->editable();
        $grid->column('user_id', __('Участник'))->select($this->getUsers()->toArray());
        $grid->column('amount_try_top', __('Кол-во попыток на топ'))->editable();
        $grid->column('amount_try_zone', __('Кол-во попыток на зону'))->editable();
        $grid->disableExport();
        $grid->disableColumnSelector();
        $grid->disableCreateButton();

        $grid->quickCreate(function (Grid\Tools\QuickCreate $create)  {
            $events = Event::where('active', '=', 1)->pluck('title','id');
            $create->select('event_id','Соревнование')->options($events);
            $create->integer('owner_id', Admin::user()->id)->default(Admin::user()->id)->style('display', 'None');
            $create->integer('final_route_id', 'Номер маршрута');
            $create->select('user_id', 'Участники')->options($this->getUsers()->toArray());
            $create->integer('amount_try_top', 'Кол-во попыток на топ');
            $create->integer('amount_try_zone', 'Кол-во попыток на зону');
        });
//        dd($grid);
        return $grid;
    }


    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid2()
    {
        $grid = new Grid(new Event);
        if (!Admin::user()->isAdministrator()){
            $grid->model()->where('owner_id', '=', Admin::user()->id);
        }
        $grid->disableExport();
        $grid->disableColumnSelector();
        $grid->disableCreateButton();
        $grid->disableActions();
        $grid->disablePagination();
        $grid->disablePerPageSelector();
        $grid->disableBatchActions();
        $grid->disableFilter();
        $grid->column('title', 'Соревнование')->expand(function ($model) {
            $headers = ['Участник', 'Пол', 'Место с учетом квалы', 'Кол-во топ','Кол-во попыток на топ','Кол-во зон', 'Кол-во попыток на зону', ];
            $style = ['table-bordered','table-hover', 'table-striped'];
            $users_id = $model->participant()->where('owner_id', '=', Admin::user()->id)->pluck('user_id')->toArray();
            $fields = ['firstname','id','category','active','team','city', 'email','year','lastname','skill','sport_category','email_verified_at', 'created_at', 'updated_at'];
            $users_male = User::whereIn('id', $users_id)->where('gender', '=', 'male')->get();
            $users_female = User::whereIn('id', $users_id)->where('gender', '=', 'female')->get();
            $male = self::getUsersSorted($users_male, $fields, $model);
            $female = self::getUsersSorted($users_female, $fields, $model);
            $all_users = array_merge($male, $female);
            $options = [
                'responsive' => true,
                'paging' => true,
                'lengthChange' => true,
                'searching' => true,
                'ordering' => false,
                'info' => true,
                'autoWidth' => true,
                'deferRender' => true,
                'processing' => true,
            ];
            return new DataTable($headers, $all_users, $style, $options);
        });
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
        $show = new Show(ResultRouteFinalStage::findOrFail($id));

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
    protected function form()
    {
        $form = new Form(new ResultRouteFinalStage);

        $form->display('ID');
        $form->hidden('owner_id')->value(Admin::user()->id);
        $form->text('event_id', 'event_id');
        $form->text('user_id', 'user_id');
        $form->text('final_route_id', 'final_route_id');
        $form->text('amount_try_top', 'amount_try_top');
        $form->text('amount_try_zone', 'amount_try_zone');
        $form->display(trans('admin.created_at'));
        $form->display(trans('admin.updated_at'));

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
        return $form;
    }

    /**
     * @return object
     */
    protected function getUsers(): object
    {
        $participant = Participant::where('owner_id', '=', Admin::user()->id)
            ->where('active', '=', 1)
            ->pluck('user_id')->toArray();
        return User::whereIn('id', $participant)->pluck('middlename', 'id');
    }

    /**
     * @param $users
     * @param $fields
     * @param $model
     * @return array
     */
    public static function getUsersSorted($users, $fields, $model): array
    {
        if (count($users->toArray()) == 0){
            return [];
        }
        foreach ($users as $index => $user){
            $result_user = ResultRouteFinalStage::where('owner_id', '=', Admin::user()->id)
                ->where('event_id', '=', $model->id)
                ->where('user_id', '=', $user->id)
                ->get();
            $result = ResultRouteFinalStage::merge_result_user_in_final_stage($result_user);
            $users[$index] = collect($user->toArray())->except($fields);
            $users[$index]['result'] = $result;
            $users[$index]['place'] = null;
            $users[$index]['gender'] = trans_choice('somewords.'.$user->gender, 10);
            $users[$index]['amount_top'] = $result['amount_top'];
            $users[$index]['amount_try_top'] = $result['amount_try_top'];
            $users[$index]['amount_zone'] = $result['amount_zone'];
            $users[$index]['amount_try_zone'] = $result['amount_try_zone'];
        }
        $users_sorted = Participant::counting_final_place($model->id, $users->toArray());
        foreach ($users_sorted as $index => $user){
            $fields = ['result'];
            $users_sorted[$index] = collect($user)->except($fields)->toArray();
            $users_sorted[$index]['place'] = $index+1;
        }
        usort($users_sorted, function($a, $b) {
            return $a['place'] <=> $b['place'];
        });
        return $users_sorted;
    }



}
