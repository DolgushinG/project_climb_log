<div class="box box-default">
    <div class="box-header with-border">
        <h3 class="box-title">Запоняемость сетов</h3>

        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
            </button>
            <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
        </div>
    </div>

    <!-- /.box-header -->
    <div class="box-body sets">
        @foreach($sets as $set)
            @if($set->free != 0)
                <label style="font-size: 12px">Сет {{$set->number_set}}-{{$set->time}}
                    (@lang('somewords.'.$set->day_of_week))(Свободно
                    - {{100 - $set->procent}}%)</label>
                <div class="progress mt-1">
                    <div class="progress-bar" role="progressbar"
                         style="width: {{$set->procent}}%" aria-valuenow="{{$set->free}}"
                         aria-valuemin="0" aria-valuemax="{{$set->max_participants}}"></div>
                </div>
            @else
                <label>Сет {{$set->number_set}}-{{$set->time}}
                    (@lang('somewords.'.$set->day_of_week)) (Полностью забит)</label>
                <div class="progress mt-1">
                    <div class="progress-bar" role="progressbar"
                         style="width: {{$set->procent}}%" aria-valuenow="{{$set->free}}"
                         aria-valuemin="0" aria-valuemax="{{$set->max_participants}}"></div>
                </div>
            @endif
        @endforeach
        <!-- /.table-responsive -->
    </div>
    <!-- /.box-body -->
</div>

<script>
    $('.sets').slimscroll({height:'510px',size:'2px'});
</script>
