
<div class="tab-pane fade show active profile-overview" id="tab-overview">
        <div class="row">
            <div class="col-md-6">
                <h6>Команда</h6>
                <p>
                    @if($user->team)
                        {{$user->team}}
                    @else
                        Не указан
                    @endif

                </p>
                <h6>Пол</h6>
                <p>
                    @if($user->gender)
                        @lang('somewords.'.$user->gender)
                    @else
                        Не указан
                    @endif
                </p>
                <h6>Год рождения</h6>
                <p>
                    @if($user->birthday)
                        {{$user->birthday}}
                    @else
                        Не указан
                    @endif
                </p>
                <h6>Разряд</h6>
                <p>
                    @if($user->sport_category)
                        {{$user->sport_category}}
                    @else
                        Не указана
                    @endif
                </p>
            </div>
            <div class="col-md-6">
                <h6>Общая статистика</h6>
                <span class="badge bg-primary"><i class="fa fa-user"></i> Участие в соревнованиях </span>
                <span class="badge bg-primary">{{$state_participant['amount_event'] ?? ''}}</span>
            </div>
            <div class="col-md-12">
                <h5 class="mt-2 mb-3"><span class="fa fa-clock-o ion-clock float-right"></span> Недавняя активность</h5>
                <table class="table table-hover table-striped">
                    <tbody>
                    @foreach($activities as $activity)
                        <tr>
                            <td>
                                <strong>{{$activity->description}} - {{$activity->updated_at}}</strong>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
</div>
