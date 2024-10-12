<?php

namespace App\Exports;

use App\Models\Event;
use App\Models\ParticipantCategory;
use App\Models\User;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Sheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExportListParticipant implements WithCustomStartCell, ShouldAutoSize, WithEvents, WithStyles, WithColumnWidths
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
    public function columnWidths(): array
    {
        return [
            'A' => 10, // Ширина для столбца A
            'B' => 25, // Ширина для столбца B
            'C' => 10, // Ширина для столбца C
            'D' => 15, // Ширина для столбца C
            'E' => 15, // Ширина для столбца C
            'H' => 15, // Ширина для столбца C
            'G' => 12, // Ширина для столбца C
            'F' => 30, // Ширина для столбца C
            'I' => 25, // Ширина для столбца C
            'J' => 30, // Ширина для столбца C
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
                        'size'      =>  25,
                    ],
                    'bold' => true,
                ];
                $sheet->mergeCells('A1:E1');
                $sheet->setCellValue('A2', 'Номер');
                $sheet->setCellValue('B2', 'Фамилия Имя');
                $sheet->setCellValue('C2', 'Пол');
                $sheet->setCellValue('D2', 'ДР');
                $sheet->setCellValue('E2', 'Город');
                $sheet->setCellValue('F2', 'Категория');
                $participants = $this->get_participants();
                if($this->event->is_need_pay_for_reg){
                    $sheet->setCellValue('G2', 'Сумма оплаты');
                    $sheet->setCellValue('H2', 'Оплата');
                    $sheet->setCellValue('I2', 'Мерч');
                    $sheet->setCellValue('J2', 'Контакт');
                    $sheet->getStyle('A1:J'.$participants->count() + 3)->applyFromArray($style);
                } else {
                    $sheet->setCellValue('G2', 'Контакт');
                    $sheet->getStyle('A1:G'.$participants->count() + 3)->applyFromArray($style);
                }
                $sheet->setCellValue('A1', $this->event_title());
                $set_cell = 3;
                $index = 1;
                foreach($participants as $participant){
                    if($participant->category_id){
                        $category_id = ParticipantCategory::find($participant->category_id)->category ?? '';
                    }
                    $sheet->setCellValue('A'.$set_cell, $index);
                    $sheet->setCellValue('B'.$set_cell, $participant->middlename ?? '' );
                    $sheet->setCellValue('C'.$set_cell, $participant->gender === 'male' ? 'Муж': 'Жен' ?? '' );
                    $sheet->setCellValue('D'.$set_cell, $participant->birthday ?? '' );
                    $sheet->setCellValue('E'.$set_cell, $participant->city ?? '' );
                    $sheet->setCellValue('F'.$set_cell, $category_id ?? '' );
                    if($this->event->is_need_pay_for_reg){
                        $sheet->setCellValue('G'.$set_cell, $participant->amount_start_price ?? '' );
                        $sheet->setCellValue('H'.$set_cell, $participant->is_paid === 1 ? 'Оплачено': '');
                        $sheet->setCellValue('I'.$set_cell, self::products_and_discounts_to_string($participant->products_and_discounts));
                        $sheet->setCellValue('J'.$set_cell, $participant->contact ?? $participant->email ?? '');
                    } else {
                        $sheet->setCellValue('G'.$set_cell, $participant->contact ?? $participant->email ?? '');
                    }
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
        return $this->event->title;
    }

    function products_and_discounts_to_string($array) {
        $result = '';
        if(!$array){
            return '';
        }
        $data = json_decode($array, true);
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                // Если это массив, соединяем его элементы через запятую
                if (!empty($value)) {
                    $result .= "[" . implode(', ', $value) . "]\n";
                }
            } elseif ($value !== null) {
                // Если это строка и не null
                $result .= "$value\n";
            }
        }

        return $result;
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
            ->get()
            ->sortByDesc('middlename');
    }

}
