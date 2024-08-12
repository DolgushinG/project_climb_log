<?php

namespace App\Jobs;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class MergeResultsParticipants implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private int $event_id;
    private array $users_ids;
    private array $event_ids;
    private string $type_operation;

    public int $timeout = 120000;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($event_id, $users_ids, $event_ids, $type_operation)
    {
        $this->event_id = $event_id;
        $this->users_ids = $users_ids;
        $this->event_ids = $event_ids;
        $this->type_operation = $type_operation;

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $event = Event::find($this->event_id);
        switch ($this->type_operation){
            case 'merge_point':
                Event::merge_point($this->users_ids, $this->event_ids, $event);
                break;
            case 'merge_auto_categories':
                Event::merge_auto_categories($event, $this->users_ids, $this->event_ids);
                break;
            case 'counting_global_place':
                Event::counting_global_category_place($event);
        }
    }
}
