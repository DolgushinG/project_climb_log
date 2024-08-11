<?php

namespace App\Exports\Sheets;

use App\Models\Event;
use App\Models\EventAndCoefficientRoute;
use App\Models\Format;
use App\Models\Grades;
use App\Models\ResultQualificationClassic;
use App\Models\ResultRouteQualificationClassic;
use App\Models\ResultRouteFinalStage;
use App\Models\ResultRouteFranceSystemQualification;
use App\Models\ResultRouteSemiFinalStage;
use App\Models\Route;
use App\Models\RoutesOutdoor;
use App\Models\Set;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Sheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use function Symfony\Component\String\s;

class Results implements FromCollection, WithTitle, WithCustomStartCell, WithHeadings, ShouldAutoSize, WithEvents, WithStyles
{
    public $event_id;
    public $type;
    public $gender;
    public $category;

    function __construct($event_id, $type, $gender = '', $category = '') {
        $this->event_id = $event_id;
        $this->type = $type;
        $this->gender = $gender;
        $this->category = $category;
    }
    public function styles(Worksheet $sheet)
    {
        if($this->type == 'Qualification' || $this->type == 'MergeQualification'){
            return [
                1    => ['font' => ['bold' => true]],
            ];
        } else {
            return [
                1    => ['font' => ['bold' => true]],
                2    => ['font' => ['bold' => true]],
            ];
        }

    }

    public function startCell(): string
    {
        switch ($this->type){
            case 'MergeQualification':
            case 'Qualification':
                return 'A1';
                break;
            default :
                return 'A3';
        }
    }

    public function registerEvents(): array {
        return [
                AfterSheet::class => function(AfterSheet $event) {
                    /** @var Sheet $sheet */
                    $sheet = $event->sheet;
                    $style = [
                        'alignment' => [
                            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                            'wrapText' => true,
                            'color' => array('rgb' => 'FF0000'),
                            'size'      =>  25,
                        ],
                    ];

                    if($this->type == 'Final' || $this->type == 'SemiFinal' || $this->type == 'FranceSystemQualification') {
                        $sheet->mergeCells('A1:C1');
                        $sheet->setCellValue('A1', $this->title());
                        $sheet->getStyle('A1')->applyFromArray($style);
                        $merge_cells = [
                            1 => ["G","J"],
                            2 => ["K", "N"],
                            3 => ["O", "R"],
                            4 => ["S", "V"],
                            5 => ["W", "Z"],
                            6 => ["AA", "AD"],
                            7 => ["AE", "AH"],
                            8 => ["AI", "AL"],
                            9 => ["AM", "AP"],
                            10 => ["AQ", "AT"],
                            11 => ["AU", "AX"],
                            12 => ["AY", "BB"],
                            13 => ["BC", "BF"],
                            14 => ["BG", "BJ"],
                            15 => ["BK", "BN"],
                            16 => ["BO", "BR"],
                            17 => ["BS", "BV"],
                            18 => ["BW", "BZ"],
                            19 => ["CA", "CD"],
                            20 => ["CE", "CH"],
                        ];
                        $cell_title = [
                            1 => "I",
                            2 => "M",
                            3 => "Q",
                            4 => "U",
                            5 => "Y",
                            6 => "AC",
                            7 => "AG",
                            8 => "AK",
                            9 => "AO",
                            10 => "AS",
                            11 => "AW",
                            12 => "BA",
                            13 => "BE",
                            14 => "BI",
                            15 => "BM",
                            16 => "BQ",
                            17 => "BU",
                            18 => "BY",
                            19 => "CC",
                            20 => "CG",
                        ];
                        switch ($this->type){
                            case 'Final':
                                $count = ResultRouteFinalStage::count_route_in_final_stage($this->event_id);
                                break;
                            case 'SemiFinal':
                                $count = ResultRouteSemiFinalStage::count_route_in_semifinal_stage($this->event_id);
                                break;
                            case 'FranceSystemQualification':
                                $count = ResultRouteFranceSystemQualification::count_route_in_qualification_final($this->event_id);
                        }
                        for($i = 1; $i <= $count; $i++){
                            $sheet->mergeCells($merge_cells[$i][0].'2:'.$merge_cells[$i][1].'2');
                            $sheet->setCellValue($cell_title[$i].'2', "Трасса ".$i);
                            $sheet->getStyle($cell_title[$i])->applyFromArray($style);
                        }
                    }
                },
            ];
    }

    public function headings(): array
    {

        switch ($this->type){
            case 'Final':
                $final = [
                    'Место',
                    'Участник(Фамилия Имя)',
                    'Сумма TOP',
                    'Сумма попыток на TOP',
                    'Сумма ZONE',
                    'Сумма попыток на ZONE',
                ];
                $count = ResultRouteFinalStage::count_route_in_final_stage($this->event_id);
                for($i = 1; $i <= $count; $i++){
                    $final[] = 'TOP';
                    $final[] = 'Попытки на TOP';
                    $final[] = 'ZONE';
                    $final[] = 'Попытки на ZONE';
                }
                return $final;
            case 'SemiFinal':
                $final = [
                    'Место',
                    'Участник(Фамилия Имя)',
                    'Сумма TOP',
                    'Сумма попыток на TOP',
                    'Сумма ZONE',
                    'Сумма попыток на ZONE',
                ];
                $count = ResultRouteSemiFinalStage::count_route_in_semifinal_stage($this->event_id);
                for($i = 1; $i <= $count; $i++){
                    $final[] = 'TOP';
                    $final[] = 'Попытки на TOP';
                    $final[] = 'ZONE';
                    $final[] = 'Попытки на ZONE';
                }
                return $final;
            case 'FranceSystemQualification':
                $france_system_qualification = [
                    'Место',
                    'Участник(Фамилия Имя)',
                    'Сумма TOP',
                    'Сумма попыток на TOP',
                    'Сумма ZONE',
                    'Сумма попыток на ZONE',
                ];
                $count = ResultRouteFranceSystemQualification::count_route_in_qualification_final($this->event_id);
                for($i = 1; $i <= $count; $i++){
                    $france_system_qualification[] = 'TOP';
                    $france_system_qualification[] = 'Попытки на TOP';
                    $france_system_qualification[] = 'ZONE';
                    $france_system_qualification[] = 'Попытки на ZONE';
                }
                return $france_system_qualification;
            case 'Qualification':
                $event = Event::find($this->event_id);
                $qualification[] = 'Место';
                $qualification[] = 'Участник(Фамилия Имя)';
                $qualification[] = 'Баллы';
                if(!$event->is_input_set) {
                    $qualification[] = 'Сет';
                }
                $qualification[] = 'Кол-во пройденных трасс';
                if($event->is_flash_value) {
                    $qualification[] = 'Кол-во FLASH';
                }
                if($event->is_zone_show){
                    $qualification[] = 'Кол-во ZONE';
                }
                if($event->is_flash_value) {
                    $qualification[] = 'Кол-во REDPOINT';
                } else {
                    $qualification[] = 'Кол-во ТОП';
                }
                $count = Grades::where('event_id', $this->event_id)->first()->count_routes;
                for($i = 1; $i <= $count; $i++){
                    $qualification[] = 'Трасса '.$i;
                }
                return $qualification;
            case 'MergeQualification':
                return [
                    'Место',
                    'Участник(Фамилия Имя)',
                    'Суммарные Баллы',
                ];
            case 'Team':
                return [
                    'Место',
                    'Команда',
                    'Суммарные Баллы',
                ];
            default:
                return [];
        }

    }


    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        if($this->type == 'SemiFinal'){
            return self::get_final('result_semifinal_stage');
        }
        if($this->type == 'Final'){
            return self::get_final('result_final_stage');
        }
        if($this->type == 'FranceSystemQualification'){
            return self::get_final('result_france_system_qualification');
        }
        if($this->type == 'Qualification'){
            return self::get_qualification();
        }
        if($this->type == 'MergeQualification'){
            return self::get_merge_qualification();
        }
        if($this->type == 'Team'){
            return self::get_team_qualification();
        }
        return collect([]);
    }

    /**
     * @return string
     */
    public function title(): string
    {
        if($this->category){
            $category = $this->category->category;
        } else {
            $category = '';
        }
        if($this->category == '' && $this->gender == ''){
            return trans_choice('somewords.'.$this->type, 10);
        }
        return trans_choice('somewords.'.$this->type, 10).
            ' [ '.$category.' ][ '.trans_choice('somewords.'.$this->gender, 10).']';
    }


    public function get_qualification(){
        $users = User::query()
            ->leftJoin('result_qualification_classic', 'users.id', '=', 'result_qualification_classic.user_id')
            ->where('result_qualification_classic.event_id', '=', $this->event_id)
            ->where('result_qualification_classic.category_id', '=', $this->category->id)
            ->select(
                'users.id',
                'result_qualification_classic.user_place',
                'users.middlename',
                'result_qualification_classic.points',
                'result_qualification_classic.owner_id',
                'result_qualification_classic.number_set_id',
            )
            ->where('is_other_event', 0)
            ->where('result_qualification_classic.gender', '=', $this->gender)->get()->sortBy('user_place')->toArray();
        if(!$users){
            return collect([]);
        }
        $event = Event::find($this->event_id);
        $users['empty_row'] = array(
            "id" => "",
            "user_place" => "",
            "middlename" => "",
            "points" => "",
            "owner_id" => "",
            "number_set_id" => "",
        );
        $users_for_filter = ResultQualificationClassic::where('event_id', $this->event_id)->pluck('user_id')->toArray();
        foreach ($users as $index => $user) {
            if ($index == 'empty_row') {
                $count = Grades::where('event_id', $this->event_id)->first()->count_routes;
                $users[$index]['user_place'] = '';
                $users[$index]['number_set_id'] = '';
                $users[$index]['amount_passed_routes'] = '';
                if($event->is_flash_value){
                    $users[$index]['amount_passed_flash'] = '';
                }
                if($event->is_zone_show){
                    $users[$index]['amount_passed_zone'] = '';
                }
                if ($event->mode == Format::ALL_ROUTE) {
                    $users[$index]['amount_passed_redpoint'] = 'Коэффициент трасс';
                    $coefficient = EventAndCoefficientRoute::where('event_id', $this->event_id)->select('route_id', 'coefficient_' . $this->gender)->pluck('coefficient_' . $this->gender, 'route_id');
                    for ($i = 1; $i <= $count; $i++) {
                        $users[$index]['route_result_' . $i] = $coefficient[$i];
                    }
                } else {
                    if($event->is_flash_value && $event->is_zone_show){
                        $users[$index]['amount_passed_redpoint'] = 'Баллы за трассу [за флеш][за зону]';
                    } else if($event->is_flash_value && !$event->is_zone_show){
                        $users[$index]['amount_passed_redpoint'] = 'Баллы за трассу [за флеш]';
                    } else if(!$event->is_flash_value && $event->is_zone_show) {
                        $users[$index]['amount_passed_redpoint'] = 'Баллы за трассу [за зону]';
                    } else {
                        $users[$index]['amount_passed_redpoint'] = 'Баллы за трассу';
                    }

                    if($event->type_event){
                        $routes_event = RoutesOutdoor::where('event_id', $this->event_id)->get();
                    } else {
                        $routes_event = Route::where('event_id', $this->event_id)->get();
                    }

                    foreach ($routes_event as $index2 => $route) {
                        if($event->is_flash_value && $event->is_zone_show){
                            $users[$index]['route_result_' . $index2] = $route->value.' ['.$route->flash_value.']['.$route->zone.']';
                        } else if($event->is_flash_value && !$event->is_zone_show){
                            $users[$index]['route_result_' . $index2] = $route->value.' ['.$route->flash_value.']';
                        } else if(!$event->is_flash_value && $event->is_zone_show) {
                            $users[$index]['route_result_' . $index2] = $route->value.' ['.$route->zone.']';
                        } else {
                            $users[$index]['route_result_' . $index2] = $route->value;
                        }
                    }
                }
            } else {
                $qualification_result = ResultRouteQualificationClassic::where('event_id', '=', $this->event_id)->where('user_id', '=', $user['id'])->get();
                $amount_passed_flash = ResultRouteQualificationClassic::where('event_id', '=', $this->event_id)->where('user_id', '=', $user['id'])->where('attempt', 1)->get()->count();
                if($event->is_zone_show){
                    $amount_passed_zone = ResultRouteQualificationClassic::where('event_id', '=', $this->event_id)->where('user_id', '=', $user['id'])->where('attempt', 3)->get()->count();
                } else {
                    $amount_passed_zone = 0;
                }
                $amount_passed_redpoint = ResultRouteQualificationClassic::where('event_id', '=', $this->event_id)->where('user_id', '=', $user['id'])->where('attempt', 2)->get()->count();
                $amount_passed_routes = $amount_passed_flash + $amount_passed_redpoint + $amount_passed_zone;
                $place = ResultQualificationClassic::get_places_participant_in_qualification($this->event_id, $users_for_filter, $user['id'], $this->gender, $this->category->id, true);
                $users[$index]['user_place'] = $place;
                $set = Set::find($user['number_set_id']);
                $users[$index]['number_set_id'] = $set->number_set ?? '';
                $users[$index]['amount_passed_routes'] = $amount_passed_routes;
                if($event->is_flash_value){
                    $users[$index]['amount_passed_flash'] = $amount_passed_flash;
                }
                if($event->is_zone_show){
                    $users[$index]['amount_passed_zone'] = $amount_passed_zone;
                }
                $users[$index]['amount_passed_redpoint'] = $amount_passed_redpoint;
                $routes_event_value = Route::where('event_id', $this->event_id)->pluck('value', 'route_id')->toArray();
                if($event->is_zone_show){
                    $routes_event_zone = Route::where('event_id', $this->event_id)->pluck('zone', 'route_id')->toArray();
                }
                if($event->is_flash_value){
                    $routes_event_flash_value = Route::where('event_id', $this->event_id)->pluck('flash_value', 'route_id')->toArray();
                }
                foreach ($qualification_result as $result){
                    switch ($result->attempt){
                        case ResultRouteQualificationClassic::STATUS_PASSED_FLASH:
                            if($event->mode == Format::N_ROUTE || $event->mode == Format::ALL_ROUTE_WITH_POINTS){
                                $attempt = $routes_event_value[$result->route_id] + $routes_event_flash_value[$result->route_id] ?? 0;
                            } else {
                                $attempt = 'F';
                            }
                            break;
                        case ResultRouteQualificationClassic::STATUS_PASSED_REDPOINT:
                            if($event->mode == Format::N_ROUTE || $event->mode == Format::ALL_ROUTE_WITH_POINTS ){
                                $attempt = $routes_event_value[$result->route_id];
                            } else {
                                $attempt = 'R';
                            }
                            break;
                        case ResultRouteQualificationClassic::STATUS_ZONE:
                            if($event->mode == Format::N_ROUTE || $event->mode == Format::ALL_ROUTE_WITH_POINTS && $event->is_zone_show){
                                $attempt = $routes_event_zone[$result->route_id];
                            }
                            break;
                        case 0:
                            $attempt = '-';
                    }
                    $users[$index]['route_result_'.$result->route_id] = $attempt;
                }
            }
            $except = ['id', 'owner_id','user_global_place','global_points'];
            if($event->is_input_set) {
                $except[] = 'number_set_id';
            }
            $users[$index] = collect($users[$index])->except($except);
        }
        $users_need_sorted = collect($users)->toArray();
        usort($users_need_sorted, function ($a, $b) {
            // Проверяем, если значение 'user_place' пустое, перемещаем его в конец
            if (empty($a['user_place'])) {
                return 1; // $a должно быть после $b
            } elseif (empty($b['user_place'])) {
                return -1; // $a должно быть перед $b
            } else {
                return $a['user_place'] <=> $b['user_place'];
            }
        });
        return collect($users_need_sorted);

    }
    public function get_merge_qualification(){
        $event = Event::find($this->event_id);
        if($event->is_auto_categories){
            $column_category_id = 'global_category_id';
        } else {
            $column_category_id = 'category_id';
        }
        $users = User::query()
            ->leftJoin('result_qualification_classic', 'users.id', '=', 'result_qualification_classic.user_id')
            ->where('result_qualification_classic.event_id', '=', $this->event_id)
            ->where('result_qualification_classic.'.$column_category_id, '=', $this->category->id)
            ->select(
                'users.id',
                'result_qualification_classic.user_global_place',
                'users.middlename',
                'result_qualification_classic.global_points',
                'result_qualification_classic.owner_id',
            )
            ->where('result_qualification_classic.gender', '=', $this->gender)->get()->sortBy('user_global_place')->toArray();
        if(!$users){
            return collect([]);
        }
        $users['empty_row'] = array(
            "id" => "",
            "user_global_place" => "",
            "middlename" => "",
            "points" => "",
            "owner_id" => "",
            "number_set_id" => "",
        );
        $users_for_filter = ResultQualificationClassic::where('event_id', $this->event_id)->pluck('user_id')->toArray();
        foreach ($users as $index => $user) {
            if ($index == 'empty_row') {
                $users[$index]['user_global_place'] = '';
            }
            $users[$index] = collect($users[$index])->except('id', 'owner_id');
        }
        $users_need_sorted = collect($users)->toArray();
        usort($users_need_sorted, function ($a, $b) {
            // Проверяем, если значение 'user_place' пустое, перемещаем его в конец
            if (empty($a['user_global_place'])) {
                return 1; // $a должно быть после $b
            } elseif (empty($b['user_global_place'])) {
                return -1; // $a должно быть перед $b
            } else {
                return $a['user_global_place'] <=> $b['user_global_place'];
            }
        });
        return collect($users_need_sorted);
    }
    public function get_team_qualification(){
        $event = Event::find($this->event_id);
        $user_team_ids = ResultQualificationClassic::where('event_id', '=', $event->id)->where('active','=', 1)->pluck('user_id')->toArray();
        $teams = User::whereIn('id', $user_team_ids)->where('team','!=', null)->distinct()->pluck('team')->toArray();
        foreach ($teams as $team){
            $result_team_cache = ResultQualificationClassic::get_list_team_and_points_participant($event->id, $team);
            $result_team[$team] = $result_team_cache;
        }
        $result_team = ResultQualificationClassic::sorted_team_points($result_team);
        return collect($result_team);
    }

    public function get_final($table){
        $users = User::query()
            ->leftJoin($table, 'users.id', '=', $table.'.user_id')
            ->where($table.'.event_id', '=', $this->event_id)
            ->select(
                $table.'.place',
                'users.id',
                'users.middlename',
                $table.'.category_id',
                $table.'.amount_top',
                $table.'.amount_try_top',
                $table.'.amount_zone',
                $table.'.amount_try_zone',
            )->where($table.'.gender', '=', $this->gender);
        if($this->category){
            $users = $users->where('category_id', '=', $this->category->id);
        }

        $users = $users->get()->sortBy('place')->toArray();
        foreach ($users as $index => $user){
            if($table == 'result_final_stage'){
                $final_result = ResultRouteFinalStage::where('event_id', '=', $this->event_id)->where('user_id', '=', $user['id'])->get();
            }
            if($table === "result_france_system_qualification"){
                $final_result = ResultRouteFranceSystemQualification::where('event_id', '=', $this->event_id)->where('user_id', '=', $user['id'])->get();
            }
            if($table === "result_semifinal_stage"){
                $final_result = ResultRouteSemiFinalStage::where('event_id', '=', $this->event_id)->where('user_id', '=', $user['id'])->get();
            }
            foreach ($final_result as $result){
                if($table == 'result_final_stage' || $table === "result_semifinal_stage"){
                    $route_id = $result->final_route_id;
                } else {
                    $route_id = $result->route_id;
                }
                $users[$index]['amount_top_'.$route_id] = $result->amount_top;
                $users[$index]['amount_try_top_'.$route_id] = $result->amount_try_top;
                $users[$index]['amount_zone_'.$route_id] = $result->amount_zone;
                $users[$index]['amount_try_zone_'.$route_id] = $result->amount_try_zone;
            }
            $users[$index] = collect($users[$index])->except('id', 'category_id');
        }
        return collect($users);
    }
}
