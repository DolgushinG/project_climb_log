<?php

namespace App\Exports;

use App\Helpers\Helpers;
use App\Models\Event;
use App\Models\Grades;
use App\Models\ParticipantCategory;
use App\Models\ResultFinalStage;
use App\Models\ResultQualificationLikeFinal;
use App\Models\ResultSemiFinalStage;
use App\Models\Set;
use App\Models\User;
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

class ExportProtocolRouteParticipant implements WithCustomStartCell, ShouldAutoSize, WithEvents, WithStyles
{

    public $event;
    public $stage;
    public $gender;
    public $number_set_id;
    public $route_id;
    public $category_id;

    function __construct($event, $stage, $number_set_id, $gender, $category_id) {
        $this->event = Event::find($event);
        $this->stage = $stage;
        $this->number_set_id = $number_set_id;
        $this->gender = $gender;
        $this->category_id = $category_id;
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
                $sheet->getDelegate()->getRowDimension('1')->setRowHeight(30);
                $sheet->getDelegate()->getRowDimension('2')->setRowHeight(30);
                $sheet->getDelegate()->getRowDimension('3')->setRowHeight(30);
                $sheet->getDelegate()->getRowDimension('4')->setRowHeight(30);
                $sheet->getDelegate()->getRowDimension('5')->setRowHeight(30);
                $sheet->mergeCells('A5:C5');
                $sheet->mergeCells('I1:K1');
                $sheet->mergeCells('D4:M4');
                $sheet->mergeCells('A2:E2');
                $sheet->setCellValue('A1', 'Протокол трассы №:');
                $sheet->setCellValue('A5', 'Фамилия Имя');
                $sheet->setCellValue('D4', 'Попытки');
                $sheet->setCellValue('N5', 'ТОП');
                $sheet->setCellValue('O5', 'БОНУС');
                $sheet->setCellValue('A2', $this->event_title());
                $sheet->setCellValue('A3', 'Сет № ');
                $sheet->setCellValue('I1', $this->event_date());
                $sheet->setCellValue('I2', $this->event_city());

                $sheet->getStyle('A1:O10')->applyFromArray($style);

                $set_cell = 6;
                $participants = $this->get_participants();
                foreach($participants as $participant){
                    $sheet->mergeCells('A'.$set_cell.':C'.$set_cell);
                    $sheet->setCellValue('A'.$set_cell, $participant);
                    $sheet->getStyle($set_cell)->applyFromArray($style);
                    $set_cell++;
                }
                $try = 10;
                $tries_cell = ['D','E','F','G','H','I','J','K','L','M'];
                for($i = 1; $i <= $try; $i++){
                    $sheet->setCellValue($tries_cell[$i-1].'5', ''.$i);
                    $sheet->getStyle($tries_cell[$i-1].'5')->applyFromArray($style);
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

    public function get_participants()
    {
        switch ($this->stage){
            case 'qualification':
                $table = 'result_qualification_like_final';
                if($this->category_id == "not_category"){
                    return User::query()
                        ->leftJoin($table, 'users.id', '=', $table.'.user_id')
                        ->where($table.'.event_id', '=', $this->event->id)
                        ->where($table.'.number_set_id', '=', $this->number_set_id)
                        ->where($table.'.gender', '=', $this->gender)
                        ->select(
                            'users.middlename',
                        )->pluck('middlename')->toArray();
                } else {
                    return User::query()
                        ->leftJoin($table, 'users.id', '=', $table.'.user_id')
                        ->where($table.'.event_id', '=', $this->event->id)
                        ->where($table.'.category_id', '=', $this->category_id)
                        ->where($table.'.number_set_id', '=', $this->number_set_id)
                        ->where($table.'.gender', '=', $this->gender)
                        ->select(
                            'users.middlename',
                        )->pluck('middlename')->toArray();
                }
            case 'semifinal':
                if($this->category_id == 'not_category'){
                    $category = null;
                } else {
                    $category = ParticipantCategory::find($this->category_id);
                }
                $amount_the_best_participant = $event->amount_the_best_participant ?? 10;
                $merged_users = ResultSemiFinalStage::get_participant_semifinal($this->event, $amount_the_best_participant, $category);
                return $merged_users->pluck( 'middlename')->toArray();
            case 'final':
                if($this->category_id == 'not_category'){
                    $category = null;
                } else {
                    $category = ParticipantCategory::find($this->category_id);
                }
                $merged_users = ResultFinalStage::get_final_participant($this->event, $category);
                return $merged_users->pluck( 'middlename')->toArray();
        }

    }

}
