@if($event->is_input_set != 1)
    <div class="form-floating mb-3">
        <select class="form-select" id="floatingSelectChangeSet"
                aria-label="Floating label select example" autocomplete="off" required>
            @foreach($sets as $set)
                @php
                    $number_set = \App\Models\Participant::participant_number_set(Auth()->user()->id, $event->id);
                @endphp
                @if($set->number_set === $number_set)
                    <option selected value="{{$set->number_set}}">Сет {{$set->number_set}} @lang('somewords.'.$set->day_of_week)
                        @isset($set->date[$set->day_of_week])
                            {{$set->date[$set->day_of_week]}}
                        @endisset
                        {{$set->time}} (еще мест {{$set->free}})</option>
                @else
                    @if($set->free != 0)
                        <option value="{{$set->number_set}}">Сет {{$set->number_set}}
                            @lang('somewords.'.$set->day_of_week)
                            @isset($set->date[$set->day_of_week])
                                {{$set->date[$set->day_of_week]}}
                            @endisset
                            {{$set->time}} (еще
                            мест {{$set->free}})
                        </option>
                    @endif
                @endif
            @endforeach
        </select>
        <label for="floatingSelectChangeSet">Выбрать время для сета</label>
    </div>
    <button id="btn-participant-change-set" data-id="{{$event->id}}"
            data-title="{{$event->title_eng}}" data-user-id="{{Auth()->user()->id}}"
            class="btn btn-dark rounded-pill">Изменить сет</button>
@endif
