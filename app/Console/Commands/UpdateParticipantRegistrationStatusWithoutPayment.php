<?php

namespace App\Console\Commands;

use App\Models\ResultQualificationClassic;
use App\Models\ResultFranceSystemQualification;
use Illuminate\Console\Command;
use App\Models\Event;
use Carbon\Carbon;

class UpdateParticipantRegistrationStatusWithoutPayment extends Command
{
    protected $signature = 'participant:update-reg-status';
    protected $description = 'Удаляет регистрацию участников в базе данных без оплаты';

    public function handle()
    {
        // Получаем текущую дату и время
        // Получаем текущую дату и время
        $now = Carbon::now();

    // Устанавливаем временную зону на "Europe/Moscow"
        $now->setTimezone('Europe/Moscow');

    // Получаем все события, где срок регистрации не истек (registration_time_expired не равен 0)
    // и для которых требуется оплата регистрации (is_need_pay_for_reg равно 1)
        $events = Event::whereNotIn('registration_time_expired', [0])
            ->where('is_need_pay_for_reg', 1)
            ->get();

        // Перебираем каждое событие
        foreach ($events as $event){
            // Определяем, нужно ли считать участников как финалистов или нет
            if($event->is_france_system_qualification){
                // Если да, получаем участников события из таблицы ResultQualificationLikeFinal
                $participants = ResultFranceSystemQualification::where('event_id', $event->id)->get();
            } else {
                // Если нет, получаем участников события из таблицы Participant
                $participants = ResultQualificationClassic::where('event_id', $event->id)->get();
            }

            // Перебираем каждого участника события
            foreach ($participants as $participant){
                // Получаем количество дней, которые нужно добавить к дате создания участника
                $days_to_add = $event->registration_time_expired;

                // Вычисляем новую дату, добавляя к дате создания участника количество дней
                $new_date = $participant->created_at->addDays($days_to_add);

                // Форматируем новую дату в строку в формате 'Y-m-d H:i'
                $formatted_date = $new_date->format('Y-m-d H:i');

                // Проверяем, если текущая дата и время больше или равно новой дате
                if ($now->gte($formatted_date)) {
                    // Если условие выполняется, удаляем участника из базы данных
                    $participant->delete();
                }
            }
        }

        $this->info('Регистрации без оплат успешно удалены');
    }
}
