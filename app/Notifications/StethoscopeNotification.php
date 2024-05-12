<?php


namespace App\Notifications;

use Illuminate\Bus\Queueable;
use MohsenAbrishami\Stethoscope\Notifications\LogReportNotification;
use NotificationChannels\Telegram\TelegramMessage;

class StethoscopeNotification extends LogReportNotification
{
    use Queueable;

    public function toTelegram()
    {
        $formattedMessage = "
        *Message from stethoscope:*

        *Be careful!! 💀*

        Your server has the following problems:
        " . (isset($this->resourceLogs['cpu']) ? '- Cpu usage: ' . $this->resourceLogs['cpu'] . ' %' : '') . "
        " . (isset($this->resourceLogs['memory']) ? '- Memory usage: ' . $this->resourceLogs['memory'] . ' %' : '') . "
        " . (isset($this->resourceLogs['network']) ? '- Network connection status: ' . $this->resourceLogs['network'] : '') . "
        " . (isset($this->resourceLogs['storage']) ? '- Remaining free space on the Storage:  ' . $this->resourceLogs['storage'] . ' GB' : '') . "
        " . (isset($this->resourceLogs['webServer']) ? '- Web server status:  ' . $this->resourceLogs['webServer'] : '') . "
    ";

        return TelegramMessage::create()->content($formattedMessage);
    }
}
