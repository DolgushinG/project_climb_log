<?php

namespace App\Exports\Sheets;

use App\Models\Event;
use App\Models\Participant;
use App\Models\ResultParticipant;
use App\Models\ResultRouteFinalStage;
use App\Models\ResultRouteQualificationLikeFinal;
use App\Models\ResultRouteSemiFinalStage;
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

class Results implements FromCollection, WithTitle, WithCustomStartCell, WithHeadings, ShouldAutoSize, WithEvents, WithStyles
{
    public $event_id;
    public $type;
    public $gender;
    public $category;

    function __construct($event_id, $type, $gender, $category = '') {
        $this->event_id = $event_id;
        $this->type = $type;
        $this->gender = $gender;
        $this->category = $category;
    }
    public function styles(Worksheet $sheet)
    {
        if($this->type == 'Qualification'){
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
        if ($this->type == 'Qualification'){
            return 'A1';
        } else {
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

                    if($this->type != 'Qualification') {
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
                            case 'SemiFinal':
                                $count = ResultRouteFinalStage::count_route_in_final_stage($this->event_id);
                            case 'QualificationLikeFinal':
                                $count = ResultRouteQualificationLikeFinal::count_route_in_qualification_final($this->event_id);
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
                for($i = 0; $i <= $count; $i++){
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
            case 'QualificationLikeFinal':
                $qualification_like_final = [
                    'Место',
                    'Участник(Фамилия Имя)',
                    'Сумма TOP',
                    'Сумма попыток на TOP',
                    'Сумма ZONE',
                    'Сумма попыток на ZONE',
                ];
                $count = ResultRouteQualificationLikeFinal::count_route_in_qualification_final($this->event_id);
                for($i = 1; $i <= $count; $i++){
                    $qualification_like_final[] = 'TOP';
                    $qualification_like_final[] = 'Попытки на TOP';
                    $qualification_like_final[] = 'ZONE';
                    $qualification_like_final[] = 'Попытки на ZONE';
                }
                return $qualification_like_final;
            case 'Qualification':
                $qualification = [
                    'Место',
                    'Участник(Фамилия Имя)',
                    'Баллы',
                    'Сет',
                    'Кол-во пройденных трасс',
                    'Кол-во FLASH',
                    'Кол-во REDPOINT'
                ];
                $count = Event::find($this->event_id)->count_routes;
                for($i = 1; $i <= $count; $i++){
                    $qualification[] = 'Трасса '.$i;
                }
                return $qualification;
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
        if($this->type == 'QualificationLikeFinal'){
            return self::get_final('result_qualification_like_final');
        }
        if($this->type == 'Qualification'){
            return self::get_qualification();
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
        return trans_choice('somewords.'.$this->type, 10).
            ' [ '.$category.' ][ '.trans_choice('somewords.'.$this->gender, 10).']';
    }


    public function get_qualification(){
        $users = User::query()
            ->leftJoin('participants', 'users.id', '=', 'participants.user_id')
            ->where('participants.event_id', '=', $this->event_id)
            ->where('participants.category_id', '=', $this->category->id)
            ->select(
                'users.id',
                'participants.user_place',
                'users.middlename',
                'participants.points',
                'participants.number_set',
            )
            ->where('gender', '=', $this->gender)->get()->sortBy('user_place')->toArray();
        foreach ($users as $index => $user){
            $qualification_result = ResultParticipant::where('event_id', '=', $this->event_id)->where('user_id', '=', $user['id'])->get();
            $amount_passed_flash = ResultParticipant::where('event_id', '=', $this->event_id)->where('user_id', '=', $user['id'])->where('attempt', 1)->get()->count();
            $amount_passed_redpoint = ResultParticipant::where('event_id', '=', $this->event_id)->where('user_id', '=', $user['id'])->where('attempt', 2)->get()->count();
            $amount_passed_routes = $amount_passed_flash+$amount_passed_redpoint;
            $place = Participant::get_places_participant_in_qualification($this->event_id, $user['id'], $this->gender, $this->category->id, true);
            $users[$index]['user_place'] = $place;
            $users[$index]['amount_passed_routes'] = $amount_passed_routes;
            $users[$index]['amount_passed_flash'] = $amount_passed_flash;
            $users[$index]['amount_passed_redpoint'] = $amount_passed_redpoint;
            foreach ($qualification_result as $result){
                switch ($result->attempt){
                    case 1:
                        $attempt = 'F';
                        break;
                    case 2:
                        $attempt = 'R';

                        break;
                    case 0:
                        $attempt = '-';
                }
                $users[$index]['route_result_'.$result->route_id] = $attempt;
            }
            $users[$index] = collect($users[$index])->except('id');
        }
        $users_need_sorted = collect($users)->toArray();
        usort($users_need_sorted, function ($a, $b) {
            return $a['user_place'] <=> $b['user_place'];
        });
        return collect($users_need_sorted);

    }

    public function get_final($table){
        if($table === "result_final_stage" || $table === "result_qualification_like_final"){
            $users = User::query()
                ->leftJoin($table, 'users.id', '=', $table.'.user_id')
                ->where($table.'.event_id', '=', $this->event_id)
                ->select(
                    $table.'.place',
                    $table.'.category_id',
                    'users.id',
                    'users.middlename',
                    $table.'.amount_top',
                    $table.'.amount_try_top',
                    $table.'.amount_zone',
                    $table.'.amount_try_zone',
                )
                ->where('gender', '=', $this->gender)
                ->where('category_id', '=', $this->category->id)
                ->get()
                ->sortBy('place')
                ->toArray();
        } else {
            $users = User::query()
                ->leftJoin($table, 'users.id', '=', $table.'.user_id')
                ->where($table.'.event_id', '=', $this->event_id)
                ->select(
                    $table.'.place',
                    'users.id',
                    'users.middlename',
                    $table.'.amount_top',
                    $table.'.amount_try_top',
                    $table.'.amount_zone',
                    $table.'.amount_try_zone',
                )
                ->where('gender', '=', $this->gender)
                ->get()
                ->sortBy('place')
                ->toArray();
        }
        foreach ($users as $index => $user){
            if($table == 'result_final_stage'){
                $final_result = ResultRouteFinalStage::where('event_id', '=', $this->event_id)->where('user_id', '=', $user['id'])->get();
            } else {
                if($table === "result_qualification_like_final"){
                    $final_result = ResultRouteQualificationLikeFinal::where('event_id', '=', $this->event_id)->where('user_id', '=', $user['id'])->get();
                } else {
                    $final_result = ResultRouteSemiFinalStage::where('event_id', '=', $this->event_id)->where('user_id', '=', $user['id'])->get();
                }
            }
            foreach ($final_result as $result){
                $users[$index]['amount_top_'.$result->final_route_id] = $result->amount_top;
                $users[$index]['amount_try_top_'.$result->final_route_id] = $result->amount_try_top;
                $users[$index]['amount_zone_'.$result->final_route_id] = $result->amount_zone;
                $users[$index]['amount_try_zone_'.$result->final_route_id] = $result->amount_try_zone;
            }
            $users[$index] = collect($users[$index])->except('id', 'category_id');
        }
        return collect($users);
    }
}
