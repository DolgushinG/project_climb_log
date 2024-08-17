<?php

namespace App\Console\Commands;

use App\Jobs\UpdateGradeInResultAllParticipant;
use App\Models\Event;
use App\Models\Route;
use App\Models\RoutesOutdoor;
use Illuminate\Console\Command;

class UpdateAllGradeInResultAllParticipant extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'grades:update-all {--event_id=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Обновление всех категорий у всех участников';

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
        $event = Event::find($event_id);
        if($event->type_event){
            $routes = RoutesOutdoor::where('event_id', $event_id)->get();
        } else {
            $routes = Route::where('event_id', $event_id)->get();
        }
        foreach ($routes as $route){
            UpdateGradeInResultAllParticipant::dispatch($event_id, $route, $route->grade);
        }
    }
}
