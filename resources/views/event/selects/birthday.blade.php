@if($event->is_input_birthday)
    @if(!Auth::user()->birthday)
        <label for="inputDate" class="col col-form-label">Укажите дату рождения</label>
        <div class="col-sm-10">
            <input name="birthday" id="birthday" type="date" class="form-control">
        </div>
    @else
        <div class="col-sm-10" style="display: none">
            <input name="birthday" id="birthday" type="date" class="form-control">
        </div>
    @endif
@endif
