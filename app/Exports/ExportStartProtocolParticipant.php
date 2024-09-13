<?php

namespace App\Exports;

use App\Helpers\Helpers;
use App\Models\Event;
use App\Models\ParticipantCategory;
use App\Models\Set;
use App\Models\User;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Sheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExportStartProtocolParticipant implements WithCustomStartCell, ShouldAutoSize, WithEvents, WithStyles
{

    public $event;
    public $category_id;

    function __construct($event, $category_id) {
        $this->event = Event::find($event);
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
                $sheet->mergeCells('A2:E2');
                $sheet->mergeCells('A2:C2');
                $sheet->mergeCells('I4:K4');
                $sheet->setCellValue('A5', '№');
                $sheet->setCellValue('B5', 'Сет');
                $sheet->setCellValue('C5', 'Фамилия Имя');
                $sheet->setCellValue('D5', 'Пол');
                $sheet->setCellValue('E5', 'Год рождения');
                $sheet->setCellValue('F5', 'Город');
                $sheet->setCellValue('G5', 'Тренер/команда');
                $sheet->setCellValue('H5', 'Разряд');
                $sheet->setCellValue('I5', 'ТБ (подпись)');
                $sheet->setCellValue('J5', 'Мед допуск (подпись)');
                $sheet->setCellValue('K5', 'Расшифровка (фамилия)');
                $sheet->setCellValue('L5', 'Группа');
                $sheet->setCellValue('M5', 'Возраст');
                $sheet->setCellValue('A1', $this->event_city());
                $sheet->setCellValue('I1', 'Дата проведения: '.$this->event_start_date());

                $sheet->setCellValue('A2', $this->event_title());
                $sheet->setCellValue('J2', 'Скалодром: '.$this->event_climbing_gym());
                $sheet->setCellValue('A3', 'Стартовый протокол');
                $sheet->setCellValue('J3', $this->get_category_name());
                $sheet->setCellValue('I4', 'Родитель (доверенное лицо)');

                $sheet->getStyle('A1:M10')->applyFromArray($style);
                $set_cell = 6;
                $participants = $this->get_participants();
                foreach($participants as $participant){
//                    $sheet->mergeCells('A'.$set_cell.':M'.$set_cell);
                    $sheet->setCellValue('A'.$set_cell, $participant['index']);
                    $sheet->setCellValue('B'.$set_cell, $participant['set']);
                    $sheet->setCellValue('C'.$set_cell, $participant['middlename']);
                    $sheet->setCellValue('D'.$set_cell, $participant['gender']);
                    $sheet->setCellValue('E'.$set_cell, $participant['birthday']);
                    $sheet->setCellValue('F'.$set_cell, $participant['city']);
                    $sheet->setCellValue('G'.$set_cell, $participant['team']);
                    $sheet->setCellValue('H'.$set_cell, $participant['sport_category']);
                    $sheet->setCellValue('I'.$set_cell, $participant['empty1']);
                    $sheet->setCellValue('J'.$set_cell, $participant['empty2']);
                    $sheet->setCellValue('K'.$set_cell, $participant['empty3']);
                    $sheet->setCellValue('L'.$set_cell, $participant['category']);
                    $sheet->setCellValue('M'.$set_cell, $participant['age']);
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
    public function get_category_name(): string
    {
        $category = ParticipantCategory::find($this->category_id);
        return $category->category;
    }
    /**
     * @return string
     */
    public function event_climbing_gym(): string
    {
        return $this->event->climbing_gym_name;
    }

    /**
     * @return string
     */
    public function event_start_date(): string
    {

        return $this->event->start_date;
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
        $users = User::query()
            ->leftJoin($table, 'users.id', '=', $table.'.user_id')
            ->where($table.'.event_id', '=', $this->event->id)
            ->select(
                'users.id',
                'users.middlename',
                'users.birthday',
                'users.city',
                'users.team',
                'users.sport_category',
                $table.'.category_id',
                $table.'.gender',
                $table.'.number_set_id',
            );
        if($this->category_id){
            $users = $users->where('category_id', '=', $this->category_id);
        }
        $export = [];
        $users = $users->get()->sortBy('place')->toArray();
        foreach ($users as $index => $user){
            $export[$index]['index'] = $index+1;
            $set = Set::find($user['number_set_id']);
            $export[$index]['set'] = $set->number_set ?? '';

            $export[$index]['middlename'] = implode(' ', array_reverse(explode(' ', $user['middlename'], 2)));
            $export[$index]['gender'] = $user['gender'] == 'male' ? 'М': 'Ж';
            $export[$index]['birthday'] = Helpers::get_year($user['birthday'] ?? '');
            $export[$index]['city'] = $user['city'] ?? '';
            $export[$index]['team'] = $user['team'] ?? '';
            $export[$index]['sport_category'] = $user['sport_category'] ?? '';
            $export[$index]['empty1'] = '';
            $export[$index]['empty2'] = '';
            $export[$index]['empty3'] = '';
            $export[$index]['category'] = $this->get_category_name();
            $age = Helpers::calculate_age($user['birthday']) ?? '';
            $export[$index]['age'] = $age;
        }
        asort($export);
        return $export;

    }

}
