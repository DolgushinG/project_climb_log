<button id="btn-participant" data-id="{{$event->id}}"
        data-link="{{$event->link}}"
        data-sets="{{$event->is_need_set}}"
        data-format="{{$event->is_france_system_qualification}}" data-user-id="{{Auth()->user()->id}}"
        class="btn btn-dark rounded-pill">Участвовать</button>
