<style>
    td .form-group {
        margin-bottom: 0 !important;
    }
</style>

<div class="row">
    <div class="{{$viewClass['label']}}"><h4 class="pull-right">{{ $label }}</h4></div>
    <div class="{{$viewClass['field']}}">
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
                <script>
                    if( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ) {
                        $(document).ready(function () {
                            {
                                document.getElementsByTagName('body')[0].style.fontSize = "12px"
                            }
                        });
                    }
                </script>
                <tbody class="has-many-{{$column}}-forms">
                @foreach($forms as $pk => $form)
                    <tr class="has-many-{{$column}}-form fields-group">

                            <?php $hidden = ''; ?>
                        @foreach($form->fields() as $field)

                            @if (is_a($field, \App\Admin\Form\Hidden::class))
                                    <?php $hidden .= $field->render(); ?>
                                @continue
                            @endif

                            <td>{!! $field->setLabelClass(['hidden'])->setWidth(12, 0)->render() !!}</td>
                        @endforeach
                        @if($options['allowDelete'])
                            <td class="form-group">
                                <div>
                                    <div class="remove btn btn-warning btn-sm pull-right"><i
                                                class="fa fa-trash">&nbsp;</i></div>
                                </div>
                            </td>
                        @endif
                        <td class="hidden">{!! $hidden !!}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            <template class="{{$column}}-tpl">
                <tr class="has-many-{{$column}}-form fields-group">

                    {!! $template !!}
                    <td class="form-group">
                        <div>
                            <div class="remove btn btn-warning btn-sm pull-right"><i
                                    class="fa fa-trash">&nbsp;</i></div>
                        </div>
                    </td>
                </tr>
            </template>

            @if($options['allowCreate'])
                <div class="form-group">
                    <div class="{{$viewClass['field']}}">
                        <div class="add-amount btn btn-success btn-sm"><i class="fa fa-save"></i>&nbsp;{{ trans('admin.new') }}
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<hr style="margin-top: 0px;">

<script>
    let inputs_for_amount_price_all = document.querySelectorAll('select.form-control.options_amount_price.Название')
    let inputs_for_amount_price_all_2 = document.querySelectorAll('.form-control.options_amount_price.Сумма')
    let inputs_for_amount_price_all_3 = document.querySelectorAll('.form-control.options_amount_price.Ссылка.на.оплату')
    let btn_add = document.querySelector('.add-amount.btn.btn-success.btn-sm')
    btn_add.addEventListener('click', function () {
        setTimeout(function() {
            let amount_all = document.querySelectorAll('input.form-control.options_amount_price.Название')
            let inputs_for_amount_price_all_2 = document.querySelectorAll('input.form-control.options_amount_price.Сумма')
            let inputs_for_amount_price_all_3 = document.querySelectorAll('input.form-control.options_amount_price.Ссылка.на.оплату')
            let btn_remove_for_amount_all_5 = document.querySelectorAll('.options_amount_price._remove_.fom-removed')
            for(let i = 0; i < amount_all.length; i++){
                let new_i = i+1
                amount_all[i].name = "options_amount_price[new_" + new_i + "][Название]"
                inputs_for_amount_price_all_2[i].name = "options_amount_price[new_" + new_i + "][Сумма]"
                inputs_for_amount_price_all_3[i].name = "options_amount_price[new_" + new_i + "][Ссылка на оплату]"
                setTimeout(function() {
                    btn_remove_for_amount_all_5[i].name = 'options_amount_price[new_'+new_i+'][_remove_]'
                }, 50);
            }
        }, 150);

    });
    if( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ) {
        $(document).ready(function () {
            {
                document.getElementsByTagName('body')[0].style.fontSize = "12px"
            }
        });
    }
</script>


