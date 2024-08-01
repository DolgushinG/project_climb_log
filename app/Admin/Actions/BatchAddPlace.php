<?php

namespace App\Admin\Actions;

use App\Models\Country;
use App\Models\Event;
use App\Models\Place;
use Encore\Admin\Actions\Action;
use Encore\Admin\Facades\Admin;
use Illuminate\Http\Request;

class BatchAddPlace extends Action
{
    public $name = 'Добавить Место';

    protected $selector = '.place';

    public function handle(Request $request)
    {
        if($request->input('country')){
            $id = $request->input('country')['id'];
            $name = $request->input('name');
            $model = Place::where('country_id', $id)->where('name', $name)->first();
            if(!$model){
                $model = new Place;
                $model->name = $name;
                $model->country_id = $id;
                $model->save();
                return $this->response()->success('Готово')->refresh();
            }
        }
    }

    public function form()
    {
        $this->modalSmall();
        $this->select('country.id', 'Страна')->options(Country::all()->pluck('name', 'id'))->required();
        $this->text('name', 'Место')->required();
    }

    public function html()
    {
        return "<a class='place btn btn-sm btn-primary'><i class='fa fa-arrow-up'></i>{$this->name}</a>
                <style>
                .place {margin-top:8px;}
                 @media screen and (max-width: 767px) {
                        .place {margin-top:8px;}
                    }
                </style>
            ";
    }
    private function modalSelect2Script()
    {
        return <<<SCRIPT
            $('body').on('shown.bs.modal', '.modal-backdrop', function() {
            $(this).find('select2').each(function() {
                var dropdownParent = $(document.body);
                if ($(this).parents('.modal.in:first').length !== 0)
                    dropdownParent = $(this).parents('.modal.in:first');
                    $(this).select2({
                        dropdownParent: dropdownParent
                    });
                });
            });
SCRIPT;

    }
    public function grid()
    {
        Admin::script($this->modalSelect2Script());
    }


}
