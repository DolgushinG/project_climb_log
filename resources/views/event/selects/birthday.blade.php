@if($event->is_input_birthday)
    @if(!Auth::user()->birthday)
        <label for="birthday" class="col col-form-label">Укажите дату рождения</label>
        <div class="col">
            <input name="birthday" id="birthday" type="date" class="form-control">
        </div>
    @endif
@endif
