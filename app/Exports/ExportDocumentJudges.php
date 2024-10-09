<?php

namespace App\Exports;

use App\Models\Event;
use App\Models\Staff;
use App\Models\User;
use Encore\Admin\Facades\Admin;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Sheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExportDocumentJudges implements WithCustomStartCell, ShouldAutoSize, WithEvents, WithStyles
{

    public $event;

    function __construct($event_id) {

        $this->event = Event::find($event_id);
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
        return 'A1';
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
                $sheet->setCellValue('A5', 'Справка');
                $sheet->setCellValue('A7', '№');
                $sheet->setCellValue('B7', 'Должность');
                $sheet->setCellValue('C7', 'Фамилия,Имя,Отчество');
                $sheet->setCellValue('D7', 'Судейская категория');
                $sheet->setCellValue('E7', 'Территория');
                $sheet->mergeCells('C6:E6');
                $sheet->setCellValue('C6', $this->event_title());
                $sheet->getStyle('C5')->applyFromArray($style);
                $sheet->getStyle('A5')->applyFromArray($style);
                $set_cell = 8;
                $index = 1;
                $judges = $this->get_judges();
                foreach($judges as $judge){
                    $sheet->setCellValue('A'.$set_cell, ($index+1).'.');
                    $sheet->setCellValue('B'.$set_cell, $judge->type);
                    $sheet->setCellValue('C'.$set_cell, $judge->middlename);
                    $sheet->setCellValue('D'.$set_cell, '-');
                    $sheet->setCellValue('E'.$set_cell, '-');
                    $sheet->getStyle($set_cell)->applyFromArray($style);
                    $set_cell++;
                    $index++;
                }
            },

        ];

    }

    /**
     * @return string
     */
    public function event_title(): string
    {
        return $this->event->title.' (дисциплина-боулдеринг, код по ВРВС-0800011811Я)'.$this->event->start_date.' '.$this->event->end_date;
    }

    /**
     * @return string
     */
    public function event_city(): string
    {
        return 'г.'.$this->event->city;
    }

    public function get_judges()
    {

        $staffJudges = Staff::where('owner_id', $this->event->owner_id)
            ->where(function ($query) {
                $query->where('type', 'like', '%судья%')
                    ->orWhere('type', 'like', '%судьи%')
                    ->orWhere('type', 'like', '%секретарь%');
            })
            ->get();
        $order = [
            'Главный судья',
            'Главный секретарь',
            'Зам. Главного судьи по виду',
            'Зам. Главного судьи по безопасности',
            'Судья на трассе'
        ];

        $sortedJudges = $staffJudges->sortBy(function ($item) use ($order) {
            return array_search($item->type, $order);
        });
        return $sortedJudges;
    }

}
