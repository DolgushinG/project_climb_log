@if(!$event->is_access_user_edit_result && !$event->is_send_result_state)
    <a id="send_result" href="{{route('listRoutesEvent', [$event->id])}}"
       class="btn btn-secondary rounded-pill">Посмотреть внесенные результаты</a>
@endif

