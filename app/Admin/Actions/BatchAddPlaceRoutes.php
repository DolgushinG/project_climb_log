<?php

namespace App\Admin\Actions;

use App\Models\Area;
use App\Models\PlaceRoute;
use Encore\Admin\Actions\Action;
use Illuminate\Http\Request;

class BatchAddPlaceRoutes extends Action
{
    public $name = 'Добавить Сектор';

    protected $selector = '.place_routes';

    public function handle(Request $request)
    {
        if($request->input('area')){
            $id = $request->input('area')['id'];
            $name = $request->input('name');
            if(request()->image){
                $imageName = time().'.'.request()->image->getClientOriginalExtension();
                $path = '/images/sector/'.$id.'/'.$imageName;
                request()->image->move(public_path('storage/images/sector/'.$id), $imageName);
            } else {
                $path = null;
            }

            $description = $request->input('description');
            $model = PlaceRoute::where('area_id', $id)->where('name', $name)->first();
            if(!$model){
                $model = new PlaceRoute;
                $model->name = $name;
                $model->image = $path;
                $model->description = $description;
                $model->area_id = $id;
                $model->save();
                return $this->response()->success('Готово')->refresh();
            }
        }
    }

    public function form()
    {
        $this->modalSmall();
        $this->select('area.id', 'Район')->options(Area::all()->pluck('name', 'id'))->required();
        $this->textarea('description', 'Описание')->required();
        $this->image('image', 'Картинка Сектора')->move('images/place_route')->required();
        $this->text('name', 'Сектор')->required();
    }

    public function html()
    {
        return "<a class='place_routes btn btn-sm btn-primary'><i class='fa fa-arrow-up'></i> {$this->name}</a>
                <style>
                .place_routes {margin-top:8px;}
                 @media screen and (max-width: 767px) {
                        .place_routes {margin-top:8px;}
                    }
                </style>
            ";
    }

}
