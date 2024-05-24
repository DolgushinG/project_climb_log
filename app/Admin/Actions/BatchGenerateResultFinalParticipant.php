<?php

namespace App\Admin\Actions;

use App\Helpers\Generators\Generators;
use App\Models\Event;
use Encore\Admin\Actions\Action;
use Illuminate\Http\Request;

class BatchGenerateResultFinalParticipant extends Action
{
    public $name = 'Сгенерировать результаты финала участников[beta]';

    protected $selector = '.generate-result-final-participant';

    public function handle(Request $request)
    {
        $owner_id = \Encore\Admin\Facades\Admin::user()->id;
        $event = Event::where('owner_id', '=', \Encore\Admin\Facades\Admin::user()->id)->where('active', 1)->first();
        Generators::prepare_result_route_qualification_classic($owner_id, $event->id,'result_route_final_stage');

        return $this->response()->success('Готово')->refresh();
    }

    public function dialog()
    {
        $this->confirm('Подтвердить генерацию результатов финала по результатам квалификации или полуфинала');
    }

    public function html()
    {
        return "<a class='generate-result-final-participant btn btn-sm btn-warning'><i class='fa fa-trophy'></i> Сгенерировать результаты[beta]</a>
            <style>
                 .generate-result-final-participant {margin-top:8px;}
                 @media screen and (max-width: 767px) {
                    .generate-result-final-participant {margin-top:8px;}
                    }
            </style>
            ";
    }

}
