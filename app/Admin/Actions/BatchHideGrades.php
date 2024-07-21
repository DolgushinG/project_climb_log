<?php

namespace App\Admin\Actions;

use App\Models\Event;
use Encore\Admin\Actions\Action;
use Encore\Admin\Facades\Admin;
use Illuminate\Http\Request;

class BatchHideGrades extends Action
{
    public $name = 'Скрыть категорию трассы для участников';

    protected $selector = '.hide-grades';

    public function handle(Request $request)
    {
        $owner_id = Admin::user()->id;
        $event = Event::where('owner_id', $owner_id)->where('active', 1)->first();
        if($event){
            if($event->is_hide_grades == 1){
                $event->is_hide_grades = 0;
            } else {
                $event->is_hide_grades = 1;
            }
            $event->save();
        }

        return $this->response()->success('готово')->refresh();
    }

    public function dialog()
    {
        $owner_id = Admin::user()->id;
        $event = Event::where('owner_id', $owner_id)->where('active', 1)->first();
        if($event->is_hide_grades){
            $this->confirm('Уверены, что хотите показать категории для участников?');
        } else {
            $this->confirm('Уверены, что хотите скрыть категории трасс для участников? ');
        }

    }

    public function html()
    {
        $owner_id = Admin::user()->id;
        $event = Event::where('owner_id', $owner_id)->where('active', 1)->first();

        if($event->is_hide_grades){
            $btn = 'hide-grades btn btn-sm btn-success';
            $icon = 'fa fa-eye';
            $text = 'Показать категорию трасс для участников';
        } else {
            $btn = 'hide-grades btn btn-sm btn-primary';
            $icon = 'fa fa-eye-slash ';
            $text = 'Скрыть категории трасс от участников';

        }
        return "<a class='{$btn}'><i class='{$icon}'></i> {$text}</a>
            <style>
                @media screen and (max-width: 767px) {
                    .hide-grades {margin-top:8px;}
                    }
            </style>
        ";
    }

}
