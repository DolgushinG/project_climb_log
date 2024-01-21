<?php

namespace App\Exports;

use App\Exports\Sheets\Results;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class FinalResultExport implements WithMultipleSheets, WithStyles
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
            'B2' => ['font' => ['italic' => true]],
//
//            // Styling an entire column.
            'C'  => ['font' => ['size' => 16]],
        ];
    }

    /**, WithMultipleSheets
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [];
        $genders = ['male', 'female'];
        foreach ($genders as $gender) {
            $sheets[] = new Results($this->event_id, 'Final', $gender);
        }
        return $sheets;
    }
}
