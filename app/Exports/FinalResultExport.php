<?php

namespace App\Exports;

use App\Exports\Sheets\Results;
use App\Models\Event;
use App\Models\ParticipantCategory;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class FinalResultExport implements WithMultipleSheets
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
        $event = Event::find($this->event_id);
        if($event->is_sort_group_final){
            $categories = ParticipantCategory::where('event_id', $this->event_id)->get();
            foreach ($genders as $gender) {
                foreach ($categories as $category) {
                    $sheets[] = new Results($this->event_id, 'Final', $gender, $category);
                }
            }
        } else {
            foreach ($genders as $gender) {
                $sheets[] = new Results($this->event_id, 'Final', $gender);
            }
        }
        return $sheets;

    }


}
