<?php

namespace App\Admin\Actions;

use App\Admin\CustomAction\ActionExport;
use App\Exports\QualificationResultExport;
use App\Helpers\Generators\Generators;
use App\Models\Event;
use App\Models\Participant;
use App\Models\ParticipantCategory;
use App\Models\ResultSemiFinalStage;
use Encore\Admin\Actions\Action;
use Encore\Admin\Actions\BatchAction;
use Encore\Admin\Facades\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class BatchGenerateResultFinalParticipant extends Action
{
    public $name = 'Сгенерировать результаты финала участников[beta]';

    protected $selector = '.generate-result-final-participant';

    public function handle(Request $request)
    {
        $owner_id = \Encore\Admin\Facades\Admin::user()->id;
        $event = Event::where('owner_id', '=', \Encore\Admin\Facades\Admin::user()->id)->where('active', 1)->first();
        Generators::prepare_result_participant($owner_id, $event->id,'result_route_final_stage');

        return $this->response()->success('Готово')->refresh();
    }

    public function dialog()
    {
        $this->confirm('Подтвердить генерацию результатов полуфинала по результатам квалификации');
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
