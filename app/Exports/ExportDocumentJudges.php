<?php

namespace App\Exports;

use App\Models\Event;
use App\Models\Staff;
use App\Models\User;
use DateTime;
use Encore\Admin\Facades\Admin;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Sheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExportDocumentJudges implements WithCustomStartCell, ShouldAutoSize, WithEvents, WithStyles, WithColumnWidths
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
    public function columnWidths(): array
    {
        return [
            'A' => 7, // Ширина для столбца A
            'B' => 25, // Ширина для столбца B
            'C' => 25, // Ширина для столбца C
            'D' => 25, // Ширина для столбца C
            'E' => 25, // Ширина для столбца C
        ];
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
                        'size'      =>  10,
                    ],
                    'bold' => true,
                ];
                $sheet->mergeCells('A5:B5');
                $sheet->setCellValue('A5', 'Справка');
                $sheet->setCellValue('A7', '№');
                $sheet->setCellValue('B7', 'Должность');
                $sheet->setCellValue('C7', 'Фамилия, Имя, Отчество');
                $sheet->setCellValue('D7', 'Судейская категория');
                $sheet->setCellValue('E7', 'Территория');
                $sheet->mergeCells('A6:E6');
                $sheet->setCellValue('A6', $this->event_title());
                $sheet->getStyle('C5')->applyFromArray($style);

                $set_cell = 8;
                $index = 1;
                $judges = $this->get_judges();
                $count = $judges->count();
                $sheet->getStyle('A1:E'.$count + 14)->applyFromArray($style);
                foreach($judges as $judge){
                    $sheet->setCellValue('A'.$set_cell, $index.'.');
                    $sheet->setCellValue('B'.$set_cell, $judge->type);
                    $sheet->setCellValue('C'.$set_cell, $judge->middlename);
                    $sheet->setCellValue('D'.$set_cell, $judge->judge_category);
                    $sheet->setCellValue('E'.$set_cell, $judge->area);
                    $sheet->getStyle($set_cell)->applyFromArray($style);
                    $set_cell++;
                    $index++;
                }


                $sheet->mergeCells('A'.($count + 9).':B'.($count + 9));
                $sheet->mergeCells('A'.($count + 12).':B'.($count + 12));
                $sheet->mergeCells('A'.($count + 10).':B'.($count + 10));
                $sheet->mergeCells('D'.($count + 10).':E'.($count + 10));
                $sheet->mergeCells('C'.($count + 14).':D'.($count + 14));
                $sheet->setCellValue('A'.$count + 9 , 'Председатель РОО');
                $sheet->setCellValue('A'.$count + 12 , 'Главный судья');
                $sheet->setCellValue('C'.$count + 14 , $this->event->city.' '.date("Y"));
            },

        ];

    }

    /**
     * @return string
     * @throws \Exception
     */
    public function event_title(): string
    {
        $start = new DateTime($this->event->start_date);
        $end = new DateTime($this->event->end_date);
        $months = [
            1 => 'января',
            2 => 'февраля',
            3 => 'марта',
            4 => 'апреля',
            5 => 'мая',
            6 => 'июня',
            7 => 'июля',
            8 => 'августа',
            9 => 'сентября',
            10 => 'октября',
            11 => 'ноября',
            12 => 'декабря',
        ];
        $startFormatted = $start->format('j');
        $endFormatted = $end->format('j');
        $month = $months[(int)$start->format('n')]; // Получаем название месяца на русском
        $year = $start->format('Y');
        return 'о составе судейской коллегии на'.PHP_EOL.$this->event->title.PHP_EOL.' (дисциплина-боулдеринг, код по ВРВС-0800011811Я)'.PHP_EOL."{$startFormatted}-{$endFormatted} {$month} {$year} г.";
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
