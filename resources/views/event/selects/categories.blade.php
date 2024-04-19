@if(!$event->is_auto_categories)
    <div class="form-floating mb-3">
        <select class="form-select" id="floatingSelectCategory"
                aria-label="Floating label select example" autocomplete="off" required>
            <option selected disabled value="">Открыть для выбора категории
            </option>
            @foreach($event->categories as $category)
                <option
                    value="{{$category}}">{{$category}}</option>
            @endforeach
        </select>
        <label for="floatingSelectCategory">Выбрать категорию</label>
    </div>
@endif
