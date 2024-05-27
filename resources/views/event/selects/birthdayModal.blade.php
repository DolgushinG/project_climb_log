@if($event->is_input_birthday)
    @if(!Auth::user()->birthday)
        <label for="birthdayModal" class="col col-form-label">Укажите дату рождения</label>
        <div class="col">
            <input name="birthday" id="birthdayModal" type="date" class="form-control">
        </div>
    @endif
@endif
