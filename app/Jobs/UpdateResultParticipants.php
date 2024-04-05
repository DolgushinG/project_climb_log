<?php

namespace App\Jobs;

use App\Models\Event;
use App\Models\Grades;
use App\Models\Participant;
use App\Models\ResultParticipant;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateResultParticipants implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private mixed $event_id;


    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public int $timeout = 120000;

    /**
     * Create a new job instance.
     *
     * @param $event_id
     * @return void
     */
    public function __construct($event_id)
    {
        $this->event_id = $event_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $event = Event::find($this->event_id);
        Event::refresh_final_points_all_participant($event);
    }
}
