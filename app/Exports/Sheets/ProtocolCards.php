<?php

namespace App\Exports\Sheets;

use App\Models\Event;
use App\Models\ResultFinalStage;
use App\Models\ResultSemiFinalStage;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Sheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProtocolCards implements WithTitle, WithCustomStartCell, ShouldAutoSize, WithEvents, WithStyles
{
    public $event;
    public $type;
    public $gender;
    public $category;

    function __construct($event_id, $type, $gender, $category = '') {
        $this->event = Event::find($event_id);
        $this->type = $type;
        $this->gender = $gender;
        $this->category = $category;
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
        return 'A6';
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
                    $sheet->getDelegate()->getRowDimension('1')->setRowHeight(30);
                    $sheet->getDelegate()->getRowDimension('2')->setRowHeight(30);
                    $sheet->getDelegate()->getRowDimension('3')->setRowHeight(30);
                    $sheet->getDelegate()->getRowDimension('4')->setRowHeight(30);
                    $sheet->getDelegate()->getRowDimension('5')->setRowHeight(30);
                    $sheet->mergeCells('A4:C4');
                    $sheet->mergeCells('I1:K1');
                    $sheet->mergeCells('D3:M3');
                    $sheet->mergeCells('A2:E2');
                    $sheet->setCellValue('A1', 'Протокол трассы №:');
                    $sheet->setCellValue('A4', 'Фамилия Имя');
                    $sheet->setCellValue('D3', 'Попытки');
                    $sheet->setCellValue('N4', 'ТОП');
                    $sheet->setCellValue('O4', 'БОНУС');
                    $sheet->setCellValue('A2', $this->event_title());
                    $sheet->setCellValue('I1', $this->event_date());
                    $sheet->setCellValue('I2', $this->event_city());

                    $sheet->getStyle('A1:O10')->applyFromArray($style);

                    $set_cell = 5;
                    if($this->type == 'semifinal'){
                        $amount_the_best_participant = $event->amount_the_best_participant ?? 10;
                        $merged_users = ResultSemiFinalStage::get_participant_semifinal($this->event, $amount_the_best_participant, $this->category);
                    }
                    if($this->type == 'final'){
                        $merged_users = ResultFinalStage::get_final_participant($this->event, $this->category);

                    }
                    $participants = array();
                    foreach ($merged_users as $user){
                        if($user['gender'] == $this->gender){
                            $participants[] = $user['middlename'];
                        }
                    }

                    foreach($participants as $participant){
                        $sheet->mergeCells('A'.$set_cell.':C'.$set_cell);
                        $sheet->setCellValue('A'.$set_cell, $participant);
                        $sheet->getStyle($set_cell)->applyFromArray($style);
                        $set_cell++;
                    }
                    $try = 10;
                    $tries_cell = ['D','E','F','G','H','I','J','K','L','M'];
                    for($i = 1; $i <= $try; $i++){
                        $sheet->setCellValue($tries_cell[$i-1].'4', ''.$i);
                        $sheet->getStyle($tries_cell[$i-1].'4')->applyFromArray($style);
                    }
                },
            ];
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

    /**
     * @return string
     */
    public function event_date(): string
    {
        return $this->event->start_date.' - '.$this->event->end_date;
    }

    /**
     * @return string
     */
    public function event_city(): string
    {
        return 'г.'.$this->event->city;
    }

    /**
     * @return string
     */
    public function event_title(): string
    {
        return $this->event->title;
    }
}
