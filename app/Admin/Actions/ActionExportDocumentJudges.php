<?php

namespace App\Admin\Actions;
use Encore\Admin\Actions\RowAction;
use Encore\Admin\Facades\Admin;

class ActionExportDocumentJudges extends RowAction
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
                        $('.judges').on('click', function () {
                            let id = this.getAttribute('data-id');
                            document.location = 'exports/judges/' + id
                        });
                        SCRIPT;
    }

    public function render()
    {
        Admin::script($this->script());
        $btn = strtoupper($this->name);
        return "<a class='btn judges btn-xs btn-success grid-check-row' data-id='{$this->id}'>$btn</a>";
    }

    public function href()
    {
        return 'exports/judges/'.$this->id;
    }
    public function __toString()
    {
        return $this->render();
    }
}
