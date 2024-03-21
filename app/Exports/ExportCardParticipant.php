<?php

namespace App\Exports;

use App\Exports\Sheets\Results;
use App\Models\Event;
use App\Models\Participant;
use App\Models\ParticipantCategory;
use App\Models\ResultParticipant;
use App\Models\ResultRouteFinalStage;
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
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ExportCardParticipant implements WithTitle, WithCustomStartCell, ShouldAutoSize, WithEvents, WithStyles
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
                ];
                $sheet->mergeCells('A1:C1');
                $sheet->setCellValue('A1', $this->title());
                $sheet->getStyle('A1')->applyFromArray($style);
                $sheet->mergeCells('E1:G1');
                $sheet->setCellValue('E1', $this->title_middlename());
                $sheet->getStyle('E1')->applyFromArray($style);
                $sheet->mergeCells('H1:K1');
                $sheet->getStyle('H1')->applyFromArray($style);
                $count_routes = Event::find($this->event_id)->count_routes;
                $max_part = ExportHelpers::countCell($count_routes);
                $part = 1;
                $start_cell = 2;
                $title_array = [];
                while($part <= $max_part){
                    $title_array[] = ExportHelpers::generate_excel_title_array(20, $start_cell);
                    $part++;
                    $start_cell=$start_cell+3;
                }
                $part_flash_rp = 1;
                $start_cell_flash_rp = 3;
                $title_array_flash_rp = [];
                while($part_flash_rp <= $max_part){
                    $title_array_flash_rp[] = ExportHelpers::generate_excel_title_array(20, $start_cell_flash_rp);
                    $part_flash_rp++;
                    $start_cell_flash_rp=$start_cell_flash_rp+3;
                }
                $ready_title = ExportHelpers::merge_arrays($title_array);
                $ready_title_flash_rp = ExportHelpers::merge_arrays($title_array_flash_rp);
                $routes = 1;
                foreach($ready_title as $title){
                    $set_cell_value = explode(':', $title)[0];
                    $sheet->mergeCells($title);
                    $sheet->setCellValue($set_cell_value, ''.$routes);
                    $sheet->getStyle($set_cell_value)->applyFromArray($style);
                    $routes++;
                }
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
    public function title_middlename(): string
    {
        return 'Фамилия:';
    }
}
