@if(!Auth::user()->gender)
    <div class="form-floating mb-3">
        <select class="form-select" id="floatingSelectGender"
                aria-label="Floating label select example" autocomplete="off" required>
            <option selected disabled value="">Отметить пол
            </option>
            <option value="male">M</option>
            <option value="female">Ж</option>
        </select>
        <label for="floatingSelectGender">Отметить пол</label>
    </div>
@else
    <div class="form-floating mb-3" style="display: none">
        <select class="form-select" id="floatingSelectGender"
                aria-label="Floating label select example" required>
            @if(Auth::user()->gender == 'male')
                <option disabled value="">Отметить пол</option>
                <option selected value="male">M</option>
                <option value="female">Ж</option>
            @else
                <option disabled value="">Отметить пол</option>
                <option value="male">M</option>
                <option selected value="female">Ж</option>
            @endif
        </select>
        <label for="floatingSelectGender">Отметить пол</label>
    </div>
@endif
