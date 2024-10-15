<?php
namespace App\Admin\Controllers;

use App\Admin\Actions\ResultRoute\BatchBaseRoute;
use App\Http\Controllers\Controller;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Layout\Content;
use App\Models\Event;
use App\Models\Set;
use App\Models\ParticipantCategory;

class CustomFormQualificationController extends Controller
{
    public function index(Content $content)
    {
        $content->body(CustomFormFinalController::customForm('qualification'));
        return $content;
    }

}
