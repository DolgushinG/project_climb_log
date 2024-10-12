<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Models\OwnerPaymentOperations;
use App\Models\ResultFranceSystemQualification;
use App\Models\ResultQualificationClassic;
use Encore\Admin\Facades\Admin;
use Illuminate\Console\Command;

class UpdateOwnerOperations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-owner-operations {--event_id=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Обновление оплаты у события {--event_id=}';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $event_id = $this->option('event_id');
        $event = Event::find($event_id);
        if($event->is_france_system_qualification){
            $participants = ResultFranceSystemQualification::where('event_id', $event->id)->get();
        } else {
            $participants = ResultQualificationClassic::where('event_id', $event->id)->where('is_other_event', 0)->where('is_paid', 1)->get();
        }
        foreach ($participants as $participant){
            OwnerPaymentOperations::execute_payment_operations($participant, $participant->owner_id, $participant->amount_start_price, 'Стартовый взнос');
            # Пересчитываем оплату за соревы
            OwnerPaymentOperations::execute_payment($participant, $participant->owner_id, $event, $participants->count());
        }
        $this->info('Оплата успешна обновлены');
    }
}
