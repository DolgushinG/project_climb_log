<div class="tab-pane fade active show pt-3" id="tab-events">
@if(count($events) == 0)
    <div class="event-wrap">
        <h5>Вы еще не принимали участие в соревнованиях</h5>
    </div>
@else
    @foreach($events as $event)
    <div class="accordion mb-2" id="accordion{{$event->id}}">
        <div class="accordion-item">
            <h2 class="accordion-header" id="heading{{$event->id}}">
                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{$event->id}}" aria-expanded="true" aria-controls="collapse{{$event->id}}">
                    {{$event->title}}
                </button>
            </h2>
            <div id="collapse{{$event->id}}" class="accordion-collapse collapse" aria-labelledby="heading{{$event->id}}" data-bs-parent="#accordion{{$event->id}}">
                <div class="accordion-body">
                    <ul class="list-group">
                        @if($event->user_qualification_place)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                 <span class="badge bg-info rounded-pill">[КВАЛИФИКАЦИЯ] Место </span>
                                <span class="badge bg-primary rounded-pill">{{$event->user_qualification_place}}</span>
                            </li>
                        @endif
                        @if($event->is_semifinal)
                            @if($event->user_semifinal_place)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span class="badge bg-info rounded-pill">[ПОЛУФИНАЛ] Место</span>
                                    <span class="badge bg-primary rounded-pill">{{$event->user_semifinal_place}}</span>
                                </li>
                            @endif
                        @endif
                        @if($event->user_final_place)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span class="badge bg-info rounded-pill">[ФИНАЛ] Место</span>
                                <span class="badge bg-primary rounded-pill">{{$event->user_final_place}}</span>
                            </li>
                        @endif

                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Кол-во участников
                            <span class="badge bg-primary rounded-pill">{{$event->amount_participant}}</span>
                        </li>

                        @if(!$event->is_send_result_state && $event->is_open_public_analytics)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <a href="{{route('index_analytics', [$event->id])}}"
                                   class="btn btn-warning rounded-pill">Статистика</a>
                            </li>
                        @endif
                        @if(!$event->is_france_system_qualification &&\App\Models\User::user_participant($event->id) && \App\Models\ResultRouteQualificationClassic::participant_with_result(Auth()->user()->id, $event->id))
                            @if(!$event->is_access_user_edit_result && !$event->is_send_result_state)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    @include('event.buttons.show_sent_result')
                                </li>
                            @endif
                        @endif
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <a href="{{$event->new_link ?? $event->link}}" class="btn btn-success rounded-pill">Перейти на страницу с соревнованием</a>
                        </li>
                    </ul><!-- End List With badges -->
                </div>
            </div>
        </div>
    </div>
  @endforeach

@endif
</div>
