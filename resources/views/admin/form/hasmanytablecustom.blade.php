<style>
    td .form-group {
        margin-bottom: 0 !important;
    }
</style>

<div class="row">
    <div class="{{$viewClass['label']}}"><h4 class="pull-right">{{ $label }}</h4></div>
    <div class="{{$viewClass['field']}}">
        <div class="col-sm-12">

            <label>Кол-во трасс в сумме</label>
            <label id="count_routes_label" class="form-control">30</label>
        </div>
        <div id="has-many-{{$column}}" style="margin-top: 15px; width: 35%;">
            <table class="table table-has-many has-many-{{$column}}">
                <thead>
                <tr>
                    @foreach($headers as $header)
                        @if($header === " remove ")
                            <th></th>
                        @else
                        <th>{{ $header }}</th>
                        @endif
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

                            @if (is_a($field, \App\Admin\Form\Hidden::class))
                                    <?php $hidden .= $field->render(); ?>
                                @continue
                            @endif

                            <td>{!! $field->setLabelClass(['hidden'])->setWidth(12, 0)->render() !!}</td>
                        @endforeach

{{--                        <td class="hidden">{!! $hidden !!}</td>--}}

{{--                        @if($options['allowDelete'])--}}
{{--                            <td class="form-group">--}}
{{--                                <div>--}}
{{--                                    <div class="remove btn btn-warning btn-sm pull-right"><i--}}
{{--                                            class="fa fa-trash">&nbsp;</i>{{ trans('admin.remove') }}</div>--}}
{{--                                </div>--}}
{{--                            </td>--}}
{{--                        @endif--}}
                    </tr>
                @endforeach
                </tbody>
            </table>
            <script>
                var table = document.getElementById('has-many-grade_and_amount');
                var label = document.getElementById('count_routes_label');
                updateTotal()
                $('a[data-toggle="tab"]').on('click', function() {
                    // Проверяем, содержит ли ссылка атрибут href
                    if ($(this).attr('href')) {
                        updateTotal(); // Выполняем метод update с передачей ID вкладки
                    }
                });
                // Функция для обновления суммы
                function updateTotal() {
                    // Проходим по всем элементам в таблице
                    let results = [...document.querySelectorAll('.Кол-во')].map(input => Number(input.value)).reduce(function (currentSum, currentNumber) {
                        return currentSum + currentNumber
                    }, 0)
                    document.getElementById('count_routes_label').textContent = results;
                }

                // Добавляем обработчики для кнопок и инпутов
                table.addEventListener('click', function(event) {
                    updateTotal();
                });

                table.addEventListener('input', function(event) {
                    if (event.target.classList.contains('input')) {
                        updateTotal();
                    }
                });
                {{--const breakdownButton = document.querySelectorAll('.Кол-во');--}}
                {{--breakdownButton.forEach(function (btn) {--}}
                {{--    btn.addEventListener('input', function () {--}}
                {{--        let results = [...document.querySelectorAll('.Кол-во')].map(input => Number(input.value)).reduce(function (currentSum, currentNumber) {--}}
                {{--            return currentSum + currentNumber--}}
                {{--        }, 0)--}}
                {{--        document.getElementById('count_routes_label').textContent = results;--}}
                {{--    });--}}
                {{--});--}}
                {{--const input_group_btn = document.querySelectorAll("//button[contains(@class, 'btn btn-primary')]");--}}
                {{--input_group_btn.forEach(function (btn) {--}}
                {{--    console.log(12)--}}
                {{--    btn.addEventListener('click', function () {--}}
                {{--        let results = [...document.querySelectorAll('.Кол-во')].map(input => Number(input.value)).reduce(function (currentSum, currentNumber) {--}}
                {{--            return currentSum + currentNumber--}}
                {{--        }, 0)--}}
                {{--        document.getElementById('count_routes_label').textContent = results;--}}
                {{--    });--}}
                {{--});--}}

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
                            <div class="remove btn btn-warning btn-sm pull-right"><i
                                    class="fa fa-trash">&nbsp;</i>{{ trans('admin.remove') }}</div>
                        </div>
                    </td>
                </tr>
            </template>

{{--            @if($options['allowCreate'])--}}
{{--                <div class="form-group">--}}
{{--                    <div class="{{$viewClass['field']}}">--}}
{{--                        <div class="add btn btn-success btn-sm"><i class="fa fa-save"></i>&nbsp;{{ trans('admin.new') }}--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            @endif--}}
        </div>
    </div>
</div>

<hr style="margin-top: 0px;">

