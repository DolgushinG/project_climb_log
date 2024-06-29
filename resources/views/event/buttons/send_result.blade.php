@if($event->is_access_user_edit_result)
    <a id="send_result" href="{{route('listRoutesEvent', [$event->start_date, $event->climbing_gym_name_eng, $event->title_eng])}}"
       class="btn btn-success rounded-pill">Изменить результаты</a>
@else
    <a id="send_result" href="{{route('listRoutesEvent', [$event->start_date, $event->climbing_gym_name_eng, $event->title_eng])}}"
       class="btn btn-success rounded-pill">Внести результаты</a>
@endif

