<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ListOfPendingParticipant extends Model
{
    protected $table = 'list_of_pending_participants';
    protected $casts = [
        'number_sets' =>'json',
    ];
}
