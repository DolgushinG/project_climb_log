<?php

namespace App\Admin\Actions;

use App\Models\Event;
use App\Models\Point;
use App\Models\ResultFinalStage;
use App\Models\ResultFranceSystemQualification;
use Encore\Admin\Actions\Action;
use Illuminate\Http\Request;

class BatchRemoveMap extends Action
{
    public $name = 'Удалить карту';

    protected $selector = '.remove-map';

    public function handle(Request $request)
    {
        $user = \Encore\Admin\Facades\Admin::user();
        $user->map = null;
        $user->save();
        return $this->response()->success('Удалено')->refresh();
    }
    public function dialog()
    {
        $this->confirm('Подтвердить удаление');
    }
    public function html()
    {
        return "<a class='remove-map btn btn-sm btn-success'><i class='fa fa-remove'></i> Удалить карту</a>
         <style>
              .remove-map {margin-top:8px;}
                        }
                @media screen and (max-width: 767px) {
                    .remove-map {margin-top:8px;}
                    }
            </style>
        ";
    }

}
