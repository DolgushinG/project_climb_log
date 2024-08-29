<?php

namespace App\Admin\Controllers;

use App\Models\Event;
use App\Models\Map;
use App\Http\Controllers\Controller;
use App\Models\Route;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class MapController extends Controller
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
     * Store a newly created resource in storage.
     *
     * @return mixed
     */
    public function store()
    {
        $data = request()->only(['event_id', 'owner_id' , 'author', 'grade', 'color', 'x', 'y']);
        $point = Map::create($data);

        return response()->json([
            'success' => true,
            'point' => $point,
        ]);
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
        $points = Map::all();
        $event = Event::where('owner_id', '=', 2)->where('active', 1)->first();
        $routes = Route::where('event_id', $event->id)->get();
        return Admin::component('admin::map', compact(['points', 'event', 'routes']));
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Map);

        $form->hidden('event_id', __('Author'))->rules('required');
        $form->hidden('owner_id', __('Author'))->rules('required');
        $form->text('author', __('Author'))->rules('required');
        $form->text('grade', __('Category'))->rules('required');
        $form->text('route_id', __('Route'))->rules('required');
        $form->color('color', __('Color'))->rules('required');
        $form->hidden('x'); // Скрываем, так как x и y будут установлены через JavaScript
        $form->hidden('y');

        return $form;
    }
}
