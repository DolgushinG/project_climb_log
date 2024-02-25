
<div class="tab-pane fade show active profile-overview" id="tab-overview">
        <div class="row">
            <div class="col-md-6">
                <h6>Команда</h6>
                <p>
                    {{$user->team  ?? ''}}
                </p>
                <h6>Пол</h6>
                <p>
                    @lang('somewords.'.$user->gender  ?? '')
                </p>
            </div>
            <div class="col-md-6">
                <h6>Recent badges</h6>
                <span class="badge bg-primary"><i class="fa fa-user"></i> 900 Участие в соревнованиях</span>
                <span class="badge bg-success"><i class="fa fa-cog"></i> 43 Маскимальная Категория</span>
                <span class="badge bg-danger"><i class="fa fa-eye"></i> 245 Лучшие место</span>
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
