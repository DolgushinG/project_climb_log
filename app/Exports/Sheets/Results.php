<?php

namespace App\Exports\Sheets;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class Results implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize
{
    public $event_id;
    public $type;
    public $gender;
    public $category;

    function __construct($event_id, $type, $gender, $category = null) {
        $this->event_id = $event_id;
        $this->type = $type;
        $this->gender = $gender;
        $this->category = $category;
    }

    public function headings(): array
    {
        if($this->type == 'Final'){
           return [
               'Место',
               'Участник(Фамилия Имя)',
               'Сумма TOP',
               'Сумма попыток на TOP',
               'Сумма ZONE',
               'Сумма попыток на ZONE'
            ];
        }
        if($this->type == 'Qualification') {
            $res = [
                'Место',
                'Участник(Фамилия Имя)',
                'Баллы',
                'Сет'
            ];
            return $res;
        }
        return [];
    }


    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        if($this->type == 'Final'){
            return DB::table('users')
                ->leftJoin('result_final_stage', 'users.id', '=', 'result_final_stage.user_id')
                ->where('result_final_stage.event_id', '=', $this->event_id)
                ->select(
                    'result_final_stage.place',
                    'users.middlename',
                    'result_final_stage.amount_top',
                    'result_final_stage.amount_try_top',
                    'result_final_stage.amount_zone',
                    'result_final_stage.amount_try_zone',
                )
                ->where('gender', '=', $this->gender)->get();

        }
        if($this->type == 'Qualification'){
            return DB::table('users')
                ->leftJoin('participants', 'users.id', '=', 'participants.user_id')
                ->where('participants.event_id', '=', $this->event_id)
                ->where('users.category', '=', $this->category->id)
                ->select(
                    'participants.user_place',
                    'users.middlename',
                    'participants.points',
                    'participants.number_set',
                )
                ->where('gender', '=', $this->gender)->get();
        }
        return collect([]);
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return  trans_choice('somewords.'.$this->type, 10).
            '('.$this->category->category.
            ' '.trans_choice('somewords.'.$this->gender, 10).')';
    }
}
