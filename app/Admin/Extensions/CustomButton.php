<?php

namespace App\Admin\Extensions;

use Encore\Admin\Actions\Action;
use Encore\Admin\Widgets\Form;

class CustomButton extends Action
{
    public function render()
    {
        return '<button type="submit" class="btn btn-primary">Создать</button>';
    }
}
