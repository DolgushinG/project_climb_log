<?php

namespace App\Admin\Actions;

use App\Mail\MessageParticipants;
use App\Models\Event;
use App\Models\MessageForParticipant;
use App\Models\ResultQualificationClassic;
use App\Models\User;
use Encore\Admin\Actions\Action;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BatchMessageForParticipant extends Action
{
    public $name = 'Предупредительный баннер';

    protected $selector = '.banner';

    public function handle(Request $request)
    {
        $event = Event::where('owner_id', '=', \Encore\Admin\Facades\Admin::user()->id)->where('active', 1)->first();
        $msg = MessageForParticipant::where('event_id', $event->id)->first();
        if(!$msg){
            $msg = new MessageForParticipant;
        }
        $msg->event_id = $event->id;
        $msg->owner_id = $event->owner_id;
        $msg->text = $request->text ?? '';
        $msg->type = $request->type;
        $msg->is_show = $request->is_show;
        $msg->save();
        return $this->response()->success('Готово')->refresh();
    }

    public function form()
    {
        $this->modalSmall();
        $event = Event::where('owner_id', '=', \Encore\Admin\Facades\Admin::user()->id)->where('active', 1)->first();
        $msg = MessageForParticipant::where('event_id', $event->id)->first();
        if($msg){
            $this->textarea('text', 'Текст')->value($msg->text);
            $this->select('type', 'Тип сообщение и цвет фона')->value($msg->type)->help('Желтый фон как предупреждение, красный как обязательный')->options(['warning' => 'Жёлтый', 'danger'=> 'Красный'])->required();
            $this->radio('is_show', 'Отображение')->options([1 => 'Показать', 0 => 'Скрыть'])->value($msg->is_show)->required();
        } else {
            $this->textarea('text', 'Текст')->required();
            $this->select('type', 'Тип сообщение и цвет фона')->help('Желтый фон как предупреждение, красный как обязательный')->options(['warning' => 'Жёлтый', 'danger'=> 'Красный'])->required();
            $this->radio('is_show', 'Отображение')->options([1 => 'Показать', 0 => 'Скрыть'])->required();
        }

    }
    public function html()
    {
        return "<a class='banner btn btn-sm btn-warning'><i class='fa fa-server'></i> $this->name</a>
                    <style>
                .banner {margin-top:8px;}
                 @media screen and (max-width: 767px) {
                        .banner {margin-top:8px;}
                    }
                </style>
                ";
    }

}
