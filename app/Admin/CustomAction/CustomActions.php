<?php

namespace App\Admin\CustomAction;
class CustomActions extends \Encore\Admin\Grid\Displayers\Actions
{
    protected function renderEdit()
    {
        return <<<EOT
<a href="{$this->getResource()}/{$this->getKey()}/edit" class="btn btn-xs btn-default">
    <i class="fa fa-edit"></i> Редактировать
</a>&nbsp;
EOT;
    }

    protected function renderDelete()
    {
        parent::renderDelete();

        return <<<EOT
<a href="javascript:void(0);" data-id="{$this->getKey()}" class="grid-row-delete btn btn-xs btn-danger">
    <i class="fa fa-trash"></i> Удалить
</a>
EOT;

    }
    protected function renderView()
    {
        return <<<EOT
<a href="{$this->getResource()}/{$this->getRouteKey()}" class="{$this->grid->getGridRowName()}-view btn btn-xs btn-secondary">
    <i class="fa fa-eye"></i> Просмотр
</a>
EOT;
    }

}
