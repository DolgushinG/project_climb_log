<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ReverseNamesAllUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:reverse-middlename';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Команда единоразовая так как требуется перевернуть значение middlename с Имя Фамилия на Фамилия Имя';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $users = User::all();
        foreach ($users as $user){
            $user->middlename = $user->lastname.' '.$user->firstname;
            $user->save();
        }
        return 'Переворот с имени фамилии на фамилию имя прошел успешно';
    }
}
