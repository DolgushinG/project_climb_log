<?php

namespace App\Exports\Sheets;

use App\Models\Event;
use App\Models\ResultParticipant;
use App\Models\ResultRouteFinalStage;
use App\Models\ResultRouteSemiFinalStage;
use App\Models\User;
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

class Results implements FromCollection, WithTitle, WithCustomStartCell, WithHeadings, ShouldAutoSize, WithEvents, WithStyles
{
    public $event_id;
    public $type;
    public $gender;
    public $category;

    function __construct($event_id, $type, $gender, $category = '') {
        $this->event_id = $event_id;
        $this->type = $type;
        $this->gender = $gender;
        $this->category = $category;
    }
    public function styles(Worksheet $sheet)
    {
        if($this->type == 'Qualification'){
            return [
                1    => ['font' => ['bold' => true]],
            ];
        } else {
            return [
                1    => ['font' => ['bold' => true]],
                2    => ['font' => ['bold' => true]],
            ];
        }

    }

    public function startCell(): string
    {
        if($this->type == 'Qualification'){
            return 'A1';
        } else {
            return 'A2';
        }

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
                        ],
                    ];
                    if($this->type != 'Qualification') {

                        $sheet->mergeCells('G1:J1');
                        $sheet->setCellValue('I1', "Трасса 1");
                        $sheet->getStyle('I1')->applyFromArray($style);

                        $sheet->mergeCells('K1:N1');
                        $sheet->setCellValue('K1', "Трасса 2");
                        $sheet->getStyle('K1')->applyFromArray($style);

                        $sheet->mergeCells('O1:R1');
                        $sheet->setCellValue('O1', "Трасса 3");
                        $sheet->getStyle('O1')->applyFromArray($style);

                        $sheet->mergeCells('S1:V1');
                        $sheet->setCellValue('S1', "Трасса 4");
                        $sheet->getStyle('S1')->applyFromArray($style);

                        $sheet->mergeCells('W1:Z1');
                        $sheet->setCellValue('W1', "Трасса 5");
                        $sheet->getStyle('W1')->applyFromArray($style);
                    }
                },
            ];
    }

    public function headings(): array
    {

        switch ($this->type){
            case 'Final':
                $final = [
                    'Место',
                    'Участник(Фамилия Имя)',
                    'Сумма TOP',
                    'Сумма попыток на TOP',
                    'Сумма ZONE',
                    'Сумма попыток на ZONE',
                ];
                $count = ResultRouteFinalStage::count_route_in_final_stage($this->event_id);
                for($i = 0; $i <= $count; $i++){
                    $final[] = 'TOP';
                    $final[] = 'Попытки на TOP';
                    $final[] = 'ZONE';
                    $final[] = 'Попытки на ZONE';
                }

                return $final;
            case 'SemiFinal':
                $final = [
                    'Место',
                    'Участник(Фамилия Имя)',
                    'Сумма TOP',
                    'Сумма попыток на TOP',
                    'Сумма ZONE',
                    'Сумма попыток на ZONE',
                ];
                $count = ResultRouteSemiFinalStage::count_route_in_semifinal_stage($this->event_id);
                for($i = 0; $i <= $count; $i++){
                    $final[] = 'TOP';
                    $final[] = 'Попытки на TOP';
                    $final[] = 'ZONE';
                    $final[] = 'Попытки на ZONE';
                }
                return $final;
            case 'Qualification':
                $qualification = [
                    'Место',
                    'Участник(Фамилия Имя)',
                    'Баллы',
                    'Сет'
                ];
                $count = Event::find($this->event_id)->count_routes;
                for($i = 1; $i <= $count; $i++){
                    $qualification[] = 'Трасса '.$i;
                }
                return $qualification;
            default:
                return [];
        }

    }


    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        if($this->type == 'SemiFinal'){
            return self::get_final('result_semifinal_stage');
        }
        if($this->type == 'Final'){
            return self::get_final('result_final_stage');
        }
        if($this->type == 'Qualification'){
            return self::get_qualification();
        }
        return collect([]);
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
        return  trans_choice('somewords.'.$this->type, 10).
            '('.$category.
            ' '.trans_choice('somewords.'.$this->gender, 10).')';
    }


    public function get_qualification(){
        $users = User::query()
            ->leftJoin('participants', 'users.id', '=', 'participants.user_id')
            ->where('participants.event_id', '=', $this->event_id)
            ->where('users.category', '=', $this->category->id)
            ->select(
                'users.id',
                'participants.user_place',
                'users.middlename',
                'participants.points',
                'participants.number_set',
            )
            ->where('gender', '=', $this->gender)->get()->sortBy('user_place')->toArray();
        foreach ($users as $index => $user){
            $qualification_result = ResultParticipant::where('event_id', '=', $this->event_id)->where('user_id', '=', $user['id'])->get();
            foreach ($qualification_result as $result){
                if ($result->attempt == 1){
                    $attempt = 'F';
                } else {
                    $attempt = 'R';
                }
                $users[$index]['route_result_'.$result->route_id] = $attempt;
            }
            $users[$index] = collect($users[$index])->except('id');
        }
        return collect($users);

    }

    public function get_final($table){
        $users = User::query()
            ->leftJoin($table, 'users.id', '=', $table.'.user_id')
            ->where($table.'.event_id', '=', $this->event_id)
            ->select(
                $table.'.place',
                'users.id',
                'users.middlename',
                $table.'.amount_top',
                $table.'.amount_try_top',
                $table.'.amount_zone',
                $table.'.amount_try_zone',
            )
            ->where('gender', '=', $this->gender)->get()->sortBy('place')->toArray();
        foreach ($users as $index => $user){
            $final_result = ResultRouteSemiFinalStage::where('event_id', '=', $this->event_id)->where('user_id', '=', $user['id'])->get();
            foreach ($final_result as $result){
                $users[$index]['amount_top_'.$result->final_route_id] = $result->amount_top;
                $users[$index]['amount_try_top_'.$result->final_route_id] = $result->amount_try_top;
                $users[$index]['amount_zone_'.$result->final_route_id] = $result->amount_zone;
                $users[$index]['amount_try_zone_'.$result->final_route_id] = $result->amount_try_zone;
            }
            $users[$index] = collect($users[$index])->except('id');
        }
        return collect($users);
    }
}