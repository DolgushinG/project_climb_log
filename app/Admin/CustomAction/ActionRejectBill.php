<?php

namespace App\Admin\CustomAction;
use App\Models\Event;
use App\Models\ResultQualificationClassic;
use App\Models\ResultFranceSystemQualification;
use Encore\Admin\Actions\RowAction;
use Encore\Admin\Facades\Admin;

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

                                    },
                                    error:function(request){

                                    }
                                });
                        });
                        SCRIPT;
    }


    public function render()
    {
        Admin::script($this->script());
        $event = Event::find($this->event_id);
        if($event->is_france_system_qualification){
            $participant = ResultFranceSystemQualification::find($this->id);
        } else {
            $participant = ResultQualificationClassic::find($this->id);
        }
        if($participant->bill){
            $display = '';
        } else {
            $display = 'display: None';
        }
        return "<a id='reject-bill' class='btn reject-bill btn-xs btn-warning' style='{$display}' data-event-id='{$this->event_id}' data-id='{$this->id}'> Отозвать чек</a>";
    }

    public function __toString()
    {
        return $this->render();
    }

}
