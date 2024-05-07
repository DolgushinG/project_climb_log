<?php

namespace App\Admin\CustomAction;
use App\Models\Event;
use Encore\Admin\Actions\RowAction;
use Encore\Admin\Facades\Admin;

class ActionExportList extends RowAction
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
                        $('.list-participant').on('click', function () {
                            let id = this.getAttribute('data-id');
                            document.location = 'exports/events/list/participant/' + id
                        });
                        SCRIPT;
    }

    public function render()
    {
        Admin::script($this->script());
        $btn = strtoupper($this->name);
        return "<a class='btn list-participant btn-xs btn-success grid-check-row' data-id='{$this->id}'>$btn</a>";
    }

    public function href()
    {
        return 'exports/events/list/participant/'.$this->id;
    }
    public function __toString()
    {
        return $this->render();
    }
}
