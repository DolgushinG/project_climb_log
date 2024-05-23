<?php

namespace App\Admin\Actions;

use App\Helpers\Generators\Generators;
use App\Models\Event;
use Encore\Admin\Actions\Action;
use Illuminate\Http\Request;

class BatchGenerateResultSemiFinalParticipant extends Action
{
    public $name = 'Сгенерировать результаты полуфинала участников[beta]';

    protected $selector = '.generate-result-semifinal-participant';

    public function handle(Request $request)
    {
        $owner_id = \Encore\Admin\Facades\Admin::user()->id;
        $event = Event::where('owner_id', '=', \Encore\Admin\Facades\Admin::user()->id)->where('active', 1)->first();
        Generators::prepare_result_route_qualification_classic($owner_id, $event->id,'result_route_semifinal_stage');
        return $this->response()->success('Готово')->refresh();
    }

    public function dialog()
    {
        $this->confirm('Подтвердить генерацию результатов полуфинала по результатам квалификации');
    }

    public function html()
    {
        $event = Event::where('owner_id', '=', \Encore\Admin\Facades\Admin::user()->id)->where('active', 1)->first();
        return "<a class='generate-result-semifinal-participant btn btn-sm btn-warning'><i class='fa fa-trophy'></i> Сгенерировать результаты[beta]</a>
            <style>
                 .generate-result-semifinal-participant {margin-top:8px;}
                 @media screen and (max-width: 767px) {
                    .generate-result-semifinal-participant {margin-top:8px;}
                    }
            </style>
        ";
    }

}
