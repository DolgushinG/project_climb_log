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
    let inputs_for_categories_all = document.querySelectorAll('select.form-control.options_categories.Категория.участника')
    let inputs_for_categories_all_2 = document.querySelectorAll('.form-control.options_categories.От.какой.категории.сложности.определять.эту.категорию')
    let inputs_for_categories_all_3 = document.querySelectorAll('.form-control.options_categories.До.какой.категории.сложности.определять.эту.категорию')
    let category = document.querySelectorAll('tbody[class="list-categories-table"]')
    let btn_add = document.querySelector('.add.btn.btn-success.btn-sm')
    btn_add.addEventListener('click', function () {
        if (document.URL.search('edit') === -1){
            setTimeout(function() {
                document.querySelector('.is_auto_categories1').click();
            }, 50);
            console.log(11)
        } else {
            setTimeout(function() {
                let cat_all = document.querySelectorAll('select.form-control.options_categories.Категория.участника')
                let inputs_for_categories_all_2 = document.querySelectorAll('.options_categories.От.какой.категории.сложности.определять.эту.категорию')
                let inputs_for_categories_all_3 = document.querySelectorAll('.options_categories.До.какой.категории.сложности.определять.эту.категорию')
                let inputs_for_categories_all_4 = document.querySelectorAll('.options_categories.Кол-во.трасс.для.опеределения')
                let btn_remove_for_categories_all_5 = document.querySelectorAll('.options_categories._remove_.fom-removed')
                for(let i = 0; i < cat_all.length; i++){
                    let new_i = i+1
                    cat_all[i].name = "options_categories[new_" + new_i + "][Категория участника]"
                    cat_all[i].previousSibling.previousElementSibling.name = "options_categories[new_" + new_i + "][Категория участника]"
                    inputs_for_categories_all_3[i].name = "options_categories[new_" + new_i + "][До какой категории сложности определять эту категорию]"
                    inputs_for_categories_all_2[i].name = "options_categories[new_" + new_i + "][От какой категории сложности определять эту категорию]"
                    inputs_for_categories_all_2[i].previousSibling.previousElementSibling.name = "options_categories[new_" + new_i + "][От какой категории сложности определять эту категорию]"
                    inputs_for_categories_all_3[i].previousSibling.previousElementSibling.name = "options_categories[new_" + new_i + "][До какой категории сложности определять эту категорию]"
                    inputs_for_categories_all_4[i].name = "options_categories[new_" + new_i + "][Кол-во трасс для опеределения]"
                    setTimeout(function() {
                        btn_remove_for_categories_all_5[i].name = 'options_categories[new_'+new_i+'][_remove_]'

                    }, 50);

                }
            }, 150);
        }

    });
    if (document.URL.search('edit') === -1) {
        setTimeout(function () {
            for (let i = 0; i < categories.length; i++) {
                inputs_for_categories_all.forEach(function (category) {
                    try {
                        category[i].text = categories[i]
                    } catch (e) {
                        category[i].value = categories[i]
                    }
                });
                inputs_for_categories_all_2.forEach(function (category) {
                    try {
                        category[i].text = categories[i]
                    } catch (e) {
                        category[i].value = categories[i]
                    }
                });
            }
        }, 50);
    }


    category.forEach(function (category) {
        category.addEventListener('input', function () {
            let inputs_for_categories_all = document.querySelectorAll('select.form-control.options_categories.Категория.участника')
            inputs_for_categories_all.forEach((category) => {
                Array.from(category).forEach((option) => {
                    category.removeChild(option)
                })
            })
            inputs_for_categories_all.forEach((category) => {
                update_list('input[name="categories[values][]"]', category)
            })
        });
    });

    const radios_auto_categories = document.querySelector('[id="is_auto_categories"][class="is_auto_categories1"]');
    if(radios_auto_categories){
        radios_auto_categories.addEventListener('click', function () {
            let inputs_for_categories_all = document.querySelectorAll('select.form-control.options_categories.Категория.участника')
            inputs_for_categories_all.forEach((category) => {
                remove_option(category)
            })
            inputs_for_categories_all.forEach((category) => {
                update_list('input[name="categories[values][]"]', category)
            })
        });
    }


    function update_list(element, category){
        const categories = [...document.querySelectorAll(element)].map(input => input.value);
        for (let i = 0; i < categories.length; i++){
            let opt = document.createElement('option');
            opt.value = i;
            opt.innerHTML = categories[i];
            category.appendChild(opt);
        }
    }
    function remove_option(category){
        Array.from(category).forEach((option) => {
            if(option.text === "1"){
                category.removeChild(option)
            }
        })
    }
    if( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ) {
        $(document).ready(function () {
            {
                document.getElementsByTagName('body')[0].style.fontSize = "12px"
            }
        });
    }
</script>


