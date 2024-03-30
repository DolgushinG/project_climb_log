<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Event;
use Carbon\Carbon;

class UpdateEventStatus extends Command
{
    protected $signature = 'events:update-status';
    protected $description = 'Обновляет статусы событий в базе данных';

    public function handle()
    {
        // Получаем текущую дату и время
        $now = Carbon::now();
        $now->setTimezone('Europe/Moscow');
        // Получаем все события, у которых is_registration_state = true
        $events = Event::where('is_registration_state', true)->where('is_send_result_state', true)->get();

        foreach ($events as $event) {
            if($event->datetime_registration_state){
                if ($now->gte($event->datetime_registration_state)) {
                    $event->is_registration_state = false;
                    $event->save();
                }
            }
            // Проверяем, если дата и время события прошли, меняем статус
           if($event->datetime_send_result_state){
               if ($now->gte($event->datetime_send_result_state)) {
                   $event->is_send_result_state = false;
                   $event->save();
               }
           }
        }

        $this->info('Статусы событий успешно обновлены');
    }
}
