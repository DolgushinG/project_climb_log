<div class="tab-pane fade active show profile-edit pt-3" id="tab-edit">

    <!-- Profile Edit Form -->
    <form>

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
            <label for="Email" class="col-md-4 col-lg-3 col-form-label">Email</label>
            <div class="col-md-8 col-lg-9">
                <input name="email" type="email" class="form-control" id="Email" value="{{$user->email}}">
            </div>
        </div>

        <div class="row mb-3">
            <label for="category" class="col-md-4 col-lg-3 col-form-label">Категория в соревнованиях</label>
            <div class="col-md-8 col-lg-9">
                <input name="category" type="text" class="form-control" id="category" value="{{\App\Models\User::category($user->category)}}">
            </div>
        </div>

        <div class="text-center">
            <button type="submit" class="btn btn-primary">Сохранить</button>
        </div>
    </form><!-- End Profile Edit Form -->

</div>

<script type="text/javascript" src="{{ asset('js/ddata.js') }}"></script>
