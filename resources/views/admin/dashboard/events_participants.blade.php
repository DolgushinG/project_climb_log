<div class="box box-default">
    <div class="box-header with-border">
        <h3 class="box-title">Кол-во участников</h3>

        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
            </button>
            <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
        </div>
    </div>

    <!-- /.box-header -->
    <div class="box-body events">
        <div class="table-responsive">
            <table class="table table-striped">
                <td>Название</td>
                <td>Кол-во участников</td>
                @if($events)
                    @foreach($events as $event)
                    <tr>
                        <td>{{ $event->title }}</td>
                        <td><span class="label label-primary">{{ $event->count_participant }}</span></td>
                    </tr>
                    @endforeach
                @endif
            </table>
        </div>
        <!-- /.table-responsive -->
    </div>
    <!-- /.box-body -->
</div>

<script>
    $('.events').slimscroll({height:'200px',size:'1px'});
</script>
