<?php

namespace App\Admin\Extensions;


class Links
{
    public function __toString()
    {
        return <<<HTML


<li>
    <a href="#">
      <i class="fa fa-bell-o"></i>
      <span class="label label-warning">7</span>
    </a>
</li>

<!--<li>-->
<!--    <a href="#">-->
<!--      <i class="fa fa-flag-o"></i>-->
<!--      <span class="label label-danger">9</span>-->
<!--    </a>-->
<!--</li>-->
<li>
    <a href="/admin/events/create">
      <i class="fa fa-flag-o"></i>
      Создать соревнование
    </a>
</li>

HTML;
    }
}
