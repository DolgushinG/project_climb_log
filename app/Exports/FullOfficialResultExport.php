<?php

namespace App\Exports;

use App\Exports\Sheets\Results;
use App\Models\Event;
use App\Models\ParticipantCategory;
use App\Models\ResultRouteFinalStage;
use App\Models\ResultRouteSemiFinalStage;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class FullOfficialResultExport implements WithMultipleSheets
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
            foreach ($categories as $category){
                $sheets[] = new Results($this->event_id, 'Full', $gender, $category);
            }
        }
        return $sheets;
    }


}
