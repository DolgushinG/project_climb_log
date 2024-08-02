<?php

namespace App\Admin\Actions;

use App\Helpers\AllClimbService\Service;
use App\Jobs\UpdateAreas;
use App\Jobs\UpdateContries;
use App\Jobs\UpdatePlaceRoutes;
use App\Jobs\UpdatePlaces;
use App\Jobs\UpdateRouteCoefficientParticipants;
use App\Models\Area;
use App\Models\Country;
use App\Models\Event;
use App\Models\Place;
use App\Models\Place_route;
use App\Models\PlaceRoute;
use App\Models\ResultQualificationClassic;
use App\Models\User;
use Encore\Admin\Actions\Action;
use Encore\Admin\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BatchUpdateOutdoorRoutes extends Action
{
    public $name = 'Обновить данные';

    protected $selector = '.update-date-outdoor';

    public function handle()
    {
        UpdateContries::dispatch();
        UpdatePlaces::dispatch();
        UpdateAreas::dispatch();
        UpdatePlaceRoutes::dispatch();
        return $this->response()->success('Поставлено в очередь и будет обновлено в течение 10-15 мин')->refresh();
    }
    public function dialog()
    {
        $this->confirm('Подтвердить обновление');
    }
    public function html()
    {
        return "<a class='update-date-outdoor btn btn-sm btn-success'><i class='fa fa-refresh'></i> $this->name</a>
                    <style>
                .update-date-outdoor {margin-top:8px;}
                 @media screen and (max-width: 767px) {
                        .update-date-outdoor {margin-top:8px;}
                    }
                </style>
                ";
    }

}
