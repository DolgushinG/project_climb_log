<?php

namespace App\Admin\CustomAction;
use Encore\Admin\Actions\RowAction;
use Encore\Admin\Facades\Admin;

class ActionCustomDelete extends RowAction
{
    protected $id;

    public function __construct($id, $name)
    {
        $this->id = $id;
        $this->name = $name;
    }

    protected function script($form)
    {
        switch ($form){
            case 'place_routes':
                return <<<SCRIPT
                            $('.delete').on('click', function () {
                                let id = this.getAttribute('data-id');
                                 $.ajax({
                                        method: 'GET' ,
                                        url: '/admin/place_routes/delete/'+ id,
                                        success: function (data) {
                                           location.reload()
                                        },
                                        error:function(request){

                                        }
                                    });
                            });
                            SCRIPT;
            case 'areas':
                return <<<SCRIPT
                                $('.delete').on('click', function () {
                                    let id = this.getAttribute('data-id');
                                     $.ajax({
                                            method: 'GET' ,
                                            url: '/admin/areas/delete/'+ id,
                                            success: function (data) {
                                               location.reload()
                                            },
                                            error:function(request){

                                            }
                                        });
                                });
                                SCRIPT;
            case 'places':
                return <<<SCRIPT
                            $('.delete').on('click', function () {
                                let id = this.getAttribute('data-id');
                                 $.ajax({
                                        method: 'GET' ,
                                        url: '/admin/place/delete/'+ id,
                                        success: function (data) {
                                           location.reload()
                                        },
                                        error:function(request){

                                        }
                                    });
                            });
                            SCRIPT;
        }

    }

    public function render()
    {
        Admin::script($this->script($this->name));
        return "<a class='btn delete btn-xs btn-danger grid-check-row' data-id='{$this->id}'><i class='fa fa-trash'></i></a>";
    }
    public function __toString()
    {
        return $this->render();
    }
}
