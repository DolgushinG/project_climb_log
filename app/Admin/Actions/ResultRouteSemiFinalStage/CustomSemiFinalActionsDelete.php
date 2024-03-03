<?php

namespace App\Admin\Actions\ResultRouteSemiFinalStage;

use App\Models\Event;
use Encore\Admin\Actions\Action;
use Encore\Admin\Actions\BatchAction;
use Encore\Admin\Actions\RowAction;
use Encore\Admin\Facades\Admin;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\DB;

class CustomSemiFinalActionsDelete extends BatchAction
{
    public $name = 'batch copy';

    public function handle()
    {

        return $this->response()->success('Success message...')->refresh();
    }

    public function html()
    {
        return "<a class='export btn btn-sm btn-primary'><i class='fa fa-arrow-up'></i> Экспорт</a>";
    }

}
