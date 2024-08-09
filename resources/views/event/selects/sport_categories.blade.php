@if($event->is_need_sport_category)
    @if(!Auth::user()->sport_category || Auth::user()->sport_category == 'Выбрать')
        <div class="form-floating mb-3">
            <select class="form-select" id="floatingSelectSportCategory"
                    aria-label="Floating label select example" autocomplete="off" required>
                <option selected disabled value="">Открыть для выбора разряда
                </option>
                @foreach ($sport_categories as $category)
                    <option value="{{$category}}">{{$category}}</option>
                @endforeach
            </select>
            <label for="floatingSelectSportCategory">Требуется указать разряд</label>
        </div>
    @endif
@endif
