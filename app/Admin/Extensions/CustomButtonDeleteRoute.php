<?php

namespace App\Admin\Extensions;
use Encore\Admin\Actions\Action;
use Encore\Admin\Widgets\Form;

class CustomButtonDeleteRoute extends Action
{
    public $id;
    public function __construct($id)
    {
        $this->id = $id;
    }

    public function render()
    {
        return <<<EOT
<a href="javascript:void(0);" data-id="{$this->id}" class="grid-row-delete btn btn-xs btn-danger">
    <i class="fa fa-trash"></i> Удалить
</a>
EOT;
    }
}
