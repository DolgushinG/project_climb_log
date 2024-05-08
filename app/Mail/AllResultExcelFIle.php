<?php

namespace App\Mail;

use App\Exports\AllResultExport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;

class AllResultExcelFIle extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $details;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($details)
    {
        $this->details = $details;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $file_name = 'Полные результаты.xlsx';
        $file = Excel::download(new AllResultExport($this->details['event_id']), $file_name, \Maatwebsite\Excel\Excel::XLSX);
        return $this->subject( 'Полные результаты')->markdown('emails.all-results')
            ->attach(
                $file->getFile(), ['as' => 'Полные_результаты.xlsx']
            );
    }
}
