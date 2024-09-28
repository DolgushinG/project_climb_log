<?php

namespace App\Exports\Sheets;

use App\Models\Event;
use App\Models\EventAndCoefficientRoute;
use App\Models\Format;
use App\Models\Grades;
use App\Models\ResultFinalStage;
use App\Models\ResultFranceSystemQualification;
use App\Models\ResultQualificationClassic;
use App\Models\ResultRouteQualificationClassic;
use App\Models\ResultRouteFinalStage;
use App\Models\ResultRouteFranceSystemQualification;
use App\Models\ResultRouteSemiFinalStage;
use App\Models\ResultSemiFinalStage;
use App\Models\Route;
use App\Models\RoutesOutdoor;
use App\Models\Set;
use App\Models\User;
use DateTime;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Sheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
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

    function getExcelCell($index, $row = 1, $digit=true)
    {
        $column = '';
        $index++; // Увеличиваем на 1, так как индексация в Excel начинается с 1, а не с 0

        while ($index > 0) {
            $mod = ($index - 1) % 26;
            $column = chr(65 + $mod) . $column;
            $index = intval(($index - 1) / 26);
        }
        if($digit){
            return $column . $row;
        }
        return $column;
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
            case 'Full':
                return 'A7';
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
                            1 => ["H","K"],
                            2 => ["L", "O"],
                            3 => ["P", "S"],
                            4 => ["T", "W"],
                            5 => ["X", "AA"],
                            6 => ["AB", "AE"],
                            7 => ["AF", "AI"],
                            8 => ["AJ", "AM"],
                            9 => ["AN", "AQ"],
                            10 => ["AR", "AU"],
                            11 => ["AV", "AY"],
                            12 => ["AZ", "BC"],
                            13 => ["BD", "BG"],
                            14 => ["BH", "BK"],
                            15 => ["BL", "BO"],
                            16 => ["BP", "BS"],
                            17 => ["BT", "BW"],
                            18 => ["BX", "CA"],
                            19 => ["CB", "CE"],
                            20 => ["CF", "CH"],
                        ];
                        $cell_title = [
                            1 => "J",
                            2 => "N",
                            3 => "R",
                            4 => "V",
                            5 => "Z",
                            6 => "AD",
                            7 => "AH",
                            8 => "AL",
                            9 => "AP",
                            10 => "AT",
                            11 => "AX",
                            12 => "BB",
                            13 => "BF",
                            14 => "BJ",
                            15 => "BN",
                            16 => "BR",
                            17 => "BV",
                            18 => "BZ",
                            19 => "CD",
                            20 => "CH",
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

                    if($this->type == 'Full'){
                        $styleTitle = [
                            'font' => [
                                'size' => 14,
                            ],
                            'alignment' => [
                                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                                'wrapText' => true,
                            ],
                        ];
                        $styleSubTitle = [
                            'font' => [
                                'size' => 10,
                            ],
                            'alignment' => [
                                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                            ],
                        ];
                        $styleArray = [
                            'font' => [
                                'size' => 10,
                            ],
                            'alignment' => [
                                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                                'wrapText' => true,
                            ],
                            'borders' => [
                                'outline' => [
                                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                    'color' => ['argb' => '000000'],
                                ],
                            ],
                        ];
                        $styleText = [
                            'font' => [
                                'size' => 9,
                            ],
                            'alignment' => [
                                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                                'wrapText' => false,
                            ],
                            'borders' => [
                                'outline' => [
                                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                    'color' => ['argb' => '000000'],
                                ],
                            ],
                        ];
                        $stylePricePlaces = [
                            'font' => [
                                'size' => 10,
                                'bold' => true
                            ],
                            'alignment' => [
                                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                                'wrapText' => true,
                            ],
                            'borders' => [
                                'outline' => [
                                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                    'color' => ['argb' => '000000'],
                                ],
                            ],
                        ];

                        $styleArray_headers = [
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => [
                                    'argb' => 'D3D3D3', // Серый цвет (Light Gray)
                                ],
                            ],
                            'alignment' => [
                                'horizontal' => Alignment::HORIZONTAL_CENTER, // Горизонтальное выравнивание
                                'vertical' => Alignment::VERTICAL_CENTER,
                                'wrapText' => true,// Вертикальное выравнивание
                            ],
                            'borders' => [
                                'allBorders' => [
                                    'borderStyle' => Border::BORDER_THIN, // Тонкие границы
                                    'color' => ['argb' => '000000'],      // Цвет границ (черный)
                                ],
                            ],

                        ];
                        $event = Event::find($this->event_id);
                        $routes = Grades::where('event_id', $this->event_id)->first()->count_routes;
                        $count_cell_needs = 5;
                        $amount_semi_final = 0;
                        if($event->amount_routes_in_semifinal){
                            $amount_semi_final = $event->amount_routes_in_semifinal * 2 + 4;
                        }
                        $amount_final = 0;
                        if($event->amount_routes_in_final){
                            $amount_final = $event->amount_routes_in_final * 2 + 4;
                        }
                        $sum = $count_cell_needs + ($routes * 2 + 4) + $amount_semi_final + $amount_final;
                        $amount_sum_row_1 = $this->getExcelCell($sum);
                        $cell_sport_category_done = $this->getExcelCell($sum, 6);
                        $cell_sport_category_done_2 = $this->getExcelCell($sum, 7);
                        $cell_with_title_route = $this->getExcelCell($sum-1, 7);
                        $amount_sum_row_2 = $this->getExcelCell($sum, 2);
                        $amount_sum_row_3 = $this->getExcelCell($sum, 3);
                        $amount_sum_row_4 = $this->getExcelCell($sum, 4);
                        $sheet->getStyle('A1')->getAlignment()->setIndent(1);
                        $for_header_qualification = $count_cell_needs + $amount_final + 4;
                        $for_header_final = $count_cell_needs + ($amount_final * 2) + 4;
                        $count_participant_record = self::count_participant_category('result_france_system_qualification') + 8;
                        $sheet->mergeCells($this->getExcelCell($for_header_qualification, 6).':'.$this->getExcelCell($for_header_qualification + 3, 6));
                        $sheet->mergeCells($this->getExcelCell($for_header_final, 6).':'.$this->getExcelCell($for_header_final + 3, 6));
                        $sheet->mergeCells('A6:A7');
                        $sheet->mergeCells('B'.$count_participant_record.':E'.$count_participant_record);
                        $sheet->mergeCells('B'.($count_participant_record + 2).':E'.($count_participant_record + 2));
                        $sheet->mergeCells('B'.($count_participant_record + 4).':E'.($count_participant_record + 4));
                        $sheet->mergeCells('A5:E5');
                        $sheet->mergeCells('B6:B7');
                        $sheet->mergeCells('C6:C7');
                        $sheet->mergeCells('D6:D7');
                        $sheet->mergeCells('E6:E7');
                        $sheet->mergeCells($cell_sport_category_done.':'.$cell_sport_category_done_2);
                        $sheet->mergeCells('A1:'.$amount_sum_row_1);
                        $sheet->getStyle('A6:A7')->applyFromArray($styleArray);
                        $sheet->getStyle('A5:E5')->applyFromArray($styleText);  # 'Зам. гл. судьи по виду - '
                        $sheet->getStyle('B6:B7')->applyFromArray($styleArray);
                        $sheet->getStyle('C6:C7')->applyFromArray($styleArray);
                        $sheet->getStyle('D6:D7')->applyFromArray($styleArray);
                        $sheet->getStyle('E6:E7')->applyFromArray($styleArray);
                        $sheet->getStyle('F7:'.$cell_sport_category_done_2)->applyFromArray($styleArray);
                        $sheet->getStyle('A6:'.$cell_sport_category_done)->applyFromArray($styleArray_headers);
                        $sheet->getStyle('A7:'.$cell_with_title_route)->applyFromArray($styleArray_headers);
                        $sheet->getStyle($cell_sport_category_done.':'.$cell_sport_category_done_2)->applyFromArray($styleArray_headers);
                        $final_cell_records = self::getExcelCell($sum, $count_participant_record);
                        $final_cell_records_8 = self::getExcelCell($sum, 8);
                        $final_cell_records_9 = self::getExcelCell($sum, 9);
                        $final_cell_records_10 = self::getExcelCell($sum, 10);
                        $sheet->getStyle('A11:'.$final_cell_records)->applyFromArray($styleArray);
                        $sheet->getStyle('A8:'.$final_cell_records_8)->applyFromArray($stylePricePlaces);
                        $sheet->getStyle('A9:'.$final_cell_records_9)->applyFromArray($stylePricePlaces);
                        $sheet->getStyle('A10:'.$final_cell_records_10)->applyFromArray($stylePricePlaces);
                        $sheet->getStyle('B'.$count_participant_record)->applyFromArray($stylePricePlaces);

                        $sheet->getStyle('A1:'.$amount_sum_row_1)->applyFromArray($styleTitle);
                        # Ширина
                        # Высота
                        $sheet->getRowDimension('1')->setRowHeight(40);
                        $sheet->getRowDimension('5')->setRowHeight(30);
                        $sheet->getRowDimension($count_participant_record+2)->setRowHeight(30);
                        $sheet->getRowDimension($count_participant_record+4)->setRowHeight(30);

                        $sheet->mergeCells('A2:'.$amount_sum_row_2);
                        $data_time_year = new DateTime($event->start_date);
                        $sheet->setCellValue('A2', $event->address. '                       '.$data_time_year->format('d.m.Y'));
                        $sheet->getStyle('A2:'.$amount_sum_row_2)->applyFromArray($styleSubTitle);
                        $sheet->mergeCells('A3:'.$amount_sum_row_3);
                        $sheet->getStyle('A3:'.$amount_sum_row_3)->applyFromArray($styleTitle);
                        $sheet->mergeCells('A4:'.$amount_sum_row_4);
                        $sheet->setCellValue('A4', $this->category->category);
                        $sheet->getStyle('A4:'.$amount_sum_row_4)->applyFromArray($styleTitle);

                        $sheet->setCellValue('A1', $event->title);
                        $sheet->setCellValue('A'.$count_participant_record, '*');
                        $sheet->setCellValue('B'.$count_participant_record, 'согласно действующим нормам, требованиям и условиям ЕВСК 1 и 2 сп.р. выполняется с 12 лет');
                        $sheet->setCellValue('B'.$count_participant_record + 2, 'Гл.судья _________________________');
                        $sheet->setCellValue('B'.$count_participant_record + 4, 'Гл.секретарь _____________________');
                        $sheet->setCellValue('A5', 'Зам. гл. судьи по виду - ________________');
                        $sheet->setCellValue('A6',  'Место');
                        $sheet->setCellValue('B6',  'Фамилия, Имя');
                        $sheet->setCellValue('C6',  'Г.р');
                        $sheet->setCellValue('D6',  'Разряд');
                        $sheet->setCellValue('E6',  'Команда');
                        $sheet->setCellValue($cell_sport_category_done, 'Вып.разряд');
                        $sheet->setCellValue('A4', 'ИТОГОВЫЙ ПРОТОКОЛ РЕЗУЛЬТАТОВ');
                        $sheet->setCellValue($this->getExcelCell($for_header_final, 6) , 'Финал');
                        $sheet->setCellValue($this->getExcelCell($for_header_qualification, 6) , 'Квалификация');

                    }
                },
            ];
    }

    public function count_participant_category($table)
    {
        return User::query()
            ->leftJoin($table, 'users.id', '=', $table.'.user_id')
            ->where($table.'.event_id', '=', $this->event_id)
            ->where($table.'.gender', '=', $this->gender)
            ->where($table.'.category_id', '=', $this->category->id)->get()->count();
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
                ];
                $event = Event::find($this->event_id);
                if ($event->is_need_sport_category) {
                    $france_system_qualification[] = 'Разряд';
                }
                $france_system_qualification = array_merge($france_system_qualification, [
                    'Сумма TOP',
                    'Сумма попыток на TOP',
                    'Сумма ZONE',
                    'Сумма попыток на ZONE',
                ]);

                $count = ResultRouteFranceSystemQualification::count_route_in_qualification_final($this->event_id);
                for($i = 1; $i <= $count; $i++){
                    $france_system_qualification[] = 'TOP';
                    $france_system_qualification[] = 'Попытки на TOP';
                    $france_system_qualification[] = 'ZONE';
                    $france_system_qualification[] = 'Попытки на ZONE';
                }
                return $france_system_qualification;
            case 'Full':
                $france_system_qualification = [
                    'Место',
                    'Фамилия, Имя',
                    'Г.р',
                    'Разряд',
                    'Команда',
                ];
                $count = ResultRouteFranceSystemQualification::count_route_in_qualification_final($this->event_id);
                for($i = 1; $i <= $count; $i++){
                    $france_system_qualification[] = 'T'.$i;
                    $france_system_qualification[] = 'З'.$i;
                }
                $france_system_qualification = array_merge($france_system_qualification, [
                    'T',
                    'З',
                    'ПТ',
                    'ПЗ',
                ]);
                $event = Event::find($this->event_id);
                if($event->is_semifinal){
                    $count = $event->amount_routes_in_semifinal;
                    for($i = 1; $i <= $count; $i++){
                        $france_system_qualification[] = 'T'.$i;
                        $france_system_qualification[] = 'З'.$i;
                    }
                    $france_system_qualification = array_merge($france_system_qualification, [
                        'T',
                        'З',
                        'ПТ',
                        'ПЗ',
                    ]);
                }
                $final_final_result = ResultRouteFinalStage::where('event_id', '=', $this->event_id)->first();
                if($final_final_result){
                    $count = $event->amount_routes_in_final;
                    for($i = 1; $i <= $count; $i++){
                        $france_system_qualification[] = 'T'.$i;
                        $france_system_qualification[] = 'З'.$i;
                    }
                    $france_system_qualification = array_merge($france_system_qualification, [
                        'T',
                        'З',
                        'ПТ',
                        'ПЗ',
                    ]);
                }
                return $france_system_qualification;
            case 'Qualification':
                $event = Event::find($this->event_id);
                $qualification[] = 'Место';
                $qualification[] = 'Участник(Фамилия Имя)';
                $qualification[] = 'Разряд';
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
                    'Баллы со всех этапов',
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
        if($this->type == 'Full'){
            return self::get_full_result('result_france_system_qualification');
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
        if($this->type == 'Full'){
            return $category;
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
                'result_qualification_classic.sport_category',
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
        $users_for_filter = ResultQualificationClassic::where('event_id', $this->event_id)->pluck('user_id')->toArray();
        foreach ($users as $index => $user) {
            if ($index == 'empty_row') {
                $count = Grades::where('event_id', $this->event_id)->first()->count_routes;
                $users[$index]['user_place'] = '';
                $users[$index]['number_set_id'] = '';
                $users[$index]['sport_category'] = '';
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
                        $users[$index]['route_result_' . $i] = $coefficient[$i] ?? '';
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
                $query = $event->type_event ? RoutesOutdoor::where('event_id', $this->event_id) : Route::where('event_id', $this->event_id);
                $routes_event_value = $query->pluck('value', 'route_id')->toArray();
                if($event->is_zone_show) {
                    $routes_event_zone = $query->pluck('zone', 'route_id')->toArray();
                }
                if($event->is_flash_value) {
                    $routes_event_flash_value = $query->pluck('flash_value', 'route_id')->toArray();
                }
                foreach ($qualification_result as $result){
                    switch ($result->attempt){
                        case ResultRouteQualificationClassic::STATUS_PASSED_FLASH:
                            if($event->mode == Format::N_ROUTE || $event->mode == Format::ALL_ROUTE_WITH_POINTS){
                                $attempt = $routes_event_value[$result->route_id] + $routes_event_flash_value[$result->route_id] ?? 'не найдено';
                            } else {
                                $attempt = 'F';
                            }
                            break;
                        case ResultRouteQualificationClassic::STATUS_PASSED_REDPOINT:
                            if($event->mode == Format::N_ROUTE || $event->mode == Format::ALL_ROUTE_WITH_POINTS ){
                                $attempt = $routes_event_value[$result->route_id] ?? 'не найдено';
                            } else {
                                $attempt = 'R';
                            }
                            break;
                        case ResultRouteQualificationClassic::STATUS_ZONE:
                            if($event->mode == Format::N_ROUTE || $event->mode == Format::ALL_ROUTE_WITH_POINTS && $event->is_zone_show){
                                $attempt = $routes_event_zone[$result->route_id] ?? '-';
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
        $column_category_id = 'global_category_id';
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
                'result_qualification_classic.last_points_after_merged',
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
        foreach ($users as $index => $user) {
            if ($index == 'empty_row') {
                $users[$index]['user_global_place'] = '';
            } else {
                $new_last_points_after_merged = str_replace("null", 'Нет участвовал(а)', $users[$index]['last_points_after_merged']);
                $users[$index]['last_points_after_merged'] = $new_last_points_after_merged;
                $users[$index] = collect($users[$index])->except('id', 'owner_id');
            }

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
        $result_team = [];
        foreach ($teams as $team){
            $result_team_cache = ResultQualificationClassic::get_list_team_and_points_participant($event->id, $team);
            $result_team[$team] = $result_team_cache;
        }
        $result_team = ResultQualificationClassic::sorted_team_points($result_team);
        return collect($result_team);
    }

    public function get_final($table){
        $event = Event::find($this->event_id);

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
                $table.'.amount_try_zone'
            )
            ->when($table === 'result_france_system_qualification', function ($query) use ($table) {
                $query->addSelect($table.'.sport_category');
            })
            ->where($table.'.gender', '=', $this->gender);
        if($this->category){
            $users = $users->where('category_id', '=', $this->category->id);
        }

        $users = $users->get()->sortBy('place')->toArray();
        foreach ($users as $index => $user){
            if($table == 'result_final_stage'){
                $final_result = ResultRouteFinalStage::where('event_id', '=', $this->event_id)->where('user_id', '=', $user['id'])->get();
                $max_routes =  $event->amount_routes_in_final;
            }
            if($table === "result_france_system_qualification"){
                $final_result = ResultRouteFranceSystemQualification::where('event_id', '=', $this->event_id)->where('user_id', '=', $user['id'])->get();
                $max_routes = Grades::where('event_id', $event->id)->first()->count_routes ?? 0;
            }
            if($table === "result_semifinal_stage"){
                $final_result = ResultRouteSemiFinalStage::where('event_id', '=', $this->event_id)->where('user_id', '=', $user['id'])->get();
                $max_routes =  $event->amount_routes_in_semifinal;
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
                for ($i = 1; $i <= $max_routes; $i++) {
                    if (!isset($users[$index]['amount_top_' . $i])) {
                        $users[$index]['amount_top_' . $i] = 0;
                        $users[$index]['amount_try_top_' . $i] = 0;
                    }
                    if (!isset($users[$index]['amount_zone_' . $i])) {
                        $users[$index]['amount_zone_' . $i] = 0;
                        $users[$index]['amount_try_zone_' . $i] = 0;
                    }
                }

            }
            $except = $event->is_need_sport_category ? ['id', 'category_id'] : ['id', 'category_id', 'sport_category'];
            $users[$index] = collect($users[$index])->except($except);
        }
        dd($users);
        return collect($users);
    }

    public function get_full_result($table){
        $event = Event::find($this->event_id);
        $max_routes = Grades::where('event_id', $event->id)->first()->count_routes ?? 0;
        $max_routes_semifinal = $event->amount_routes_in_semifinal;
        $max_routes_final = $event->amount_routes_in_final;
        $users = User::query()
            ->leftJoin($table, 'users.id', '=', $table.'.user_id')
            ->where($table.'.event_id', '=', $this->event_id)
            ->select(
                $table.'.place',
                'users.id',
                'users.middlename',
                'users.birthday',
                'users.team',
                $table.'.category_id',
            )
            ->when($table === 'result_france_system_qualification', function ($query) use ($table) {
                $query->addSelect($table.'.sport_category');
            })
            ->where($table.'.gender', '=', $this->gender);
        if($this->category){
            $users = $users->where('category_id', '=', $this->category->id);
        }

        $users = $users->get()->sortBy('place')->toArray();
        $export = [];
        foreach ($users as $index => $user){
            $export[$index]['place'] = $user['place'];
            $export[$index]['middlename'] = $user['middlename'];
            $export[$index]['birthday'] = $user['birthday'];
            $export[$index]['sport_category'] = $user['sport_category'];
            $export[$index]['team'] = $user['team'];
            $final_result_sum = ResultFranceSystemQualification::where('event_id', '=', $this->event_id)->where('user_id', '=', $user['id'])->first();
            $final_final_result_sum = ResultFinalStage::where('event_id', '=', $this->event_id)->where('user_id', '=', $user['id'])->first();
            $final_final_result = ResultRouteFinalStage::where('event_id', '=', $this->event_id)->where('user_id', '=', $user['id'])->get();
            $is_final = ResultRouteFinalStage::where('event_id', '=', $this->event_id)->first();
            $is_semifinal = ResultRouteSemiFinalStage::where('event_id', '=', $this->event_id)->first();
            $final_semifinal_result_sum = ResultSemiFinalStage::where('event_id', '=', $this->event_id)->where('user_id', '=', $user['id'])->first();
            $final_semifinal_result = ResultRouteSemiFinalStage::where('event_id', '=', $this->event_id)->where('user_id', '=', $user['id'])->get();
            $result = ResultRouteFranceSystemQualification::where('event_id', $this->event_id)
                ->where('user_id', $user['id'])
                ->get()
                ->mapWithKeys(function ($item) {
                    return [$item['route_id'] => $item];
                })
                ->toArray();
            for($route = 1; $route <= $max_routes; $route++){
                if (!isset($result[$route]['amount_try_top'])) {
                    $export[$index]['amount_try_top_' . $route] = '-';
                }
                if (!isset($result[$route]['amount_try_zone'])) {
                    $export[$index]['amount_try_zone_' . $route] = '-';
                }
                if(isset($result[$route]['amount_try_zone']) && isset($result[$route]['amount_try_top'])){
                    $export[$index]['amount_try_top_'.$route] = $result[$route]['amount_try_top'];
                    $export[$index]['amount_try_zone_'.$route] = $result[$route]['amount_try_zone'];
                }
            }
            $export[$index]['amount_top'] = $final_result_sum->amount_top;
            $export[$index]['amount_zone'] = $final_result_sum->amount_zone;
            $export[$index]['amount_try_top'] = $final_result_sum->amount_try_top;
            $export[$index]['amount_try_zone'] = $final_result_sum->amount_try_zone;
            if($is_semifinal){
                for($route = 1; $route <= $max_routes_semifinal; $route++){
                    if (!isset($final_semifinal_result[$route]['amount_try_top'])) {
                        $export[$index]['semifinal_amount_try_top_' . $route] = '-';
                    }
                    if (!isset($final_semifinal_result[$route]['amount_try_zone'])) {
                        $export[$index]['semifinal_amount_try_zone_' . $route] = '-';
                    }
                    if(isset($final_semifinal_result[$route]['amount_try_zone']) && isset($final_semifinal_result[$route]['amount_try_top'])){
                        $export[$index]['semifinal_amount_try_top_'.$route] = $final_semifinal_result[$route]['amount_try_top'];
                        $export[$index]['semifinal_amount_try_zone_'.$route] = $final_semifinal_result[$route]['amount_try_zone'];
                    }
                }
                if($final_semifinal_result_sum) {
                    $export[$index]['semifinal_amount_top'] = $final_semifinal_result_sum->amount_top;
                    $export[$index]['semifinal_amount_zone'] = $final_semifinal_result_sum->amount_zone;
                    $export[$index]['semifinal_amount_try_top'] = $final_semifinal_result_sum->amount_try_top;
                    $export[$index]['semifinal_amount_try_zone'] = $final_semifinal_result_sum->amount_try_zone;
                }
            }
            if($is_final){
                for($route = 1; $route <= $max_routes_final; $route++){
                    if (!isset($final_final_result[$route]['amount_try_top'])) {
                        $export[$index]['final_amount_try_top_' . $route] = '-';
                    }
                    if (!isset($final_final_result[$route]['amount_try_zone'])) {
                        $export[$index]['final_amount_try_zone_' . $route] = '-';
                    }
                    if(isset($final_final_result[$route]['amount_try_top'])){
                        $export[$index]['final_amount_try_top_'.$route] = $final_final_result[$route]['amount_try_top'];
                    }
                    if(isset($final_final_result[$route]['amount_try_zone'])){
                        $export[$index]['final_amount_try_zone_'.$route] = $final_final_result[$route]['amount_try_zone'];
                    }
                }
                if($final_final_result_sum){
                    $export[$index]['final_amount_top'] = $final_final_result_sum->amount_top;
                    $export[$index]['final_amount_zone'] = $final_final_result_sum->amount_zone;
                    $export[$index]['final_amount_try_top'] = $final_final_result_sum->amount_try_top;
                    $export[$index]['final_amount_try_zone'] = $final_final_result_sum->amount_try_zone;
                }
            }
        }
        return collect($export);
    }
}
