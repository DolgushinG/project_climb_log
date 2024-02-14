<div class="tab-pane fade active show profile-edit pt-3" id="tab-edit">
    <!-- Profile Edit Form -->
    <form id="editForm">
        @csrf
        <div class="row mb-3">
            <label for="firstname" class="col-md-4 col-lg-3 col-form-label">Имя</label>
            <div class="col-md-8 col-lg-9">
                <input name="firstname" type="text" class="form-control" id="firstname" value="{{$user->firstname}}">
            </div>
        </div>
        <div class="row mb-3">
            <label for="lastname" class="col-md-4 col-lg-3 col-form-label">Фамилия</label>
            <div class="col-md-8 col-lg-9">
                <input name="lastname" type="text" class="form-control" id="lastname" value="{{$user->lastname}}">
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
            <label for="gender" class="col-md-4 col-lg-3 col-form-label">Пол</label>
            <div class="col-md-8 col-lg-9">
                <select class="form-select" name="gender" id="gender" required>
                @if($user->gender == "male")
                <option disabled value="">Укажите пол...</option>
                <option selected value="male">
                    М
                </option>
                <option value="female">
                    Ж
                </option>
                @elseif($user->gender == "female")
                    <option disabled value="">Укажите пол...</option>
                    <option value="male">
                        М
                    </option>
                    <option selected value="female">
                        Ж
                    </option>
                @else
                    <option selected disabled value="">Укажите пол...</option>
                    <option value="male">
                        М
                    </option>
                    <option value="female">
                        Ж
                    </option>
                @endif
            </select>
            </div>
        </div>
        @if(!$user->telegram_id)
        <div class="row mb-3">
            <label for="Email" class="col-md-4 col-lg-3 col-form-label">Email</label>
            <div class="col-md-8 col-lg-9">
                <input name="email" type="email" class="form-control" id="Email" value="{{$user->email}}">
            </div>
        </div>
        @endif
        <div class="text-center">
            <button id="saveChanges" type="submit" class="btn btn-primary btn-save-change">Сохранить</button>
        </div>
    </form>

</div>
