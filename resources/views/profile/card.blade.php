<div class="profile-card-4 z-depth-3">
    <div class="card">
    <div class="card-body text-center bg-primary rounded-top">
        <div class="user-box">
            <img src="images/avatar.jpeg"
                 alt="Profile" class="img-fluid rounded-circle">
        </div>
        <h5 class="mb-1 text-white">{{$user->middlename}}</h5>
    </div>
    <div class="card-body">
        <ul class="list-group shadow-none">
            <li class="list-group-item">
                <div class="list-icon">
                    <i class="fa fa-building"></i>
                </div>
                <div class="list-details">
                    <span>{{$user->city ?? ''}}</span>
                    <small>Город</small>
                </div>
            </li>
            <li class="list-group-item">
                <div class="list-icon">
                    <i class="fa fa-envelope"></i>
                </div>
                <div class="list-details" style="overflow: hidden; overflow-wrap: break-word;max-width:250px;">
                    <span >{{$user->email ?? ''}}</span>
                    <small>Email</small>
                </div>
            </li>
        </ul>
        <div class="row text-center mt-4">
            <h4> Статистика только по фестивалям </h4>
            <div class="col p-2">
                <h4 class="mb-1 line-height-5">{{$state_user['flash']}}</h4>
                <small class="mb-0 font-weight-bold">% Flash</small>
            </div>
            <div class="col p-2">
                <h4 class="mb-1 line-height-5">{{$state_user['redpoint']}}</h4>
                <small class="mb-0 font-weight-bold">% Redpoint </small>
            </div>
            <div class="col p-2">
                <h4 class="mb-1 line-height-5"> {{$state_user['all']}}</h4>
                <small class="mb-0 font-weight-bold">Всего трасс </small>
            </div>
        </div>
    </div>
</div>
</div>
