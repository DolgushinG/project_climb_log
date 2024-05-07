<?php

namespace App\Admin\Extensions;


use Encore\Admin\Facades\Admin;

class Links
{

    public function __toString()
    {
        if (Admin::user()->is_access_to_create_event) {
            return <<<HTML
            <!--            <li>-->
            <!--                <a href="#">-->
            <!--                  <i class="fa fa-bell-o"></i>-->
            <!--                  <span class="label label-warning">7</span>-->
            <!--                </a>-->
            <!--            </li>-->
            <!--<li>-->
            <!--    <a href="#">-->
            <!--      <i class="fa fa-flag-o"></i>-->
            <!--      <span class="label label-danger">9</span>-->
            <!--    </a>-->
            <!--</li>-->
            <li>
                <a href="/admin/events/create" id="create-events-link">
                  <i class="fa fa-flag-o"></i>
                  Создать соревнование
                </a>
            </li>

            HTML;
        } else {
            return '';
        }

    }
}
