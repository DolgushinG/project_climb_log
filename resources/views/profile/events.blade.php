<div class="tab-pane fade active show pt-3" id="tab-events">
@if(count($events) == 0)
    <div class="event-wrap">
        <h5>Вы не принимали участие ни в каких соревнованиях</h5>
    </div>
@else
    @foreach($events as $event)
{{--        <div class="alert alert-info alert-dismissible" role="alert">--}}
{{--            <button type="button" class="close" data-dismiss="alert">×</button>--}}
{{--            <div class="alert-icon">--}}
{{--                <i class="icon-info"></i>--}}
{{--            </div>--}}
{{--            <div class="alert-message">--}}
{{--                <span><strong>Info!</strong> Lorem Ipsum is simply dummy text.</span>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--        <table class="table table-hover table-striped">--}}
{{--            <tbody>--}}
{{--            <tr>--}}
{{--                <td>--}}
{{--                    <span class="float-right font-weight-bold">3 hrs ago</span> Here is your a link to the latest summary report from the..--}}
{{--                </td>--}}
{{--            </tr>--}}
{{--            <tr>--}}
{{--                <td>--}}
{{--                    <span class="float-right font-weight-bold">Yesterday</span> There has been a request on your account since that was..--}}
{{--                </td>--}}
{{--            </tr>--}}
{{--            <tr>--}}
{{--                <td>--}}
{{--                    <span class="float-right font-weight-bold">9/10</span> Porttitor vitae ultrices quis, dapibus id dolor. Morbi venenatis lacinia rhoncus.--}}
{{--                </td>--}}
{{--            </tr>--}}
{{--            <tr>--}}
{{--                <td>--}}
{{--                    <span class="float-right font-weight-bold">9/4</span> Vestibulum tincidunt ullamcorper eros eget luctus.--}}
{{--                </td>--}}
{{--            </tr>--}}
{{--            <tr>--}}
{{--                <td>--}}
{{--                    <span class="float-right font-weight-bold">9/4</span> Maxamillion ais the fix for tibulum tincidunt ullamcorper eros.--}}
{{--                </td>--}}
{{--            </tr>--}}
{{--            </tbody>--}}
{{--        </table>--}}
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

                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <a href="event/{{$event->climbing_gym_name_eng}}/{{$event->title_eng}}" class="btn btn-success">Перейти на страницу с соревнованием</a>
                        </li>
                    </ul><!-- End List With badges -->
                </div>
            </div>
        </div>
    </div>
  @endforeach

@endif
</div>
