<?php

namespace App\Admin\CustomAction;
use Encore\Admin\Actions\RowAction;
use Encore\Admin\Facades\Admin;

class ActionExportCardParticipant extends RowAction
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
                            document.location = 'exports/events/card/participant/' + id
                        });
                        SCRIPT;
    }

    public function render()
    {
        Admin::script($this->script());
        $btn = strtoupper($this->name);
        return "<a class='btn card-participant btn-xs btn-success grid-check-row' data-id='{$this->id}'>$btn</a>";
    }

    public function href()
    {
        return 'exports/events/card/participant/'.$this->id;
    }
    public function __toString()
    {
        return $this->render();
    }
}
