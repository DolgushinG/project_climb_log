<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OwnerPayments extends Model
{
    protected $table = 'owner_payments';

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
