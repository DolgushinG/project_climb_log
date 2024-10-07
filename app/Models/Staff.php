<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    protected $casts = [
        'events_id' =>'json',
    ];
    const TYPES = ['judge', 'helper_judge','main_judge','routesetter', 'helper', 'administrator', 'cleaner', 'tester', 'other'];
    const SHOW_TYPES = [
        'judge' => 'Судья',
        'helper_judge' => 'Помощник судьи',
        'main_judge' => 'Главный судьи',
        'routesetter' => 'Подготовщик',
        'helper' => 'Волонтер',
        'administrator' => 'Админ',
        'cleaner' => 'Клинер',
        'tester'=> 'Тестер',
        'other'=> 'Другой'
    ];
}
