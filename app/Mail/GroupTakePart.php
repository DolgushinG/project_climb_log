<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class GroupTakePart extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $details;
    public $created_users;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($details, $created_users = [])
    {
        $this->details = $details;
        $this->created_users = $created_users;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject( 'Участие в соревновании группы')->markdown('emails.group-take-part');
    }
}
