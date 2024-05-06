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

class ExportCardParticipant implements WithTitle, WithCustomStartCell, ShouldAutoSize, WithEvents, WithStyles, WithDrawings
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

    public function drawings()
    {
        $event = Event::find($this->event_id);
        $qr_path = Helpers::save_qr_code($event);
        $drawing = new Drawing();
        $drawing->setPath($qr_path);
        $drawing->setHeight(70);
        $drawing->setCoordinates('S1');
        return $drawing;
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
                $sheet->getDelegate()->getRowDimension('1')->setRowHeight(53);
                $sheet->mergeCells('A1:D1');
                $sheet->setCellValue('A1', $this->title_event());
                $sheet->setCellValue('P1', 'Внести результат');
                $sheet->mergeCells('P1:R1');
                $sheet->mergeCells('S1:T1');
                $sheet->getStyle('A1')->applyFromArray($style);
                $sheet->getStyle('P1')->applyFromArray($style);
                $sheet->getStyle('S1')->applyFromArray($style);
                $sheet->mergeCells('E1:G1');
                $sheet->setCellValue('E1', $this->title());
                $sheet->getStyle('E1')->applyFromArray($style);
                $sheet->mergeCells('I1:K1');
                $sheet->setCellValue('I1', $this->title_middlename());
                $sheet->getStyle('I1')->applyFromArray($style);
                $sheet->mergeCells('L1:O1');
                $sheet->getStyle('L1')->applyFromArray($style);
                $count_routes = Grades::where('event_id', $this->event_id)->first()->count_routes;
                $max_part = ExportHelpers::countCell($count_routes);
                $title_array = ExportHelpers::prepare(2, $count_routes, $max_part);
                $title_array_flash_rp = ExportHelpers::prepare(3, $count_routes, $max_part);
//                $empty_cell =  ExportHelpers::generate_excel_title_array(20, $max_part * 3 + 1);
                $ready_title = ExportHelpers::merge_arrays($title_array);
                $ready_title_flash_rp = ExportHelpers::merge_arrays($title_array_flash_rp);
                $routes = 1;
                $cell_height = 2;
//                dd($title_array, $title_array_flash_rp, $ready_title, $ready_title_flash_rp);
                foreach($ready_title as $title){
                    $set_cell_value = explode(':', $title)[0];
                    $sheet->mergeCells($title);
                    $sheet->setCellValue($set_cell_value, ''.$routes);
                    $sheet->getStyle($set_cell_value)->applyFromArray($style);
                    $sheet->getDelegate()->getRowDimension($cell_height)->setRowHeight(15);
                    $routes++;
                    $cell_height++;
                }
//                foreach($empty_cell as $title){
//                    $set_cell_value = explode(':', $title)[0];
//                    $sheet->setCellValue($set_cell_value, '');
//                    $sheet->getStyle($set_cell_value)->applyFromArray($style);
//                }
                foreach($ready_title_flash_rp as $title){
                    $set_cell_value_1 = explode(':', $title)[0];
                    $set_cell_value_2 = explode(':', $title)[1];
                    $sheet->setCellValue($set_cell_value_1, 'F');
                    $sheet->getStyle($set_cell_value_1)->applyFromArray($style);
                    $sheet->setCellValue($set_cell_value_2, 'RP');
                    $sheet->getStyle($set_cell_value_2)->applyFromArray($style);
                }
            },

        ];

    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Карточка';
    }
    /**
     * @return string
     */
    public function title_event(): string
    {
        return Event::find($this->event_id)->title;
    }
    /**
     * @return string
     */
    public function title_middlename(): string
    {
        return 'Фамилия:';
    }
}
