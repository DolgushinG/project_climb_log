<?php

namespace App\Exports;

use App\Exports\Sheets\Results;
use App\Models\ParticipantCategory;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class FranceSystemQualificationResultExport implements WithMultipleSheets
{

    public $event_id;

    function __construct($event_id) {
        $this->event_id = $event_id;
    }

    /**, WithMultipleSheets
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [];
        $genders = ['male', 'female'];
        $categories = ParticipantCategory::where('event_id', $this->event_id)->get();
        foreach ($genders as $gender) {
            foreach ($categories as $category) {
                $sheets[] = new Results($this->event_id, 'FranceSystemQualification', $gender, $category);
            }
        }
        return $sheets;
    }
}
