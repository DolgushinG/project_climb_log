{{--<div class="tab-pane" id="edit">--}}
{{--    <form>--}}
{{--        <div class="form-group row">--}}
{{--            <label class="col-lg-3 col-form-label form-control-label">First name</label>--}}
{{--            <div class="col-lg-9">--}}
{{--                <input class="form-control" type="text" value="Mark">--}}
{{--            </div>--}}
{{--        </div>--}}
{{--        <div class="form-group row">--}}
{{--            <label class="col-lg-3 col-form-label form-control-label">Last name</label>--}}
{{--            <div class="col-lg-9">--}}
{{--                <input class="form-control" type="text" value="Jhonsan">--}}
{{--            </div>--}}
{{--        </div>--}}
{{--        <div class="form-group row">--}}
{{--            <label class="col-lg-3 col-form-label form-control-label">Email</label>--}}
{{--            <div class="col-lg-9">--}}
{{--                <input class="form-control" type="email" value="mark@example.com">--}}
{{--            </div>--}}
{{--        </div>--}}
{{--        <div class="form-group row">--}}
{{--            <label class="col-lg-3 col-form-label form-control-label">Change profile</label>--}}
{{--            <div class="col-lg-9">--}}
{{--                <input class="form-control" type="file">--}}
{{--            </div>--}}
{{--        </div>--}}
{{--        <div class="form-group row">--}}
{{--            <label class="col-lg-3 col-form-label form-control-label">Website</label>--}}
{{--            <div class="col-lg-9">--}}
{{--                <input class="form-control" type="url" value="">--}}
{{--            </div>--}}
{{--        </div>--}}
{{--        <div class="form-group row">--}}
{{--            <label class="col-lg-3 col-form-label form-control-label">Address</label>--}}
{{--            <div class="col-lg-9">--}}
{{--                <input class="form-control" type="text" value="" placeholder="Street">--}}
{{--            </div>--}}
{{--        </div>--}}
{{--        <div class="form-group row">--}}
{{--            <label class="col-lg-3 col-form-label form-control-label"></label>--}}
{{--            <div class="col-lg-6">--}}
{{--                <input class="form-control" type="text" value="" placeholder="City">--}}
{{--            </div>--}}
{{--            <div class="col-lg-3">--}}
{{--                <input class="form-control" type="text" value="" placeholder="State">--}}
{{--            </div>--}}
{{--        </div>--}}

{{--        <div class="form-group row">--}}
{{--            <label class="col-lg-3 col-form-label form-control-label">Username</label>--}}
{{--            <div class="col-lg-9">--}}
{{--                <input class="form-control" type="text" value="jhonsanmark">--}}
{{--            </div>--}}
{{--        </div>--}}
{{--        <div class="form-group row">--}}
{{--            <label class="col-lg-3 col-form-label form-control-label">Password</label>--}}
{{--            <div class="col-lg-9">--}}
{{--                <input class="form-control" type="password" value="11111122333">--}}
{{--            </div>--}}
{{--        </div>--}}
{{--        <div class="form-group row">--}}
{{--            <label class="col-lg-3 col-form-label form-control-label">Confirm password</label>--}}
{{--            <div class="col-lg-9">--}}
{{--                <input class="form-control" type="password" value="11111122333">--}}
{{--            </div>--}}
{{--        </div>--}}
{{--        <div class="form-group row">--}}
{{--            <label class="col-lg-3 col-form-label form-control-label"></label>--}}
{{--            <div class="col-lg-9">--}}
{{--                <input type="reset" class="btn btn-secondary" value="Cancel">--}}
{{--                <input type="button" class="btn btn-primary" value="Save Changes">--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </form>--}}
{{--</div>--}}
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
        <div class="row mb-3">
            <label for="Email" class="col-md-4 col-lg-3 col-form-label">Email</label>
            <div class="col-md-8 col-lg-9">
                <input name="email" type="email" class="form-control" id="Email" value="{{$user->email}}">
            </div>
        </div>
        <div class="text-center">
            <button id="saveChanges" type="submit" class="btn btn-primary btn-save-change">Сохранить</button>
        </div>
    </form>

</div>
