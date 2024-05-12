<?php
namespace App\Notifications;

use MohsenAbrishami\Stethoscope\Notifications\Notifiable;

class StethoscopeNotifiable extends Notifiable
{
    public function routeNotificationForTelegram()
    {
        return config('stethoscope.notifications.telegram.channel_id');
    }
}
