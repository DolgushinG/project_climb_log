<?php

namespace App\Admin\Actions;

use App\Models\Event;
use Encore\Admin\Actions\Action;
use Encore\Admin\Facades\Admin;
use Illuminate\Http\Request;

class BatchDisableSets extends Action
{
    public $name = 'Выключить сеты';

    protected $selector = '.disable-sets';

    public function handle(Request $request)
    {
        $owner_id = Admin::user()->id;
        $event = Event::where('owner_id', $owner_id)->where('active', 1)->first();
        if($event){
            if($event->is_input_set == 1){
                $event->is_input_set = 0;
            } else {
                $event->is_input_set = 1;
            }
            $event->save();
        }

        return $this->response()->success('готово')->refresh();
    }

    public function dialog()
    {
        $owner_id = Admin::user()->id;
        $event = Event::where('owner_id', $owner_id)->where('active', 1)->first();
        if($event->is_input_set){
            $this->confirm('Подтвердить включиние регистрации по сетам');
        } else {
            $this->confirm('Подтвердить отключение сетов для участие в соревновании');
        }

    }

    public function html()
    {
        $owner_id = Admin::user()->id;
        $event = Event::where('owner_id', $owner_id)->where('active', 1)->first();

        if($event->is_input_set){
            $btn = 'disable-sets btn btn-sm btn-success';
            $icon = 'fa fa-fire';
            $text = 'Регистрация без сетов (активна)';
        } else {
            $btn = 'disable-sets btn btn-sm btn-primary';
            $icon = 'fa fa-power-off';
            $text = 'Регистрация по сетам (активна)';
        }
        return "<a class='{$btn}'><i class='{$icon}'></i> {$text}</a>
            <style>
                @media screen and (max-width: 767px) {
                    .disable-sets {margin-top:8px;}
                    }
            </style>
        ";
    }

}
