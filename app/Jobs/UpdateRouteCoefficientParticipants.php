<?php

namespace App\Jobs;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateRouteCoefficientParticipants implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private mixed $event_id;
    private mixed $gender;


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
     * @param $gender
     * @return void
     */
    public function __construct($event_id, $gender)
    {
        $this->event_id = $event_id;
        $this->gender = $gender;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Event::update_coefficient_for_all_route($this->event_id, $this->gender);
    }
}
