<?php

namespace App\Admin\CustomAction;
use Encore\Admin\Actions\RowAction;
use Encore\Admin\Facades\Admin;

class ActionExport extends RowAction
{
    protected $id;
    public $name;
    public $format;

    public function __construct($id, $name, $format)
    {
        $this->id = $id;
        $this->name = $name;
        $this->format = $format;
    }

    protected function script($name, $format)
    {
        switch ($name){
            case 'final':
                switch ($format) {
                    case 'excel':
                        return <<<SCRIPT
                        $('.final-excel').on('click', function () {
                            let id = this.getAttribute('data-id');
                            document.location = 'exports/events/excel/final/' + id
                        });
                        SCRIPT;
                    case 'csv':
                        return <<<SCRIPT
                        $('.final-csv').on('click', function () {
                            let id = this.getAttribute('data-id');
                            document.location = 'exports/events/csv/final/' + id
                        });
                        SCRIPT;
                    case 'ods':
                        return <<<SCRIPT
                        $('.final-ods').on('click', function () {
                            let id = this.getAttribute('data-id');
                            document.location = 'exports/events/ods/final/' + id
                        });
                        SCRIPT;
                }
                break;
            case 'semifinal':
                switch ($format) {
                    case 'excel':
                          return <<<SCRIPT
                        $('.semifinal-excel').on('click', function () {
                            let id = this.getAttribute('data-id');
                            document.location = 'exports/events/excel/semifinal/' + id
                        });
                        SCRIPT;
                    case 'csv':
                        return <<<SCRIPT
                        $('.semifinal-csv').on('click', function () {
                            let id = this.getAttribute('data-id');
                            document.location = 'exports/events/csv/semifinal/' + id
                        });
                        SCRIPT;
                    case 'ods':
                        return <<<SCRIPT
                        $('.semifinal-ods').on('click', function () {
                            let id = this.getAttribute('data-id');
                            document.location = 'exports/events/ods/semifinal/' + id
                        });
                        SCRIPT;
                }
                break;
            case 'qualification':
                switch ($format) {
                    case 'excel':
                        return <<<SCRIPT
                        $('.qualification-excel').on('click', function () {
                            let id = this.getAttribute('data-id');
                            document.location = 'exports/events/excel/qualification/' + id
                        });
                    SCRIPT;
                    case 'csv':
                        return <<<SCRIPT
                        $('.qualification-csv').on('click', function () {
                            let id = this.getAttribute('data-id');
                            document.location = 'exports/events/csv/qualification/' + id
                        });
                        SCRIPT;
                    case 'ods':
                        return <<<SCRIPT
                        $('.qualification-ods').on('click', function () {
                            let id = this.getAttribute('data-id');
                            document.location = 'exports/events/ods/qualification/' + id
                        });
                        SCRIPT;
                }
                break;
            case 'all':
                switch ($format) {
                    case 'excel':
                        return <<<SCRIPT
                        $('.all-excel').on('click', function () {
                            let id = this.getAttribute('data-id');
                            document.location = 'exports/events/excel/all/' + id
                        });
                    SCRIPT;
                    case 'csv':
                        return <<<SCRIPT
                        $('.all-csv').on('click', function () {
                            let id = this.getAttribute('data-id');
                            document.location = 'exports/events/csv/all/' + id
                        });
                        SCRIPT;
                    case 'ods':
                        return <<<SCRIPT
                        $('.all-ods').on('click', function () {
                            let id = this.getAttribute('data-id');
                            document.location = 'exports/events/ods/all/' + id
                        });
                        SCRIPT;
                }
        }
    }

    public function render()
    {
        Admin::script($this->script($this->name, $this->format));
        $btn = strtoupper($this->format);
        return "<a class='btn report-participant btn-xs btn-success grid-check-row {$this->name}-{$this->format}' data-id='{$this->id}'>$btn</a>";
    }

    public function href()
    {
        return 'exports/events/'.$this->format.'/'.$this->name.'/'.$this->id;
    }
    public function __toString()
    {
        return $this->render();
    }
}
