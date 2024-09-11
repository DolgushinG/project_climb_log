<?php

namespace App\Models;

use App\Helpers\Helpers;
use Encore\Admin\Facades\Admin;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UpdateParticipantResult extends Model
{
    use HasFactory;

    public static function is_validate_access_delete($model, $user_id, $event_id)
    {
        if($model && $user_id && $event_id){
            if(Admin::user()->is_delete_result == 1 && $model->is_paid == 0){
                return true;
            }
        }
        return false;
    }
}
