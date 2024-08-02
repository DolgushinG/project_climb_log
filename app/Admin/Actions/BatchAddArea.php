<?php

namespace App\Admin\Actions;

use App\Models\Area;
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
            if(request()->image){
                $imageName = time().'.'.request()->image->getClientOriginalExtension();
                $path = '/images/areas/'.$id.'/'.$imageName;
                request()->image->move(public_path('storage/images/areas/'.$id), $imageName);
            } else {
                $path = null;
            }

            $name = $request->input('name');
            $model = Area::where('place_id', $id)->where('name', $name)->first();
            if(!$model){
                $model = new Area;
                $model->name = $name;
                $model->place_id = $id;
                $model->image = $path;
                $model->save();
                return $this->response()->success('Готово')->refresh();
            }
        }
    }

    public function form()
    {
        $this->modalSmall();
        $this->select('place.id', 'Место')->options(Place::all()->pluck('name', 'id'))->required();
        $this->image('image', 'Картинка района')->move('images/area')->required();
        $this->text('name', 'Район')->required();
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
