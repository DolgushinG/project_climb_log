@if($event->is_access_user_edit_result && \App\Models\ResultRouteQualificationClassic::participant_with_result(Auth()->user()->id, $event->id))
    <a id="send_result" href="{{route('listRoutesEvent', [$event->city, $event->start_date, $event->id])}}"
       class="btn btn-success rounded-pill">Изменить результаты</a>
@else
    <a id="send_result" href="{{route('listRoutesEvent', [$event->city, $event->start_date, $event->id])}}"
       class="btn btn-success rounded-pill">Внести результаты</a>
@endif

