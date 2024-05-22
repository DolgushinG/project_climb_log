<?php

namespace App\Admin\Actions;

use App\Admin\CustomAction\ActionExport;
use App\Exports\QualificationResultExport;
use App\Models\Event;
use App\Models\ResultQualificationClassic;
use App\Models\ResultFranceSystemQualification;
use App\Models\ResultSemiFinalStage;
use App\Models\User;
use Encore\Admin\Actions\Action;
use Encore\Admin\Actions\BatchAction;
use Encore\Admin\Facades\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;

class BatchNotificationOfParticipant extends Action
{
    public $name = 'Уведомить по почте всех юзеров';

    protected $selector = '.notify';

    public function handle(Request $request)
    {
        $event = Event::where('owner_id', '=', \Encore\Admin\Facades\Admin::user()->id)->where('active', 1)->first();
        if($event->is_france_system_qualification){
            $table = 'result_france_system_qualification';
        } else {
            $table = 'participant';
        }
        $users = User::query()
            ->leftJoin($table, 'users.id', '=', $table.'.user_id')
            ->where($table.'.event_id', '=', $event->id)
            ->select(
                'users.middlename',
                'users.email',
            )->get()->toArray();
        if($request->message && $request->subject){
            if(count($users) > 0){
                foreach ($users as $user){
                    ResultQualificationClassic::send_message_from_climbing_gym($request->subject, $request->message, $user, $event->climbing_gym_name);
                }
                return $this->response()->success('Отправлено')->refresh();
            }

        } else {
            Log::error('Не найдено сообщение - $request->'.$request->message);
            return $this->response()->error('Ошибка отправки')->refresh();
        }



    }

    public function form()
    {
        $this->modalLarge();
        $this->text('subject', 'Тема сообщения')->required();
        $this->textarea('message', 'Сообщение')->required();
    }
    public function html()
    {
        return "<a class='notify btn btn-sm btn-success'><i class='fa fa-send'></i> $this->name</a>
                    <style>
                .notify {margin-top:8px;}
                 @media screen and (max-width: 767px) {
                        .notify {margin-top:8px;}
                    }
                </style>
                ";
    }

}
