@if(!$event->is_auto_categories)
    <div class="form-floating mb-3">
        <select class="form-select" id="floatingSelectCategoryModal"
                aria-label="Floating label select example" autocomplete="off" required>
            <option selected disabled value="">Открыть для выбора категории
            </option>
            @foreach($event->categories as $category)
                @if($list_pending)
                    @if(\App\Models\ParticipantCategory::where('event_id', $event->id)->where('category', $category)->first()->id == $list_pending->category_id ?? null)
                        <option selected
                            value="{{$category}}">{{$category}}</option>
                    @else
                        <option
                            value="{{$category}}">{{$category}}</option>
                    @endif
                @else
                    <option
                        value="{{$category}}">{{$category}}</option>
                @endif
            @endforeach
        </select>
        <label for="floatingSelectCategory">Выбрать категорию</label>
    </div>
@endif
