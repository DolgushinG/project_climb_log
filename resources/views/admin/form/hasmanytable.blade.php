<style>
    td .form-group {
        margin-bottom: 0 !important;
    }
</style>

<div class="row">
    <div class="{{$viewClass['label']}}"><h4 class="pull-right">{{ $label }}</h4></div>
    <div class="{{$viewClass['field']}}">
        <div class="col-sm-12">
            <label id="count_routes_label" class="form-control">30</label>
        </div>
        <div id="has-many-{{$column}}" style="margin-top: 15px;">
            <table class="table table-has-many has-many-{{$column}}">
                <thead>
                <tr>
                    @foreach($headers as $header)
                        <th>{{ $header }}</th>
                    @endforeach

                    <th class="hidden"></th>

                    @if($options['allowDelete'])
                        <th></th>
                    @endif
                </tr>
                </thead>
                <tbody class="has-many-{{$column}}-forms">
                @foreach($forms as $pk => $form)
                    <tr class="has-many-{{$column}}-form fields-group count_routes_input">

                        <?php $hidden = ''; ?>

                        @foreach($form->fields() as $field)

                            @if (is_a($field, \Encore\Admin\Form\Field\Hidden::class))
                                <?php $hidden .= $field->render(); ?>
                                @continue
                            @endif

                            <td>{!! $field->setLabelClass(['hidden'])->setWidth(12, 0)->render() !!}</td>
                        @endforeach

                        <td class="hidden">{!! $hidden !!}</td>

                        @if($options['allowDelete'])
                            <td class="form-group">
                                <div>
                                    <div class="remove btn btn-warning btn-sm pull-right"><i class="fa fa-trash">&nbsp;</i>{{ trans('admin.remove') }}</div>
                                </div>
                            </td>
                        @endif
                    </tr>
                @endforeach
                </tbody>
            </table>
            <script>
                const breakdownButton = document.querySelectorAll('.Кол-во');
                let results = [...document.querySelectorAll('.Кол-во')].map(input => Number(input.value)).reduce(function (currentSum, currentNumber) {
                    return currentSum + currentNumber
                }, 0)
                document.getElementById('count_routes_label').textContent = results
                breakdownButton.forEach(function(btn) {
                    btn.addEventListener('input', function() {
                        let results = [...document.querySelectorAll('.Кол-во')].map(input => Number(input.value)).reduce(function (currentSum, currentNumber) {
                            return currentSum + currentNumber
                        }, 0)
                        let count_routes = document.querySelector('#count_routes').value
                        if (Number(count_routes) !== results){
                            document.getElementById('count_routes_label').style.background = 'red';
                        } else {
                            document.getElementById('count_routes_label').style.background = 'white';
                        }
                        document.getElementById('count_routes_label').textContent = results;

                    });
                });
                // if( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ) {
                //     $(document).ready(function () {
                //         {
                //             const breakdownButton = document.querySelector('.input-group-addon');
                //             breakdownButton.style.display = "None";
                //         }
                //     });
                // }
            </script>
            <template class="{{$column}}-tpl">
                <tr class="has-many-{{$column}}-form fields-group">

                    {!! $template !!}

                    <td class="form-group">
                        <div>
                            <div class="remove btn btn-warning btn-sm pull-right"><i class="fa fa-trash">&nbsp;</i>{{ trans('admin.remove') }}</div>
                        </div>
                    </td>
                </tr>
            </template>

            @if($options['allowCreate'])
                <div class="form-group">
                    <div class="{{$viewClass['field']}}">
                        <div class="add btn btn-success btn-sm"><i class="fa fa-save"></i>&nbsp;{{ trans('admin.new') }}</div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<hr style="margin-top: 0px;">

