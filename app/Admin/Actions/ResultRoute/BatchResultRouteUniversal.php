<?php

namespace App\Admin\Actions\ResultRoute;

use App\Admin\Extensions\CustomAction;
use App\Helpers\Helpers;
use App\Models\Event;
use App\Models\Grades;
use App\Models\ResultQualificationClassic;
use App\Models\ParticipantCategory;
use App\Models\ResultFinalStage;
use App\Models\ResultFranceSystemQualification;
use App\Models\ResultRouteFinalStage;
use App\Models\ResultRouteFranceSystemQualification;
use App\Models\ResultRouteSemiFinalStage;
use App\Models\ResultSemiFinalStage;
use App\Models\User;
use Encore\Admin\Admin;
use Illuminate\Http\Request;

class BatchResultRouteUniversal extends CustomAction
{
    private string $stage;

    public function __construct($stage = '')
    {
        $this->initInteractor();
        $this->stage = $stage;
    }
    protected $selector = '.result-universal';

    /**
     * @var mixed|string
     */

    const URLS_SET_ATTEMPTS = [
      'final' => '/admin/api/final/set_attempts',
      'semifinal' => '/admin/api/semifinal/set_attempts',
      'qualification' => '/admin/api/set_attempts'
    ];
    const MODELS_ROUTE = [
        'final' => ResultRouteFinalStage::class,
        'semifinal' => ResultRouteSemiFinalStage::class,
        'qualification' => ResultRouteFranceSystemQualification::class,
    ];
    const URLS_GET_ATTEMPTS = [
        'final' => '/admin/api/final/get_attempts',
        'semifinal' => '/admin/api/semifinal/get_attempts',
        'qualification' => '/admin/api/get_attempts'
    ];
    const ROUTE = [
        'final' => 'final_route_id',
        'semifinal' => 'final_route_id',
        'qualification' => 'route_id',
        ];

    public function handle(Request $request)
    {
        $results = $request->toArray();

        $amount_try_top = intval($results['amount_try_top']);
        $amount_try_zone = intval($results['amount_try_zone']);
        $all_attempts = intval($results['all_attempts']);
        $event_id = intval($results['event_id']);
        $user_id = intval($results['user_id']);
        $event = Event::find($event_id);
        $route_id = intval($results['route_id']);
        if($amount_try_top > 0){
            $amount_top  = 1;
        } else {
            $amount_top  = 0;
        }
        if($amount_try_zone > 0){
            $amount_zone  = 1;
        } else {
            $amount_zone  = 0;
        }
        if($route_id == 0){
            return $this->response()->error('Вы не выбрали номер маршрута');
        }
        $max_attempts = Helpers::find_max_attempts($amount_try_top, $amount_try_zone);
        if(Helpers::validate_amount_sum_top_and_zone_and_attempts($all_attempts, $amount_try_top, $amount_try_zone)){
            return $this->response()->error(
                'У трассы '.$route_id.' Максимальное кол-во попыток '.$max_attempts.' а в поле все попытки - '. $all_attempts);
        }
        # Если есть ТОП то зона не может быть 0
        if(Helpers::validate_amount_top_and_zone($amount_top, $amount_zone)){
            return $this->response()->error('У трассы '.$route_id.' отмечен ТОП, и получается зона не может быть 0');
        }

        # Кол-во попыток на зону не может быть меньше чем кол-во на ТОП
        if(Helpers::validate_amount_try_top_and_zone($amount_try_top, $amount_try_zone)){
            return $this->response()->error('Кол-во попыток на зону не может быть меньше, чем кол-во попыток на ТОП, трасса '.$route_id );
        }

        if($event->is_france_system_qualification){
            $participant = ResultFranceSystemQualification::where('event_id', $event_id)->where('user_id', $user_id)->first();
        } else {
            $participant = ResultQualificationClassic::where('event_id', $event_id)->where('user_id', $user_id)->first();
        }
        if($event->is_open_main_rating && $event->is_auto_categories){
            $category_id = $participant->global_category_id;
        } else {
            $category_id = $participant->category_id;
        }
        $gender = $participant->gender;
        $owner_id = \Encore\Admin\Facades\Admin::user()->id;
        if($this->stage == 'final' || $this->stage == 'semifinal'){
            ResultRouteFinalStage::update_semi_or_final_route_results(
                stage: $this->stage,
                owner_id: $owner_id,
                event_id: $event_id,
                category_id: $category_id,
                route_id: $route_id,
                user_id: $user_id,
                amount_try_top: $amount_try_top,
                amount_try_zone: $amount_try_zone,
                amount_top: $amount_top,
                amount_zone: $amount_zone,
                gender: $gender,
                all_attempts: $all_attempts,
            );
        }
        if($this->stage == 'qualification'){
            $number_set_id = $participant->number_set_id;
            ResultFranceSystemQualification::update_france_route_results(
                owner_id: $owner_id,
                event_id: $event_id,
                category_id: $category_id,
                route_id: $route_id,
                user_id: $user_id,
                amount_try_top: $amount_try_top,
                amount_try_zone: $amount_try_zone,
                amount_top: $amount_top,
                amount_zone: $amount_zone,
                gender: $gender,
                all_attempts: $all_attempts,
                number_set_id: $number_set_id
            );
        }

        switch ($this->stage){
            case 'final':
                Event::refresh_final_points_all_participant_in_final($event_id);
                break;
            case 'semifinal':
                Event::refresh_final_points_all_participant_in_semifinal($event_id);
                break;
            case 'qualification':
                Event::refresh_france_system_qualification_counting($event);
                break;
        }
        return $this->response()->success('Результат успешно внесен')->refresh();
    }

    public function custom_form()
    {
        $this->modalSmall();
        $url_get_attempts = self::URLS_GET_ATTEMPTS[$this->stage];
        $url_set_attempts = self::URLS_SET_ATTEMPTS[$this->stage];
        $event = Event::where('owner_id', '=', \Encore\Admin\Facades\Admin::user()->id)
            ->where('active', '=', 1)->first();
        $amount_routes = self::get_amount_routes($event, $this->stage);
        if($event->is_open_main_rating){
            switch ($this->stage){
                case 'final':
                    $merged_users = ResultFinalStage::get_final_global_participant($event);
                    $result = $merged_users->pluck( 'middlename','id');
                    break;
                case 'semifinal':
                    $merged_users = ResultSemiFinalStage::get_global_participant_semifinal($event);
                    $result = $merged_users->pluck( 'middlename','id');
                    break;
                case 'qualification':
                    $participant_users_id = ResultFranceSystemQualification::where('event_id', $event->id)->pluck('global_user_id')->toArray();
                    $result = User::whereIn('id', $participant_users_id)->pluck('middlename','id');
                    break;
            }
        } else {
            switch ($this->stage){
                case 'final':
                    $merged_users = ResultFinalStage::get_final_participant($event);
                    $result = $merged_users->pluck( 'middlename','id');
                    break;
                case 'semifinal':
                    $merged_users = ResultSemiFinalStage::get_participant_semifinal($event);
                    $result = $merged_users->pluck( 'middlename','id');
                    break;
                case 'qualification':
                    $participant_users_id = ResultFranceSystemQualification::where('event_id', $event->id)->pluck('user_id')->toArray();
                    $result = User::whereIn('id', $participant_users_id)->pluck('middlename','id');
                    break;
            }
        }
        $modelClass = self::MODELS_ROUTE[$this->stage];
        $result_final = $modelClass::where('event_id', '=', $event->id)->select('user_id')->distinct()->pluck('user_id')->toArray();
        $categories = ParticipantCategory::where('event_id', $event->id)->pluck('category', 'id')->toArray();
        foreach ($result as $user_id => $middlename){
            $result[$user_id] = $middlename;
            if(in_array($user_id, $result_final)){
                $result_user = $modelClass::where('event_id', $event->id)->where('user_id', $user_id);
                $routes = $result_user->get()->sortBy(self::ROUTE[$this->stage])->pluck(self::ROUTE[$this->stage])->toArray();
                $string_version = '';
                foreach ($routes as $value) {
                    $string_version .= $value . ', ';
                }
                if($result_user->get()->count() == $amount_routes){
                    $result[$user_id] = $middlename.' [Добавлены все трассы]';
                } else {
                    $result[$user_id] = $middlename.' [Трассы: '.$string_version.']';
                }
            }
        }
        $routes = [];
        for($i = 1; $i <= $amount_routes; $i++){
            $routes[$i] = $i;
        }
        $result = $result->toArray();
        asort($result);
        $this->select('category_id', 'Группа')->attribute('autocomplete', 'off')->attribute('data-category-id', 'category_id')->options($categories);
        $this->select('user_id', 'Участник')->attribute('autocomplete', 'off')->attribute('data-user-id', 'user_id')->options($result)->required();

        $this->hidden('event_id', '')->attribute('autocomplete', 'off')->attribute('data-event-id', 'event_id')->value($event->id);
        $this->text('user_gender', 'Пол')->attribute('autocomplete', 'off')->readonly();
        $this->text('user_category', 'Группа')->attribute('autocomplete', 'off')->readonly();
        $this->select('route_id', 'Трасса')->attribute('autocomplete', 'off')->attribute('data-route-id', 'route_id')->options($routes)->required();
        $this->integer('all_attempts', 'Все попытки')
            ->attribute('autocomplete', 'off')
            ->attribute('id', 'all_attempts')
            ->attribute('data-all-attempts-id', 'all-attempts');
        $this->integer('amount_try_zone', 'Попытки на зону')->attribute('id', 'amount_try_zone')->attribute('data-amount-try-zone', 'amount_try_zone');
        $this->integer('amount_try_top', 'Попытки на топ')->attribute('id', 'amount_try_top')->attribute('data-amount-try-top', 'amount_try_top');

        Admin::style('
            .input-group {
                display: flex;
                align-items: center;
            }

            #increment-btn {
                font-size: 20px;
            }

            #zone-btn {
                font-size: 20px;
            }

            #top-btn {
                font-size: 20px;
            }

            .form-control {
                margin-right: -1px; /* Небольшой выступ для кнопки */
            }

            .input-group-append {
                margin-top: 10px;
                margin-left: 5px; /* Убираем отступ слева */
            }

            .btn-warning {
                margin-left: 5px;
            }

        ');
        $script_one_route = <<<EOT
                            function set_attempt(){
                                var routeId = $('[data-route-id=route_id]').val(); // ID выбранного маршрута
                                var userId = $('[data-user-id=user_id]').select2('val')
                                var eventId = $('[data-event-id=event_id]').val(); // ID выбранного участника
                                var attempt = $('[data-all-attempts-id=all-attempts]').val();
                                var amount_try_top = $('[id=amount_try_top]').val();
                                var amount_try_zone = $('[id=amount_try_zone]').val();
                                let missingFields = [];

                                if (!attempt) {
                                    missingFields.push('Попытки');
                                }
                                if (!userId) {
                                    missingFields.push('Участник');
                                }
                                if (!routeId) {
                                    missingFields.push('Трасса');
                                }
                                if (missingFields.length > 0) {
                                   return  $.admin.toastr.error(
                                        'Не хватает данных для их отправки: ' + missingFields.join(', '),
                                        '',
                                        { positionClass: "toast-bottom-center", timeOut: 1000 }
                                    ).css("width", "200px");
                                }
                                 if(Number(amount_try_top) == 1 && Number(amount_try_zone) == 0){
                                         return $.admin.toastr.error(
                                            'У трассы '+routeId+ ' отмечен ТОП, а зона 0 попыток' + missingFields.join(', '),
                                            '',
                                            { positionClass: "toast-bottom-center", timeOut: 2000 }
                                        ).css("width", "200px");
                                    }
                                if(routeId){
                                    $.get("$url_set_attempts",
                                        {
                                            route_id: routeId,
                                            user_id: userId,
                                            event_id: eventId,
                                            attempt: attempt,
                                            amount_try_top: amount_try_top,
                                            amount_try_zone: amount_try_zone
                                        },
                                        function (data) {
                                            $.admin.toastr.success(
                                                'Сохранено',
                                                { positionClass: "toast-bottom-center", timeOut: 1000 }
                                            ).css("width", "200px");
                                            $('[data-amount_try_top=amount_try_top]').val(data.amount_try_top);
                                            $('[data-amount_try_zone=amount_try_zone]').val(data.amount_try_zone);
                                            $('[data-all-attempts-id=all-attempts]').val(data.all_attempts);
                                        }
                                    );
                                }
                            }
                         $(document).on("click", '#universal-$this->stage', function () {
                            const allAttemptsInput = document.getElementById('all_attempts');
                            const incrementBtn = document.getElementById('increment-btn');
                            const decrementBtn = document.getElementById('decrement-btn');
                            const topInput = document.getElementById('amount_try_top');
                            const zoneInput = document.getElementById('amount_try_zone');
                            const zoneBtn = document.getElementById('zone-btn');
                            const topBtn = document.getElementById('top-btn');

                                if (!incrementBtn && !decrementBtn && !zoneBtn && !topBtn) {
                                    const inputGroupAppend = document.createElement('div');
                                    const inputGroupAppend2 = document.createElement('div');
                                    const inputGroupAppend3 = document.createElement('div');
                                    inputGroupAppend.className = 'input-group-append';
                                    inputGroupAppend2.className = 'input-group-append';
                                    inputGroupAppend3.className = 'input-group-append';

                                    const newIncrementBtn = document.createElement('button');
                                    newIncrementBtn.type = 'button';
                                    newIncrementBtn.className = 'btn btn-warning';
                                    newIncrementBtn.id = 'increment-btn';

                                    const newZoneBtn = document.createElement('button');
                                    newZoneBtn.type = 'button';
                                    newZoneBtn.className = 'btn btn-success';
                                    newZoneBtn.id = 'zone-btn';
                                    const newTopBtn = document.createElement('button');
                                    newTopBtn.type = 'button';
                                    newTopBtn.className = 'btn btn-success';
                                    newTopBtn.id = 'top-btn';
                                    const incrementIcon = document.createElement('i');
                                    incrementIcon.className = 'fa fa-plus';

                                    const incrementText = document.createElement('span');
                                    const zoneText = document.createElement('span');
                                    const topText = document.createElement('span');
                                    zoneText.textContent = 'Зона';
                                    incrementText.textContent = ' Попытка';
                                    topText.textContent = 'Топ';

                                    newIncrementBtn.appendChild(incrementIcon);
                                    newIncrementBtn.appendChild(incrementText);
                                    newZoneBtn.appendChild(zoneText);
                                    newTopBtn.appendChild(topText);

                                    const newDecrementBtn = document.createElement('button');
                                    newDecrementBtn.type = 'button';
                                    newDecrementBtn.className = 'btn btn-danger';
                                    newDecrementBtn.id = 'decrement-btn';

                                    const decrementIcon = document.createElement('i');
                                    decrementIcon.className = 'fa fa-minus';

                                    newDecrementBtn.appendChild(decrementIcon);

                                    inputGroupAppend.appendChild(newDecrementBtn);
                                    inputGroupAppend.appendChild(newIncrementBtn);
                                    inputGroupAppend2.appendChild(newZoneBtn);
                                    inputGroupAppend3.appendChild(newTopBtn);

                                    allAttemptsInput.parentNode.appendChild(inputGroupAppend);
                                    zoneInput.parentNode.appendChild(inputGroupAppend2);
                                    topInput.parentNode.appendChild(inputGroupAppend3);

                                    newZoneBtn.addEventListener('click', function () {
                                        let currentValue = parseInt(allAttemptsInput.value) || 0;
                                         $('[data-amount-try-zone=amount_try_zone]').val(currentValue);
                                        set_attempt()
                                    });
                                    newTopBtn.addEventListener('click', function () {
                                        let currentValue = parseInt(allAttemptsInput.value) || 0;
                                         $('[data-amount-try-top=amount_try_top]').val(currentValue);
                                        set_attempt()
                                    });
                                    newIncrementBtn.addEventListener('click', function () {
                                        let currentValue = parseInt(allAttemptsInput.value) || 0;
                                        allAttemptsInput.value = currentValue + 1;
                                        set_attempt()
                                    });

                                    newDecrementBtn.addEventListener('click', function () {
                                        let currentValue = parseInt(allAttemptsInput.value) || 0;
                                        if (currentValue > 0) {
                                            allAttemptsInput.value = currentValue - 1;
                                        }
                                        set_attempt()
                                    });
                                    $('[data-all-attempts-id=all-attempts]').val('');
                                    $('[data-amount-try-top=amount_try_top]').val('');
                                    $('[data-amount-try-zone=amount_try_zone]').val('');
                                    $('[data-user-id=user_id]').val('');
                                }
                            });
                            $(document).on("change", '[data-user-id="user_id"]', function () {
                                var routeId = $('[data-route-id=route_id]').val();
                                var userId = $('[data-user-id="user_id"]').select2('val')
                                var eventId = $('[data-event-id=event_id]').val();
                                if(routeId){
                                    $.get("$url_get_attempts",
                                        {
                                            route_id: routeId,
                                            user_id: userId,
                                            event_id: eventId
                                        },
                                        function (data) {
                                            $('[data-amount-try-top=amount_try_top]').val(data.amount_try_top);
                                            $('[data-amount-try-zone=amount_try_zone]').val(data.amount_try_zone);
                                            $('[data-all-attempts-id=all-attempts]').val(data.all_attempts);
                                        }
                                    );
                                }
                                if(userId){
                                    $.get("/admin/api/get_user_info", // URL эндпоинта
                                        {
                                            user_id: userId,
                                            event_id: eventId
                                        },
                                        function (data) {
                                            $('[id="user_gender"]').val(data.gender);
                                            $('[id="user_category"]').val(data.category);
                                        }
                                    );
                                }
                            });
                            $(document).on("change", '[data-route-id=route_id]', function () {
                                var routeId = $(this).val(); // ID выбранного маршрута
                                var userId = $('[data-user-id="user_id"]').select2('val')
                                var eventId = $('[data-event-id=event_id]').val(); // ID выбранного участника
                                $.get("$url_get_attempts", // URL эндпоинта
                                    {
                                        route_id: routeId,
                                        user_id: userId,
                                        event_id: eventId
                                    },
                                    function (data) {
                                        // Обновляем поля с количеством попыток
                                        $('[data-all-attempts-id=all-attempts]').val(data.all_attempts);
                                        $('[data-amount-try-top=amount_try_top]').val(data.amount_try_top);
                                        $('[data-amount-try-zone=amount_try_zone]').val(data.amount_try_zone);
                                    }
                                );

                            });
                            $(document).on("change", '[data-category-id=category_id]', function () {
                                var categoryId = $('[data-category-id=category_id]').select2('val')
                                var eventId = $('[data-event-id=event_id]').val(); // ID выбранного участника
                                $('[data-user-id=user_id]').val(''); // ID выбранного участника

                                $.get("/admin/api/get_users",
                                    {eventId: eventId, categoryId: categoryId, stage: '$this->stage'},
                                    function (data) {
                                        var model = $('[data-user-id=user_id]');
                                        model.empty();
                                        model.append("<option>Выбрать</option>");
                                        var sortedData = Object.entries(data).sort(function (a, b) {
                                            return a[1].localeCompare(b[1]); // сортируем по значению (имя пользователя)
                                        });
                                        $.each(sortedData, function (i, item) {
                                            var userId = item[0];
                                            var userName = item[1];
                                            model.append("<option data-user-id='" + userId + "' value='" + userId + "'>" + userName + "</option>");
                                        });
                                    }
                                );

                            });
                            let icon_close = '[id="app-admin-actions-resultroute-batchresultrouteuniversal"] [data-dismiss="modal"][class="close"]'
                            $(document).on("click", icon_close, function () {
                                window.location.reload();
                            });
                            let btn_close = '[id="app-admin-actions-resultroute-batchresultrouteuniversal"] [data-dismiss="modal"][class="btn btn-default"]'
                            $(document).on("click", btn_close, function () {
                                window.location.reload();
                            });
                        EOT;

        \Encore\Admin\Facades\Admin::script($script_one_route);
        $script_log = <<<EOT
                            observer.observe(document.body, {
                                attributes: true,
                                childList: true,
                                subtree: true
                            });
                            document.addEventListener('click', function(event) {
                                console.log('Клик по элементу:', event.target);
                            });

                            document.addEventListener('change', function(event) {
                                console.log('Изменение в элементе:', event.target);
                            });
                        EOT;
//        \Encore\Admin\Facades\Admin::script($script_log);

    }

    public function html()
    {
        $event = Event::where('owner_id', '=', \Encore\Admin\Facades\Admin::user()->id)->where('active', 1)->first();
        if($event->amount_the_best_participant_to_go_final > 0){
            return "<a id='universal-{$this->stage}' class='result-universal btn btn-sm btn-primary'><i class='fa fa-plus-circle'></i> По одной трассе</a>
                 <style>
                 .result-universal {margin-top:8px;}
                 @media screen and (max-width: 767px) {
                        .result-universal {margin-top:8px;}
                    }
                </style>
            ";
        } else {
            return "<a href='#' class='result-universal btn btn-sm btn-primary' disabled>Кол-во участников в финал 0</a>
                 <style>
                 .result-universal {margin-top:8px;}
                 @media screen and (max-width: 767px) {
                        .result-universal {margin-top:8px;}
                    }
                </style>
            ";
        }

    }
    public static function get_amount_routes($event, $stage)
    {
        switch ($stage){
            case 'final':
                return $event->amount_routes_in_final;
            case 'semifinal':
                return $event->amount_routes_in_semifinal;
            case 'qualification':
                return Grades::where('event_id', $event->id)->first()->count_routes ?? 0;
        }
    }
}



