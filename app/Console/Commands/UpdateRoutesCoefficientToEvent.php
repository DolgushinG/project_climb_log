<?php

namespace App\Console\Commands;

use App\Jobs\UpdateRouteCoefficientParticipants;
use App\Models\Event;
use Illuminate\Console\Command;

class UpdateRoutesCoefficientToEvent extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'routes:update-coefficient {--event_id=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Обновление все коэффициентов у трасс';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $event_id = $this->option('event_id');
        foreach (['male', 'female'] as $gender){
            UpdateRouteCoefficientParticipants::dispatch($event_id, $gender);
        }
        $event = Event::find($event_id);
        Event::refresh_final_points_all_participant($event);
    }
}
