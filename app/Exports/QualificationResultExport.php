<?php

namespace App\Exports;

use App\Models\Participant;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class QualificationResultExport implements WithHeadings, FromCollection, WithStyles
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
            'Город',
            'Команда',
            'Категория',
            'Место',
            'Баллы',
        ];
    }

    public function collection()
    {
        HeadingRowFormatter::default('none');


        $users_id = Participant::where('event_id', '=', $this->event_id)->where('owner_id', '=', \Encore\Admin\Facades\Admin::user()->id)->pluck('user_id')->toArray();
        $users_point = Participant::where('event_id', '=', $this->event_id)->where('owner_id', '=', \Encore\Admin\Facades\Admin::user()->id)->pluck('points','user_id')->toArray();
        $fields = ['firstname','id', 'email','year','lastname','skill','sport_category','email_verified_at', 'created_at', 'updated_at'];
        $users = User::whereIn('id', $users_id)->get();
        foreach ($users as $index => $user){
            $users[$index] = collect($user->toArray())->except($fields);
            $users[$index]['place'] = Participant::get_places_participant_in_qualification($this->event_id, $user->id, true);
            $users[$index]['middlename'] = $user->middlename;
            $users[$index]['gender'] = trans_choice('somewords.'.$user->gender, 10);
            $users[$index]['city'] = $user->city;
            $users[$index]['team'] = $user->team;
            $users[$index]['category'] = User::category($user->category);
            $users[$index]['points'] = $users_point[$user->id];
        }
        return $users->sortBy('place');
    }
}
