<?php

namespace App\Admin\Actions;

use App\Models\Area;
use App\Models\Country;
use App\Models\Event;
use App\Models\Place;
use Encore\Admin\Actions\Action;
use Illuminate\Http\Request;

class BatchAddArea extends Action
{
    public $name = 'Добавить Район';

    protected $selector = '.area';

    public function handle(Request $request)
    {
        if($request->input('place')){
            $id = $request->input('place')['id'];
            $name = $request->input('name');
            $model = Area::where('place_id', $id)->where('name', $name)->first();
            if(!$model){
                $model = new Area;
                $model->name = $name;
                $model->place_id = $id;
                $model->save();
                return $this->response()->success('Готово')->refresh();
            }
        }
    }

    public function form()
    {
        $this->modalSmall();
        $this->select('place_id', 'Место')->options(Place::all()->pluck('name', 'id'));
        $this->text('name', 'Район');
    }

    public function html()
    {
        return "<a class='area btn btn-sm btn-primary'><i class='fa fa-arrow-up'></i> {$this->name}</a>
                <style>
                .area {margin-top:8px;}
                 @media screen and (max-width: 767px) {
                        .area {margin-top:8px;}
                    }
                </style>
            ";
    }

}
