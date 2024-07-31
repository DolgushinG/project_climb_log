<?php

namespace App\Admin\Actions;

use App\Models\Area;
use App\Models\Country;
use App\Models\Event;
use App\Models\Place;
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
            $imageName = time().'.'.request()->image->getClientOriginalExtension();
            $path = '/images/areas/'.$id.'/'.$imageName;
            request()->image->move(public_path('storage/images/areas/'.$id), $imageName);
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
        $this->select('area.id', 'Район')->options(Area::all()->pluck('name', 'id'));
        $this->textarea('description', 'Описание');
        $this->image('image', 'Картинка Сектора')->move('images/place_route');
        $this->text('name', 'Сектор');
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
