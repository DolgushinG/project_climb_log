<?php

namespace App\Admin\CustomAction;
use App\Models\Event;
use Encore\Admin\Actions\Action;
use Encore\Admin\Actions\BatchAction;
use Encore\Admin\Actions\RowAction;
use Encore\Admin\Facades\Admin;
use Illuminate\Database\Eloquent\Collection;

class ActionRejectBill extends RowAction
{
    protected $id;
    public $event_id;

    public function __construct($id, $event_id)
    {
        $this->id = $id;
        $this->event_id = $event_id;
    }
    protected function script()
    {
        return <<<SCRIPT
                        $('#reject-bill').on('click', function () {
                            let id = document.getElementById('reject-bill').getAttribute('data-id');
                            let event_id = document.getElementById('reject-bill').getAttribute('data-event-id');
                             $.ajax({
                                    method: 'GET' ,
                                    url: 'reject/bill/event/'+ event_id +'/participant/'+id,
                                    success: function (data) {
                                        resolve([data, target]);
                                    },
                                    error:function(request){
                                        reject(request);
                                    }
                                });
                        });
                        SCRIPT;
    }

    public function render()
    {
        Admin::script($this->script());
        return "<a id='reject-bill' class='btn reject-bill btn-xs btn-warning' data-event-id='{$this->event_id}' data-id='{$this->id}'> Отозвать чек</a>";
    }

    public function __toString()
    {
        return $this->render();
    }

}