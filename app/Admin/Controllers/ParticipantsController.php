<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\BatchForceRecouting;
use App\Admin\Actions\ResultQualification\BatchResultQualification;
use App\Admin\Actions\ResultRouteFinalStage\BatchResultFinal;
use App\Admin\CustomAction\ActionExport;
use App\Admin\Extensions\Popover;
use App\Admin\Extensions\Tools\UserGender;
use App\Exceptions\ExportToCsv;
use App\Exceptions\ExportToExcel;
use App\Exceptions\ExportToOds;
use App\Exports\SemiFinalResultExport;
use App\Exports\QualificationResultExport;
use App\Models\Event;
use App\Models\Participant;
use App\Http\Controllers\Controller;
use App\Models\ParticipantCategory;
use App\Models\ResultSemiFinalStage;
use App\Models\Set;
use App\Models\User;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Grid\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Show;
use Encore\Admin\Widgets\Box;
use Illuminate\Http\Request;
use Jxlwqq\DataTable\DataTable;
use Maatwebsite\Excel\Facades\Excel;

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
            ->row(function(Row $row) {
                $event = Event::where('owner_id', '=', Admin::user()->id)->where('active', '=', 1)->first();
                if($event) {
//                    $row->column(10, $this->grid4());
                    $row->column(20, $this->grid2());
                } else {
                    $row->column(10, $this->grid());
                    $row->column(10, $this->grid3());
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
        }

        $grid->actions(function ($actions) {
            $actions->disableDelete();
            $actions->disableEdit();
            $actions->disableView();
            $actions->append(new ActionExport($actions->getKey(), 'qualification', 'excel'));
            $actions->append(new ActionExport($actions->getKey(), 'qualification', 'csv'));
            $actions->append(new ActionExport($actions->getKey(), 'qualification', 'ods'));
        });
        $grid->disableExport();
        $grid->disableCreateButton();
        $grid->disableColumnSelector();
        $grid->disablePagination();
        $grid->disablePerPageSelector();
        $grid->disableBatchActions();
        $grid->disableFilter();
        $grid->column('title', 'Название')->expand(function ($model) {
            $headers = ['Участник','Пол','Город','Команда','Категория','Место','Сет','Баллы', 'Результаты'];

            $style = ['table-bordered','table-hover', 'table-striped'];

            $options = [
                'responsive' => true,
                'paging' => true,
                'lengthChange' => true,
                'searching' => true,
                'ordering' => true,
                'info' => true,
                'autoWidth' => true,
                'deferRender' => true,
                'processing' => true,
            ];
            $users_id = $model->participant()->where('owner_id', '=', Admin::user()->id)->pluck('user_id')->toArray();
            $users_point = $model->participant()->pluck('points','user_id')->toArray();
            $users_active = $model->participant()->pluck('active','user_id')->toArray();
            $users_number_set = $model->participant()->pluck('number_set','user_id')->toArray();
            $fields = ['firstname','id', 'email','year','lastname','skill','sport_category','email_verified_at', 'created_at', 'updated_at'];
            $users = User::whereIn('id', $users_id)->get();
            foreach ($users as $index => $user){
                $participant_update = Participant::where('event_id', '=', $model->id)->where('user_id', '=', $user->id)->first();
                $users[$index] = collect($user->toArray())->except($fields);
                $users[$index]['middlename'] = $user->middlename;
                $place = Participant::get_places_participant_in_qualification($model->id, $user->id, $user->gender, $participant_update->category_id,true);
                $users[$index]['place'] = $place;
                $users[$index]['number_set'] = 'Сет '.$users_number_set[$user->id];
                $users[$index]['gender'] = trans_choice('somewords.'.$user->gender, 10);
                $users[$index]['city'] = $user->city;
                $users[$index]['team'] = $user->team;
                $users[$index]['category'] = User::category($participant_update->category_id);
                $users[$index]['points'] = $users_point[$user->id];
                $users[$index]['active'] = $users_active[$user->id];

                $participant_update->user_place = $place;
                $participant_update->save();

                if (isset($users_active[$user->id])){
                    if ($users_active[$user->id]){
                            $status = '<i class="fa fa-circle text-success">';
                        } else {
                            $status = '<i class="fa fa-times-circle text-danger">';
                        }
                    $users[$index]['active'] = $status;
                }
//
            }
            return new DataTable($headers, $users->toArray(), $style, $options);
        });
        $grid->column('active', 'Статус')->using([0 => 'Не активно', 1 => 'Активно'])->display(function ($title, $column) {
            If ($this->active == 0) {
                return $column->label('default');
            } else {
                return $column->label('success');
            }
        });
        $grid->header(function ($query) {
            $event_id = $query->where('owner_id', '=', Admin::user()->id)->get()->pluck('id');
            $users_id = Participant::whereIn('event_id',$event_id)->get()->pluck('user_id');
            $users_female = User::whereIn('id', $users_id)->where('gender', '=', 'female')->get()->count();
            $users_male = User::whereIn('id', $users_id)->where('gender', '=', 'male')->get()->count();
            $gender = array('female' => $users_female, 'male' => $users_male);
            $doughnut = view('admin.charts.gender', compact('gender'));

            return new Box('Соотношение мужчин и женщин', $doughnut);
        });

        $grid->filter(function($filter){
            $filter->disableIdFilter();
            $filter->in('active', 'Внесли результат')->checkbox([
                1    => 'Те кто внес',
                0    => 'Не внесли',
            ]);
        });

        return $grid;
    }


    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid2()
    {
        $grid = new Grid(new Participant);
        if (!Admin::user()->isAdministrator()){
            $grid->model()->where('owner_id', '=', Admin::user()->id);
        }
        $grid->model()->where(function ($query) {
            $query->has('event.participant');
        });
        $grid->disableExport();
        $grid->disableCreateButton();
        $grid->disableColumnSelector();
        $grid->tools(function (Grid\Tools $tools) {
            $tools->append(new BatchResultQualification);
            $tools->append(new BatchForceRecouting);
        });
        $grid->actions(function ($actions) {
            $actions->disableEdit();
            $actions->disableView();
            $actions->disableDelete();
        });
        $grid->column('user.middlename', __('Участник'));
        $grid->column('user.gender', __('Пол'))->display(function ($gender) {
            return trans_choice('somewords.'.$gender, 10);
        })->editable();
        $grid->column('category_id', 'Категория')
            ->help('Если случается перенос, из одной категории в другую, необходимо обязательно пересчитать результаты')
            ->select($this->getUserCategory()->toArray());
        $grid->column('number_set', 'Номер сета')->editable();
        $grid->column('user_place', 'Место в квалификации')->sortable();
        $grid->column('points', 'Баллы')->sortable();
        $grid->column('active', 'Статус')->using([0 => 'Не внес', 1 => 'Внес'])->display(function ($title, $column) {
            If ($this->active == 0) {
                return $column->label('default');
            } else {
                return $column->label('success');
            }
        });
        $grid->filter(function($filter){
            $filter->disableIdFilter();
            $filter->in('user.gender', 'Пол')->checkbox([
                'male'    => 'Мужчина',
                'female'    => 'Женщина',
            ]);
//            $filter->in('number_set', 'Номер сета')->checkbox(Set::where('owner_id', '=', Admin::user()->id)->pluck('number_set'));
            $filter->in('category_id', 'Категория')->checkbox($this->getUserCategory()->toArray());

        });
        return $grid;
    }
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid3()
    {
        $grid = new Grid(new Event);
        $grid->disableExport();
        $grid->disableColumnSelector();
        $grid->disableCreateButton();
        $grid->disablePagination();
        $grid->disablePerPageSelector();
        $grid->disableBatchActions();
        $grid->disableFilter();
        $grid->disableActions();
        $grid->setTitle('Нет активных соревнований');
        return $grid;
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid4()
    {
        $grid = new Grid(new Event);
        if (!Admin::user()->isAdministrator()){
            $grid->model()->where('owner_id', '=', Admin::user()->id);
        }
        $grid->actions(function ($actions) {
            $actions->disableDelete();
            $actions->disableEdit();
            $actions->disableView();
            $actions->append(new ActionExport($actions->getKey(), 'qualification', 'excel'));
            $actions->append(new ActionExport($actions->getKey(), 'qualification', 'csv'));
            $actions->append(new ActionExport($actions->getKey(), 'qualification', 'ods'));
        });
        $grid->disableExport();
        $grid->disableCreateButton();
        $grid->disableColumnSelector();
        $grid->disablePagination();
        $grid->disablePerPageSelector();
        $grid->disableBatchActions();
        $grid->disableFilter();
        $grid->column('title', 'Название');
        $grid->column('active', 'Статус')->using([0 => 'Не активно', 1 => 'Активно'])->display(function ($title, $column) {
            If ($this->active == 0) {
                return $column->label('default');
            } else {
                return $column->label('success');
            }
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
        $show = new Show(Participant::findOrFail($id));

        $show->id('ID');
        $show->event_id('event_id');
        $show->number_set('number_set');
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
        $form->text('number_set', 'number_set');
        $form->text('category_id', 'number_set');
        $form->switch('active', 'active');
        $form->display(trans('admin.created_at'));
        $form->display(trans('admin.updated_at'));
        $form->saving(function (Form $form) {
//            dd($form->category);
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
        $participant = Participant::where('owner_id', '=', Admin::user()->id)
            ->where('event_id', '=', $event_id)
            ->where('active', '=', 1)
            ->pluck('user_id')->toArray();
        return User::whereIn('id', $participant)->pluck('middlename', 'id');
    }
    protected function getUserCategory()
    {
        $participant = Participant::where('owner_id', '=', Admin::user()->id)
            ->where('active', '=', 1)
            ->pluck('category_id')->toArray();
        return ParticipantCategory::whereIn('id', $participant)->pluck('category', 'id');
    }

}
