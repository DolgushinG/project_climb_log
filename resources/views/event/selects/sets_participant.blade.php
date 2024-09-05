@include('event.selects.birthday')
<div class="form-floating mb-3">
    <select class="form-select" id="floatingSelectChangeSet"
            aria-label="Floating label select example" autocomplete="off" required>
        @foreach($sets as $set)
            @php
                $number_set = \App\Models\ResultQualificationClassic::participant_number_set(Auth()->user()->id, $event->id);
            @endphp

                @if($set->number_set === $number_set)
                    <option data-set="current" data-free="{{$set->free}}" selected value="{{$set->number_set}}">
                        Сет {{$set->number_set}} @lang('somewords.'.$set->day_of_week)
                        @isset($set->date[$set->day_of_week])
                            {{$set->date[$set->day_of_week]}}
                        @endisset
                        {{$set->time}} (Ваш сет)
                    </option>
                @else
                    @if(Auth::user()->birthday && App\Helpers\Helpers::is_valid_year_for_event($event->id, $set->number_set, Auth::user()->birthday))
                        @if($set->free > 0)
                            <option data-set="" data-free="{{$set->free}}" value="{{$set->number_set}}">Сет {{$set->number_set}}
                                @lang('somewords.'.$set->day_of_week)
                                @isset($set->date[$set->day_of_week])
                                    {{$set->date[$set->day_of_week]}}
                                @endisset
                                {{$set->time}} (еще
                                мест {{$set->free}})
                            </option>
                        @else
                            <option data-set="" data-free="{{$set->free}}" value="{{$set->number_set}}">Сет {{$set->number_set}}
                                @lang('somewords.'.$set->day_of_week)
                                @isset($set->date[$set->day_of_week])
                                    {{$set->date[$set->day_of_week]}}
                                @endisset
                                {{$set->time}} (мест нет)
                            </option>
                        @endif
                    @endif
               @endif
        @endforeach
    </select>
    <label for="floatingSelectChangeSet">Выбрать время для сета</label>
</div>
<button id="btn-participant-change-set" data-id="{{$event->id}}"
        data-user-id="{{Auth()->user()->id}}"
        class="btn btn-dark rounded-pill">Изменить сет
</button>
