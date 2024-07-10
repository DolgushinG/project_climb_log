<?php

namespace App\Jobs;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateAttemptInRoutesParticipants implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private mixed $event_id;
    private mixed $data;


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
     * @param $data
     * @return void
     */
    public function __construct($event_id, $data)
    {
        $this->event_id = $event_id;
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Event::update_attempt_for_participant($this->event_id, $this->data);
    }
}
