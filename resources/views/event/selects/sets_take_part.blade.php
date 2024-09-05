@if($event->is_input_set != 1)
    <div class="form-floating mb-3">
        <select class="form-select" id="floatingSelect"
                aria-label="Floating label select example" required>
            <option selected disabled value="">Открыть для выбора сета</option>
            @foreach($sets as $set)

                @if(Auth::user()->birthday)
                    @if(App\Helpers\Helpers::is_valid_year_for_event($event->id, $set->number_set, Auth::user()->birthday))
                        @if($set->free > 0)
                            <option data-free="{{$set->free}}" value="{{$set->number_set}}">Сет {{$set->number_set}}
                                @lang('somewords.'.$set->day_of_week)
                                @isset($set->date[$set->day_of_week])
                                    {{$set->date[$set->day_of_week]}}
                                @endisset
                                {{$set->time}} (еще
                                мест {{$set->free}})
                            </option>
                        @endif
                    @endif
                @else
                    @if($set->free > 0)
                        <option data-free="{{$set->free}}" value="{{$set->number_set}}">Сет {{$set->number_set}}
                            @lang('somewords.'.$set->day_of_week)
                            @isset($set->date[$set->day_of_week])
                                {{$set->date[$set->day_of_week]}}
                            @endisset
                            {{$set->time}} (еще
                            мест {{$set->free}})
                        </option>
                    @else
                        <option disabled data-free="{{$set->free}}" value="{{$set->number_set}}">Сет {{$set->number_set}}
                            @lang('somewords.'.$set->day_of_week)
                            @isset($set->date[$set->day_of_week])
                                {{$set->date[$set->day_of_week]}}
                            @endisset
                            {{$set->time}} (мест нет)
                        </option>
                    @endif
                @endif
            @endforeach
        </select>
        <label for="floatingSelect">Выбрать время для сета</label>
    </div>
    @if($is_show_button_list_pending)
        @include('event.list_pending')
    @endif
@endif
