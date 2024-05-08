<?php

namespace App\Admin\CustomAction;
use App\Models\Event;
use Encore\Admin\Actions\RowAction;
use Encore\Admin\Facades\Admin;

class ActionExportCardParticipantFestival extends RowAction
{
    protected $id;

    public function __construct($id, $name)
    {
        $this->id = $id;
        $this->name = $name;
    }

    protected function script()
    {
        return <<<SCRIPT
                        $('.card-participant').on('click', function () {
                            let id = this.getAttribute('data-id');
                            document.location = 'exports/events/card-festival/participant/' + id
                        });
                        SCRIPT;
    }

    public function render()
    {
        Admin::script($this->script());
        $event = Event::find($this->id);
        $display = '';
        if($event->is_qualification_counting_like_final){
            $display = 'display:None';
        }
        $btn = strtoupper($this->name);
        return "<a class='btn card-participant btn-xs btn-success grid-check-row' style='{$display}' data-id='{$this->id}'>$btn</a>";
    }

    public function href()
    {
        return 'exports/events/card-festival/participant/'.$this->id;
    }
    public function __toString()
    {
        return $this->render();
    }
}
