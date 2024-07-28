<?php

namespace App\Admin\Actions;

use App\Helpers\AllClimbService\Service;
use App\Models\Area;
use App\Models\Country;
use App\Models\Event;
use App\Models\ResultQualificationClassic;
use App\Models\RoutesOutdoor;
use App\Models\User;
use Encore\Admin\Actions\Action;
use Encore\Admin\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BatchCreateOutdoorRoutes extends Action
{
    public $name = 'Добавить скальные трассы';

    protected $selector = '.notify';

    public function handle(Request $request)
    {
//        "place" => "13"
//      "area" => "32"
//      "place_routes" => "206"
//      "rock" => array:8 [
//        0 => "2138"
//        1 => "2139"
//        2 => "2140"
//        3 => "2141"
//        4 => "2142"
//        5 => "2143"
//        6 => "2144"
//        7 => "2145"
//      ]
            dd($request);
            $model_route_outdoors = new RoutesOutdoor;
//        $guides = Service::ge('Россия');
//        foreach ($guides as $guide){
//
//        }
        return $this->response()->success('Отправлено')->refresh();
//        if($request->message && $request->subject){
//            if(count($users) > 0){
//                foreach ($users as $user){
//                    ResultQualificationClassic::send_message_from_climbing_gym($request->subject, $request->message, $user, $event->climbing_gym_name);
//                }
//                return $this->response()->success('Отправлено')->refresh();
//            }
//
//        } else {
//            Log::error('Не найдено сообщение - $request->'.$request->message);
//            return $this->response()->error('Ошибка отправки')->refresh();
//        }



    }

    public function form()
    {
        $this->modalLarge();
        $guides = Country::all()->pluck('name', 'id');
        $this->select('place', 'Страна')->attribute('id', 'place-outdoor')->options($guides);
        $this->select('area', 'Место')->attribute('id', 'area-outdoor');
        $this->select('place_routes', 'Район')->attribute('id', 'local-outdoor');
        $this->multipleSelect('rock', 'Камни(Cкалы)')->attribute('id', 'rock-outdoor');
        $script = <<<EOT
        $(document).on("change", '[id=place-outdoor]', function () {
                    $.get("api/get_places",
                            {option: $(this).val()},
                            function (data) {
                                var model = $('[id=area-outdoor]');
                                model.empty();
                                model.append("<option>Select a state</option>");
                                $.each(data, function (index, element) {
                                    model.append("<option value='" + element.id + "'>" + element.text + "</option>");
                                });
                            }
                    );
                });

        $(document).on("change", '[id=area-outdoor]', function () {
                    $.get("api/get_place_routes",
                            {option: $(this).val()},
                            function (data) {
                                var model = $('[id=local-outdoor]');
                                model.empty();
                                model.append("<option>Select a state</option>");
                                $.each(data, function (index, element) {
                                    model.append("<option value='" + element.id + "'>" + element.text + "</option>");
                                });
                            }
                    );
                });
        $(document).on("change", '[id=local-outdoor]', function () {
                    $.get("api/get_rocks",
                            {option: $(this).val()},
                            function (data) {
                                var model = $('[id=rock-outdoor]');
                                model.empty();
                                model.append("<option>Select a state</option>");
                                $.each(data, function (index, element) {
                                    model.append("<option value='" + element.id + "'>" + element.text + "</option>");
                                });
                            }
                    );
                });

        EOT;
//        \Encore\Admin\Facades\Admin::script($script);
    }
    public function html()
    {
        return "<a class='notify btn btn-sm btn-success'><i class='fa fa-send'></i> $this->name</a>
                    <style>
                .notify {margin-top:8px;}
                 @media screen and (max-width: 767px) {
                        .notify {margin-top:8px;}
                    }
                </style>
                ";
    }

}
