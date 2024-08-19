<?php

namespace App\Jobs;

use App\Models\Event;
use App\Models\Route;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateGradeInResultAllParticipant implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private int $event_id;
    private Route $route;
    private string $grade;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($event_id, Route $route, $grade)
    {
        $this->event_id = $event_id;
        $this->route = $route;
        $this->grade = $grade;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $event = Event::find($this->event_id);
        Event::refresh_grade_all_participant_in_result_for_edit($event, $this->route, $this->grade);
        Event::refresh_grade_all_participant_in_route_result($event, $this->route, $this->grade);
        Event::refresh_final_points_all_participant($event);
    }
}
