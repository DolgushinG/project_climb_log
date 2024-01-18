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
        $routes = ResultParticipant::where('event_id', '=', $this->event_id)->select('route_id')->distinct()->get()->toArray();
        $event = Event::find($this->event_id);
        $format = $event->mode;
        $final_participant = Participant::where('event_id', '=', $this->event_id)->pluck('user_id')->toArray();
        foreach ($final_participant as $user) {
            $points = 0;
            $routes_only_passed = array();
            foreach ($routes as $route) {

                $user_model = ResultParticipant::where('event_id', '=', $this->event_id)
                    ->where('user_id', '=', $user)
                    ->where('route_id', '=', $route['route_id'])
                    ->first();
                if ($user_model->attempt != 0) {
                    $gender = User::gender($user);
                    $value_category = Grades::where('grade', '=', $user_model->grade)->where('owner_id', '=', $event->owner_id)->first()->value;
                    $coefficient = ResultParticipant::get_coefficient($this->event_id, $route['route_id'], $gender);
                    $value_route = (new \App\Models\ResultParticipant)->get_value_route($user_model->attempt, $value_category, $event->mode);
                    $points += $coefficient + $value_route;
                    $point_route = $coefficient + $value_route;
                    $user_model->points = $point_route;
                    $routes_only_passed[] = $user_model;
                }
            }
            if ($format == 1) {
                $points = 0;
                usort($routes_only_passed, function ($a, $b) {
                    return $a['points'] <=> $b['points'];
                });
                $lastElems = array_slice($routes_only_passed, -10, 10);
                foreach ($lastElems as $lastElem) {
                    $points += $lastElem->points;
                }
            }
            $final_participant_result = Participant::where('user_id', '=', $user)->where('event_id', '=', $this->event_id)->first();
            $final_participant_result->points = $points;
            $final_participant_result->event_id = $this->event_id;
            $final_participant_result->user_id = $user;
            $final_participant_result->user_place = Participant::get_places_participant_in_qualification($this->event_id, $user, true);
            $final_participant_result->save();
        }
    }
}
