<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Models\ListOfPendingParticipant;
use App\Models\ResultFranceSystemQualification;
use App\Models\ResultQualificationClassic;
use App\Models\Set;
use App\Models\User;
use Illuminate\Console\Command;

class UpdateSetsParticipants extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sets:update-sets-participant';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ищет и обновляет сеты участников';

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
        $events = Event::where('is_input_set', 0)->where('active', 1)->where('is_registration_state', 1)->where('is_public', 1)->get();
        foreach ($events as $event){
            $list = ListOfPendingParticipant::where('event_id', '=', $event->id)->orderBy('created_at', 'asc')->get();
            foreach ($list as $job){
                $number_set = $this->get_free_set($event, $job->number_sets);
                if($number_set){
                    if ($event->is_france_system_qualification) {
                        $participants_event = ResultFranceSystemQualification::where('event_id', '=', $event->id)->where('user_id', '=', $job->user_id)->first();
                        if(!$participants_event){
                            $participants_event = new ResultFranceSystemQualification;
                        }
                    } else {
                        $participants_event = ResultQualificationClassic::where('event_id', '=', $event->id)->where('user_id', '=', $job->user_id)->first();
                        if(!$participants_event){
                            $participants_event = new ResultQualificationClassic;
                        }
                    }
                    $user = User::find($job->user_id);
                    $number_set = Set::where('owner_id', $event->owner_id)->where('number_set', $number_set)->first();
                    $participants_event->category_id = $job->category_id;
                    $participants_event->user_id = $job->user_id;
                    $participants_event->owner_id = $event->owner_id;
                    $participants_event->event_id = $job->event_id;
                    $participants_event->gender = $user->gender;
                    $participants_event->sport_category = $user->sport_category;
                    $participants_event->number_set_id = $number_set->id;
                    $participants_event->active = 0;
                    $participants_event->is_paid = 0;
                    $participants_event->save();
                    if($user && $event && $participants_event){
                        ResultQualificationClassic::send_main_about_take_part($event, $user, $participants_event);
                    }
                    $user_job = ListOfPendingParticipant::where('event_id', '=', $event->id)->where('user_id', '=', $job->user_id)->first();
                    $user_job->delete();
                    $this->info('Обновление сетов прошло успешно');
                }
            }
        }
    }

    public function get_free_set($event, $number_sets)
    {
        $sets = Set::whereIn('number_set', $number_sets)->where('owner_id', '=', $event->owner_id)->get();
        foreach ($sets as $set) {
            if ($event->is_france_system_qualification) {
                $participants_event = ResultFranceSystemQualification::where('event_id', '=', $event->id)->where('owner_id', '=', $event->owner_id)->where('number_set_id', '=', $set->id)->count();
            } else {
                $participants_event = ResultQualificationClassic::where('event_id', '=', $event->id)->where('owner_id', '=', $event->owner_id)->where('number_set_id', '=', $set->id)->count();
            }
            $free = $set->max_participants - $participants_event;
            if($free > 0){
                return $set->number_set;
            }
        }
        return false;
    }
}
