<?php

namespace App\Admin\CustomAction;
use Encore\Admin\Actions\RowAction;
use Encore\Admin\Facades\Admin;

class ActionCloneEvent extends RowAction
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
                        $('.clone-event').on('click', function () {
                            let id = this.getAttribute('data-id');
                             $.ajax({
                                    method: 'GET' ,
                                    url: '/admin/events/clone/'+ id,
                                    success: function (data) {
                                       location.reload()
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
        $btn = strtoupper($this->name);
        return "<a class='btn clone-event btn-xs btn-success grid-check-row' data-id='{$this->id}'>$btn</a>";
    }
    public function href()
    {
        return '/admin/events/clone/'.$this->id;
    }
    public function __toString()
    {
        return $this->render();
    }
}
