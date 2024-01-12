<?php

namespace App\Admin\CustomAction;
use Encore\Admin\Actions\RowAction;
use Encore\Admin\Facades\Admin;

class ActionExcel extends RowAction
{
    protected $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

    protected function script()
    {
        return <<<SCRIPT
        $('.grid-check-row').on('click', function () {
            let id = this.getAttribute('data-id');
            document.location = 'exports/events/excel/' + id
        });

        SCRIPT;
    }

    public function render()
    {
        Admin::script($this->script());
        return "<a class='btn report-participant btn-xs btn-success grid-check-row' data-id='{$this->id}'>Excel</a>";
    }

    public function href()
    {
        return 'exports/events/excel/'.$this->id;
    }
    public function __toString()
    {
        return $this->render();
    }
}
