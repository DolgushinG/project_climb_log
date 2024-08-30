<?php

namespace App\Exports;

use App\Exports\Sheets\Results;
use App\Models\Event;
use App\Models\ParticipantCategory;
use App\Models\ResultRouteFinalStage;
use App\Models\ResultRouteSemiFinalStage;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class AllResultExport implements WithMultipleSheets
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
        $event = Event::find($this->event_id);
        if($event->is_france_system_qualification){
            $stage = 'FranceSystemQualification';
        } else {
            $stage = 'Qualification';
        }
        foreach ($genders as $gender) {
            foreach ($categories as $category){
                $sheets[] = new Results($this->event_id, $stage, $gender, $category);
            }
        }
        if($event->is_open_main_rating){
            foreach ($genders as $gender) {
                foreach ($categories as $category){
                    $sheets[] = new Results($this->event_id, 'MergeQualification', $gender, $category);
                }
            }
        }

        if($event->is_sort_group_semifinal){
            $categories = ParticipantCategory::where('event_id', $this->event_id)->get();
            foreach ($genders as $gender) {
                foreach ($categories as $category) {
                    $result = ResultRouteSemiFinalStage::where('event_id', $this->event_id)->where('category_id', $category->id)->first();
                    if($result){
                        $sheets[] = new Results($this->event_id, 'SemiFinal', $gender, $category);
                    }
                }
            }
        } else {
            foreach ($genders as $gender) {
                $result = ResultRouteSemiFinalStage::where('event_id', $this->event_id)->where('gender', $gender)->first();
                if($result){
                    $sheets[] = new Results($this->event_id, 'SemiFinal', $gender);
                }
            }
        }
        if($event->is_sort_group_final){
            $categories = ParticipantCategory::where('event_id', $this->event_id)->get();
            foreach ($genders as $gender) {
                foreach ($categories as $category) {
                    $result = ResultRouteFinalStage::where('event_id', $this->event_id)->where('category_id', $category->id)->first();
                    if($result){
                        $sheets[] = new Results($this->event_id, 'Final', $gender, $category);
                    }
                }
            }
        } else {
            foreach ($genders as $gender) {
                $result = ResultRouteFinalStage::where('event_id', $this->event_id)->where('gender', $gender)->first();
                if($result){
                    $sheets[] = new Results($this->event_id, 'Final', $gender);
                }
            }
        }
        if($event->is_open_team_result){
            $sheets[] = new Results($this->event_id, 'Team');
        }
        return $sheets;
    }


}
