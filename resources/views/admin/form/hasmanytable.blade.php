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

                        <td class="hidden">{!! $hidden !!}</td>

                        @if($options['allowDelete'])
                            <td class="form-group">
                                <div>
                                    <div class="remove btn btn-warning btn-sm pull-right"><i
                                            class="fa fa-trash">&nbsp;</i>{{ trans('admin.remove') }}</div>
                                </div>
                            </td>
                        @endif
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
                                    class="fa fa-trash">&nbsp;</i>{{ trans('admin.remove') }}</div>
                        </div>
                    </td>
                </tr>
            </template>

            @if($options['allowCreate'])
                <div class="form-group">
                    <div class="{{$viewClass['field']}}">
                        <div class="add btn btn-success btn-sm"><i class="fa fa-save"></i>&nbsp;{{ trans('admin.new') }}
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<hr style="margin-top: 0px;">

<script>
    const categories = [...document.querySelectorAll('input[name="categories[values][]"]')].map(input => input.value);
    let inputs_for_categories = document.querySelector('.transfer_to_next_category.Категория.участника')

    for(var i = 0; i < categories.length; i++) {
        inputs_for_categories[i].text = categories[i]
    }
    let category = document.querySelectorAll('tbody[class="list-categories-table"]')
    category.forEach(function (category) {
        category.addEventListener('input', function () {
            const categories = [...document.querySelectorAll('input[name="categories[values][]"]')].map(input => input.value);
            let inputs_for_categories = document.querySelector('.transfer_to_next_category.Категория.участника')
            Array.from(inputs_for_categories).forEach((option) => {
                inputs_for_categories.removeChild(option)
            })
            for (var i = 0; i < categories.length; i++){
                var opt = document.createElement('option');
                opt.value = i;
                opt.innerHTML = categories[i];
                inputs_for_categories.appendChild(opt);
            }
        });
    });
    const radios = document.querySelectorAll('#choice_transfer');
    radios.forEach(function (radio) {
        radio.addEventListener('click', function () {
            const categories = [...document.querySelectorAll('input[name="categories[values][]"]')].map(input => input.value);
            let inputs_for_categories = document.querySelector('.transfer_to_next_category.Категория.участника')
            Array.from(inputs_for_categories).forEach((option) => {
                inputs_for_categories.removeChild(option)
            })
            for (var i = 0; i < categories.length; i++){
                var opt = document.createElement('option');
                opt.value = i;
                opt.innerHTML = categories[i];
                inputs_for_categories.appendChild(opt);
            }
        });
    });
    // let checkbox_choice_transfer = document.querySelector('input[name="choice_transfer"][value="2"]')
    // checkbox_choice_transfer.addEventListener('click', function() {
    //     console.log(111);
    // });
    // checkbox_choice_transfer.addEventListener('change', function() {
    //     console.log(111);
    // });
    // checkbox_choice_transfer.addEventListener('input', function() {
    //     console.log(111);
    // });
    // checkbox_choice_transfer.addEventListener('change', function () {
    //     console.log(2222)
    //    if(checkbox_choice_transfer.checked === true){
    //        const categories = [...document.querySelectorAll('input[name="categories[values][]"]')].map(input => input.value);
    //        let inputs_for_categories = document.querySelector('.transfer_to_next_category.Категория.участника')
    //        for(var i = 0; i < categories.length; i++) {
    //            inputs_for_categories[i].text = categories[i]
    //        }
    //    }
    // })
    // if( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ) {
    //     $(document).ready(function () {
    //         {
    //             const breakdownButton = document.querySelector('.input-group-addon');
    //             breakdownButton.style.display = "None";
    //         }
    //     });
    // }
</script>
