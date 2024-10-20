<?php

namespace App\Exports;

use App\Exports\Sheets\ProtocolCards;
use App\Models\Event;
use App\Models\ParticipantCategory;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class FinalProtocolCardsExport implements WithMultipleSheets
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
        if($event->is_additional_final){
            $categories = ParticipantCategory::where('event_id', $this->event_id)->get();
            foreach ($genders as $gender) {
                foreach ($categories as $category) {
                    $sheets[] = new ProtocolCards($this->event_id, 'final', $gender, $category);
                }
            }
        } else {
            foreach ($genders as $gender) {
                $sheets[] = new ProtocolCards($this->event_id, 'final', $gender);
            }
        }
        return $sheets;
    }


}
