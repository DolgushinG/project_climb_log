<?php

namespace App\Exports;

use App\Models\Participant;
use App\Models\ResultFinalStage;
use App\Models\ResultRouteFinalStage;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class FinalResultExport implements WithHeadings, FromCollection, WithStyles
{

    public $event_id;

    function __construct($event_id) {
        $this->event_id = $event_id;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text.
            1    => ['font' => ['bold' => true]],

//            // Styling a specific cell by coordinate.
//            'B2' => ['font' => ['italic' => true]],
//
//            // Styling an entire column.
//            'C'  => ['font' => ['size' => 16]],
        ];
    }

//    /**, WithMultipleSheets
//     * @return array
//     */
//    public function sheets(): array
//    {
//        $sheets = [];
//
//        for ($month = 1; $month <= 12; $month++) {
//            $sheets[] = new InvoicesPerMonthSheet($this->year, $month);
//        }
//
//        return $sheets;
//    }

    public function headings(): array
    {
        return [
            'Участник',
            'Пол',
            'Место',
            'Кол-во TOP',
            'Кол-во попыток на TOP',
            'Кол-во ZONE',
            'Кол-во попыток на ZONE',
        ];
    }

    public function collection()
    {
        HeadingRowFormatter::default('none');


        $users_id = Participant::where('event_id', '=', $this->event_id)->where('owner_id', '=', \Encore\Admin\Facades\Admin::user()->id)->pluck('user_id')->toArray();
        $users_male = User::whereIn('id', $users_id)->where('gender', '=', 'male')->get();
        $users_female = User::whereIn('id', $users_id)->where('gender', '=', 'female')->get();
        $users_male_final = $this->prepare_export($users_male);
        $users_female_final = $this->prepare_export($users_female);
        $usr = array_merge($users_male_final->sortBy('place')->toArray(), $users_female_final->sortBy('place')->toArray());
        return collect($usr);
    }

    public function prepare_export($users){
        $fields = ['firstname','id', 'city', 'team', 'category', 'email','year','lastname','skill','sport_category','email_verified_at', 'created_at', 'updated_at'];
        foreach ($users as $index => $user){
            $users[$index] = collect($user->toArray())->except($fields);
            $result = ResultFinalStage::where('event_id', '=', $this->event_id)->where('user_id', '=', $user->id)->first();
            $users[$index]['middlename'] = $user->middlename;
            $users[$index]['gender'] = trans_choice('somewords.'.$user->gender, 10);
            $users[$index]['place'] = $result->place;
            $users[$index]['amount_top'] = $result->amount_top;
            $users[$index]['amount_try_top'] = $result->amount_try_top;
            $users[$index]['amount_zone'] = $result->amount_zone;
            $users[$index]['amount_try_zone'] = $result->amount_try_zone;
        }
        return $users;
    }
}
