<?php

namespace App\Admin\Actions;

use App\Models\Area;
use App\Models\Place;
use Encore\Admin\Actions\Action;
use Encore\Admin\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BatchAddMap extends Action
{
    public $name = 'Добавить карту';

    protected $selector = '.maps';

    public function handle(Request $request)
    {
        $user = \Encore\Admin\Facades\Admin::user();
        if($request->image){
            if($request->image){
                $imageName = time().'.'.$request->image->getClientOriginalExtension();
                $path = '/images/maps/'.$user->id.'/'.$imageName;
                $request->image->move(public_path('storage/images/maps/'.$user->id), $imageName);
            } else {
                $path = null;
            }
            $user->map = $path;
            $user->save();
        }
        return $this->response()->success('Готово')->refresh();
    }

    public function form()
    {
        $this->modalSmall();
        $this->image('image', 'Карта')->move('images/maps')->required();
    }

    public function html()
    {
        return "<a class='maps btn btn-sm btn-primary'><i class='fa fa-upload'></i> {$this->name}</a>
                <style>
                .maps {margin-top:8px;}
                 @media screen and (max-width: 767px) {
                        .maps {margin-top:8px;}
                    }
                </style>
            ";
    }

}
