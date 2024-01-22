<?php

namespace App\Exports;

use App\Exports\Sheets\Results;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\BeforeExport;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class AdditionalFinalResultExport implements WithMultipleSheets
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
        foreach ($genders as $gender) {
            $sheets[] = new Results($this->event_id, 'AdditionalFinal', $gender);
        }
        return $sheets;
    }


}
