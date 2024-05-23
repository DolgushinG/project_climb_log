<?php

namespace App\Exports;

use App\Models\Event;
use App\Models\User;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Sheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExportFinalParticipant implements WithCustomStartCell, ShouldAutoSize, WithEvents, WithStyles
{

    public $event;

    function __construct($event) {
        $this->event = Event::find($event);
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
                $sheet->mergeCells('A1:E1');
                $sheet->mergeCells('A2:C2');
                $sheet->setCellValue('A2', 'Фамилия Имя');
                $sheet->setCellValue('A1', $this->event_title());
                $sheet->getStyle('A1:C1')->applyFromArray($style);
                $sheet->getStyle('A2:C2')->applyFromArray($style);
                $set_cell = 3;
                $participants = $this->get_participants();
                foreach($participants as $participant){
                    $sheet->mergeCells('A'.$set_cell.':C'.$set_cell);
                    $sheet->setCellValue('A'.$set_cell, $participant);
                    $sheet->getStyle($set_cell)->applyFromArray($style);
                    $set_cell++;
                }
            },

        ];

    }

    /**
     * @return string
     */
    public function event_title(): string
    {
        return $this->event->title;
    }

    /**
     * @return string
     */
    public function event_city(): string
    {
        return 'г.'.$this->event->city;
    }

    public function get_participants()
    {
        if($this->event->is_france_system_qualification){
            $table = 'result_france_system_qualification';
        } else {
            $table = 'result_qualification_classic';
        }
        return User::query()
            ->leftJoin($table, 'users.id', '=', $table.'.user_id')
            ->where($table.'.event_id', '=', $this->event->id)
            ->select(
                'users.middlename',
            )->pluck('middlename')->toArray();

    }

}
