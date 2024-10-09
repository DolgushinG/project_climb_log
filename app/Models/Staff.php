<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    protected $casts = [
        'events_id' =>'json',
    ];
    const SHOW_TYPES = [
        'Судья на трассе',
        'Зам. Главного судьи',
        'Зам. Главного судьи по виду',
        'Зам. Главного судьи по безопасности',
        'Главный секретарь',
        'Помощник судьи',
        'Главный судья',
        'Председатель',
        'Подготовщик',
        'Волонтер',
        'Админ',
        'Клинер',
        'Тестер',
        'Другой'
    ];
}
