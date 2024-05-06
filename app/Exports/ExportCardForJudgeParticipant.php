<?php

namespace App\Exports;

use App\Helpers\Helpers;
use App\Models\Event;
use App\Models\Grades;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\WithDrawings;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Sheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExportCardForJudgeParticipant implements WithCustomStartCell, ShouldAutoSize, WithEvents, WithStyles
{

    public $event_id;

    function __construct($event_id) {
        $this->event_id = $event_id;
    }
    public function styles(Worksheet $sheet)
    {
        return [
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
                        'color' => array('rgb' => 'FF0000'),
                        'size'      =>  25,
                    ],
                    'bold' => true,
                ];
                $sheet->getDelegate()->getRowDimension('1')->setRowHeight(30);
                $sheet->getDelegate()->getRowDimension('2')->setRowHeight(30);
                $sheet->getDelegate()->getRowDimension('3')->setRowHeight(30);
                $sheet->mergeCells('A1:B1');
                $sheet->mergeCells('A2:B2');
                $sheet->mergeCells('A3:B3');
                $sheet->mergeCells('C1:D1');
                $sheet->mergeCells('C2:D2');
                $sheet->mergeCells('C3:F3');
                $sheet->mergeCells('G3:H3');
                $sheet->mergeCells('I1:J1');
                $sheet->mergeCells('K1:M1');
                $sheet->mergeCells('J2:M2');
                $sheet->mergeCells('B5:K5');
                $sheet->setCellValue('A1', '№ старт: ');
                $sheet->setCellValue('A2', 'Сет №: ');
                $sheet->setCellValue('A3', 'ФИО: ');
                $sheet->setCellValue('A6', '№ Трассы');
                $sheet->setCellValue('G4', 'подпись');
                $sheet->setCellValue('I1', 'Город: ');
                $sheet->setCellValue('I2', 'Группа: ');
                $sheet->setCellValue('I3', 'Пол: ');
                $sheet->setCellValue('L6', 'ТОП');
                $sheet->setCellValue('M6', 'БОНУС');
                $sheet->setCellValue('N6', '№ Трассы');
                $sheet->setCellValue('F5', 'Попытка');
                $sheet->getStyle('A1:N16')->applyFromArray($style);

                $count_routes = Grades::where('event_id', $this->event_id)->first()->count_routes;
                $set_cell = 7;
////                dd($title_array, $title_array_flash_rp, $ready_title, $ready_title_flash_rp);
                for($route = 1; $route <= $count_routes; $route++){
                    $sheet->setCellValue('A'.$set_cell, ''.$route);
                    $sheet->setCellValue('N'.$set_cell, ''.$route);
                    $sheet->getStyle($set_cell)->applyFromArray($style);
//                    $sheet->getDelegate()->getRowDimension($cell_height)->setRowHeight(15);
                    $set_cell++;
//                    $cell_height++;
                }
                $try = 10;
                $tries_cell = ['B','C','D','E','F','G','H','I','J','K'];
                for($i = 1; $i <= $try; $i++){
                    $sheet->setCellValue($tries_cell[$i-1].'6', ''.$i);
                    $sheet->getStyle($set_cell)->applyFromArray($style);
//                    $sheet->getDelegate()->getRowDimension($cell_height)->setRowHeight(15);
                    $set_cell++;
//                    $cell_height++;
                }
////                foreach($empty_cell as $title){
////                    $set_cell_value = explode(':', $title)[0];
////                    $sheet->setCellValue($set_cell_value, '');
////                    $sheet->getStyle($set_cell_value)->applyFromArray($style);
////                }
//                foreach($ready_title_flash_rp as $title){
//                    $set_cell_value_1 = explode(':', $title)[0];
//                    $set_cell_value_2 = explode(':', $title)[1];
//                    $sheet->setCellValue($set_cell_value_1, 'F');
//                    $sheet->getStyle($set_cell_value_1)->applyFromArray($style);
//                    $sheet->setCellValue($set_cell_value_2, 'RP');
//                    $sheet->getStyle($set_cell_value_2)->applyFromArray($style);
//                }
            },

        ];

    }
}
