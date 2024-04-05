
@php($listErrorKey = "$column.values")

<div class="{{$viewClass['form-group']}} {{ $errors->has($listErrorKey) ? 'has-error' : '' }}">

    <label class="{{$viewClass['label']}} control-label">{{$label}}</label>

    <div class="{{$viewClass['field']}}">

        @if($errors->has($listErrorKey))
            @foreach($errors->get($listErrorKey) as $message)
                <label class="control-label" for="inputError"><i class="fa fa-times-circle-o"></i> {{$message}}</label><br/>
            @endforeach
        @endif

        <table class="table table-hover">

            <tbody class="list-{{$column}}-table">

            @foreach(old("{$column}.values", ($value ?: [])) as $k => $v)

                @php($itemErrorKey = "{$column}.values.{$loop->index}")

                <tr>
                    <td>
                        <div class="form-group {{ $errors->has($itemErrorKey) ? 'has-error' : '' }}">
                            <div class="col-sm-12">
                                <input name="{{ $column }}[values][]" value="{{ old("{$column}.values.{$k}", $v) }}" class="form-control" />
                                @if($errors->has($itemErrorKey))
                                    @foreach($errors->get($itemErrorKey) as $message)
                                        <label class="control-label" for="inputError"><i class="fa fa-times-circle-o"></i> {{$message}}</label><br/>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td></td>
                    <td>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
    <template class="{{$column}}-tpl">
        <tr>
            <td>
                <div class="form-group">
                    <div class="col-sm-12">
                        <input name="{{ $column }}[values][]" class="form-control" />
                    </div>
                </div>
            </td>
        </tr>
    </template>
</div>
