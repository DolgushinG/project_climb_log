@if(!Auth::user()->gender)
    <div class="form-floating mb-3">
        <select class="form-select" id="floatingSelectGender"
                aria-label="Floating label select example" autocomplete="off" required>
            <option selected disabled value="">М или Ж
            </option>
            <option value="male">M</option>
            <option value="female">Ж</option>
        </select>
        <label for="floatingSelectGender">Укажите ваш пол</label>
    </div>
@endif
