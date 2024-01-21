<?php

namespace App\Exports\Sheets;

use App\Models\ResultRouteFinalStage;
use App\Models\User;
use Illuminate\Support\Facades\DB;
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
        return [
            // Style the first row as bold text.
            1    => ['font' => ['bold' => true]],
            2    => ['font' => ['bold' => true]],
        ];
    }

    public function startCell(): string
    {
        return 'A2';
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
                    ],
                ];
                $sheet->mergeCells('G1:J1');
                $sheet->setCellValue('I1', "Трасса 1");
                $sheet->getStyle('I1')->applyFromArray($style);

                $sheet->mergeCells('K1:N1');
                $sheet->setCellValue('K1', "Трасса 2");
                $sheet->getStyle('K1')->applyFromArray($style);

                $sheet->mergeCells('O1:R1');
                $sheet->setCellValue('O1', "Трасса 3");
                $sheet->getStyle('O1')->applyFromArray($style);

                $sheet->mergeCells('S1:V1');
                $sheet->setCellValue('S1', "Трасса 4");
                $sheet->getStyle('S1')->applyFromArray($style);

                $sheet->mergeCells('W1:Z1');
                $sheet->setCellValue('W1', "Трасса 5");
                $sheet->getStyle('W1')->applyFromArray($style);

            },
        ];
    }

    public function headings(): array
    {
        if($this->type == 'Final'){
           return [
               'Место',
               'Участник(Фамилия Имя)',
               'Сумма TOP',
               'Сумма попыток на TOP',
               'Сумма ZONE',
               'Сумма попыток на ZONE',
               'TOP',
               'Попытки на TOP',
               'ZONE',
               'Попытки на ZONE',
               'TOP',
               'Попытки на TOP',
               'ZONE',
               'Попытки на ZONE',
               'TOP',
               'Попытки на TOP',
               'ZONE',
               'Попытки на ZONE',
               'TOP',
               'Попытки на TOP',
               'ZONE',
               'Попытки на ZONE',
               'TOP',
               'Попытки на TOP',
               'ZONE',
               'Попытки на ZONE',
            ];
        }
        if($this->type == 'Qualification') {
            $res = [
                'Место',
                'Участник(Фамилия Имя)',
                'Баллы',
                'Сет'
            ];
            return $res;
        }
        return [];
    }


    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        if($this->type == 'Final'){
           $users = User::query()
               ->leftJoin('result_final_stage', 'users.id', '=', 'result_final_stage.user_id')
                ->where('result_final_stage.event_id', '=', $this->event_id)
                ->select(
                    'result_final_stage.place',
                    'users.id',
                    'users.middlename',
                    'result_final_stage.amount_top',
                    'result_final_stage.amount_try_top',
                    'result_final_stage.amount_zone',
                    'result_final_stage.amount_try_zone',
                )
                ->where('gender', '=', $this->gender)->get()->toArray();
           foreach ($users as $index => $user){
               $final_result = ResultRouteFinalStage::where('event_id', '=', $this->event_id)->where('user_id', '=', $user['id'])->get();
               foreach ($final_result as $index2 => $result){
                   $users[$index]['amount_top_'.$result->final_route_id] = $result->amount_top;
                   $users[$index]['amount_try_top_'.$result->final_route_id] = $result->amount_try_top;
                   $users[$index]['amount_zone_'.$result->final_route_id] = $result->amount_zone;
                   $users[$index]['amount_try_zone_'.$result->final_route_id] = $result->amount_try_zone;
               }
               $users[$index] = collect($users[$index])->except('id');
           }
           return collect($users);
        }
        if($this->type == 'Qualification'){
            return DB::table('users')
                ->leftJoin('participants', 'users.id', '=', 'participants.user_id')
                ->where('participants.event_id', '=', $this->event_id)
                ->where('users.category', '=', $this->category->id)
                ->select(
                    'participants.user_place',
                    'users.middlename',
                    'participants.points',
                    'participants.number_set',
                )
                ->where('gender', '=', $this->gender)->get()->sortBy('user_place');
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
        return  trans_choice('somewords.'.$this->type, 10).
            '('.$category.
            ' '.trans_choice('somewords.'.$this->gender, 10).')';
    }
}
