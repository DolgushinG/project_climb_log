
@if(count($events) == 0)
    <div class="event-wrap">
        <h5>Вы не принимали участие ни в каких соревнованиях</h5>
    </div>
@else
    @foreach($events as $event)

    <div class="accordion" id="accordion{{$event->id}}">
        <div class="accordion-item">
            <h2 class="accordion-header" id="heading{{$event->id}}">
                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{$event->id}}" aria-expanded="true" aria-controls="collapse{{$event->id}}">
                    {{$event->title}}
                </button>
            </h2>
            <div id="collapse{{$event->id}}" class="accordion-collapse collapse show" aria-labelledby="heading{{$event->id}}" data-bs-parent="#accordion{{$event->id}}">
                <div class="accordion-body">
                    <ul class="list-group">
                        @if($event->user_place == 'Нет результата')
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                {{$event->user_place}}
                            </li>
                        @else
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Место
                                <span class="badge bg-primary rounded-pill">{{$event->user_place}}</span>
                            </li>
                        @endif

                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Кол-во участников
                            <span class="badge bg-primary rounded-pill">{{$event->amount_participant}}</span>
                        </li>

                        @if($event->participant_active == "Внес результаты")
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span class="badge rounded-pill bg-success text-white">{{$event->participant_active}}</span>
                            </li>

                        @else
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span class="badge rounded-pill bg-success text-white">{{$event->participant_active}}</span>
                            </li>
                        @endif

                        @if(!\App\Models\ResultParticipant::participant_with_result(Auth()->user()->id, $event->id))
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <a href="{{route('listRoutesEvent', $event->title_eng)}}" class="btn btn-success">Внести результаты</a>
                            </li>
                        @else
                            @if($event->active)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <a class="badge rounded-pill bg-warning text-white" href="event/{{$event->climbing_gym_name_eng}}/{{$event->title_eng}}">Подробнее</a>
                                </li>
                            @endif
                        @endif
                    </ul><!-- End List With badges -->
                </div>
            </div>
        </div>
    </div>
  @endforeach

@endif

