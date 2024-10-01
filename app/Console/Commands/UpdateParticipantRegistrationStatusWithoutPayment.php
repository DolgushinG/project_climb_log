<?php

namespace App\Console\Commands;

use App\Models\ResultQualificationClassic;
use App\Models\ResultFranceSystemQualification;
use Illuminate\Console\Command;
use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class UpdateParticipantRegistrationStatusWithoutPayment extends Command
{
    protected $signature = 'participant:update-reg-status';
    protected $description = 'Удаляет регистрацию участников в базе данных без оплаты';

    public function handle()
    {
        $now = Carbon::now();

        $now->setTimezone('Europe/Moscow');

        $events = Event::whereNotIn('registration_time_expired', [0])
            ->where('is_need_pay_for_reg', 1)
            ->get();

        foreach ($events as $event){
            if($event->is_france_system_qualification){
                $participants = ResultFranceSystemQualification::where('event_id', $event->id)->get();
            } else {
                $participants = ResultQualificationClassic::where('event_id', $event->id)->where('is_other_event', 0)->get();
            }

            foreach ($participants as $participant){
                $days_to_add = $event->registration_time_expired;
                $new_date = $participant->created_at->addDays($days_to_add);
                $formatted_date = $new_date->format('Y-m-d H:i');
                if ($now->gte($formatted_date)) {
                    Log::info('Удаление участие участника - user_id - '.$participant->user_id);
                    $participant->delete();
                }
            }
        }

        $this->info('Регистрации без оплат успешно удалены');
    }
}
