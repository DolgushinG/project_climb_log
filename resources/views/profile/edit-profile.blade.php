<div class="tab-pane fade active show profile-edit pt-3" id="tab-edit">
    <!-- Profile Edit Form -->
    <form id="editForm">
        @csrf
        <div class="row mb-3">
            <label for="firstname" class="col-md-4 col-lg-3 col-form-label">Имя</label>
            <div class="col-md-8 col-lg-9">
                <input name="firstname" type="text" class="form-control" id="firstname" value="{{$user->firstname}}" required>
            </div>
        </div>
        <div class="row mb-3">
            <label for="lastname" class="col-md-4 col-lg-3 col-form-label">Фамилия</label>
            <div class="col-md-8 col-lg-9">
                <input name="lastname" type="text" class="form-control" id="lastname" value="{{$user->lastname}}" required>
            </div>
        </div>
        <div class="row mb-3">
            <label for="team" class="col-md-4 col-lg-3 col-form-label">Команда</label>
            <div class="col-md-8 col-lg-9">
                <input name="team" type="text" class="form-control" id="team" value="{{$user->team}}">
            </div>
        </div>
        <div class="row mb-3">
            <label for="city" class="col-md-4 col-lg-3 col-form-label">Город</label>
            <div class="col-md-8 col-lg-9">
                <input name="city" type="text" class="form-control" id="city" value="{{$user->city}}">
            </div>
        </div>
        <div class="row mb-3">
            <label for="city" class="col-md-4 col-lg-3 col-form-label">Контакты для быстрой связи</label>
            <div class="col-md-8 col-lg-9">
                <input name="contact" placeholder="" type="text" class="form-control" id="contact" value="{{$user->contact}}">
            </div>
        </div>
        <div class="row mb-3">
            <label for="birthday" class="col-md-4 col-lg-3 col-form-label">Год рождения</label>
            <div class="col-md-8 col-lg-9">
                <input name="birthday" type="date" class="form-control" id="birthday" value="{{$user->birthday}}">
            </div>
        </div>
        <div class="row mb-3">
            <label for="sport_category" class="col-md-4 col-lg-3 col-form-label">Разряд</label>
            <div class="col-md-8 col-lg-9">
                <select name="sport_category" class="form-select" aria-label="sport_category">
                    <option>Выбрать</option>
                    @foreach ($sport_categories as $category)
                        @if($category === $user->sport_category)
                            <option selected value="{{$category}}">{{$category}}</option>
                        @else
                            <option value="{{$category}}">{{$category}}</option>
                        @endif
                    @endforeach
                </select>
            </div>
        </div>
        <div class="row mb-3">
            <label for="gender" class="col-md-4 col-lg-3 col-form-label">Пол</label>
            <div class="col-md-8 col-lg-9">
                <select class="form-select" name="gender" id="gender" required>
                    <option disabled selected value="">Укажите пол...</option>
                    @if($user->gender)
                        @foreach($genders as $gender)
                            @if($user->gender == $gender)
                                <option selected value="{{$user->gender}}">
                                    @lang('somewords.'.$user->gender)
                                </option>
                            @else
                                <option value="{{$gender}}">
                                    @lang('somewords.'.$gender)
                                </option>
                            @endif
                        @endforeach
                    @else
                        @foreach($genders as $gender)
                                <option value="{{$gender}}">
                                    @lang('somewords.'.$gender)
                                </option>
                        @endforeach
                    @endif
            </select>
            </div>
        </div>
        <div class="row mb-3">
            <label for="Email" class="col-md-4 col-lg-3 col-form-label">Email</label>
            <div class="col-md-8 col-lg-9">
                @if(str_contains($user->email, 'telegram'))
                    <input name="email" type="email" class="form-control" id="email" value="" required>
                @else
                    <input name="email" type="email" class="form-control" id="email" value="{{$user->email}}" required>
                @endif
                <div id="result"></div>
            </div>
        </div>
        <div class="text-center">
            <button id="saveChanges" type="submit" class="btn btn-primary btn-save-change">Сохранить</button>
        </div>
    </form>

</div>
<script type="text/javascript" src="{{ asset('js/ddata.js') }}"></script>
