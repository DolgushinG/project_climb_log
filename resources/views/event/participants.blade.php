@extends('layouts.main_page.app')
@section('content')
    <section class="section contact">
            <div class="row mt-3">
                @if($days)
                    @foreach($days as $day)
                        <div class="col-md-2"></div>
                        <div class="col-md-8 mb-2">
                            <div class="card">
                                <div class="card-body">
                                    <!-- Bordered Tabs Justified -->
                                    <ul class="nav nav-pills nav-tabs-bordered d-flex" id="borderedTabJustified" role="tablist">
                                        @foreach($sets as $index => $set)
                                            @if($day->day_of_week === $set->day_of_week)
                                                <li class="nav-item" role="presentation">
                                                    <button class="nav-link {{ $index == 0 ? 'active' : '' }}" style="font-size: 10px" id="{{$set->id}}-tab"
                                                            data-bs-toggle="tab"
                                                            data-bs-target="#bordered-justified-{{$set->id}}" type="button"
                                                            role="tab" aria-controls="{{$set->id}}"
                                                            aria-selected="true">{{$set->time}} @lang('somewords.'.ucfirst($set->day_of_week))
                                                            @isset($set->date[$set->day_of_week])
                                                                {{$set->date[$set->day_of_week]}}
                                                            @endisset
                                                        <span style="margin-left: 5px;" class="badge bg-dark text-light">{{$set->count_participant}}</span></button>
                                                </li>
                                            @endif
                                        @endforeach
                                    </ul>
                                    <div class="tab-content pt-2" id="borderedTabJustifiedContent">
                                        @foreach($sets as $index => $set)
                                            @if($day->day_of_week == $set->day_of_week)
                                                <div class="tab-pane fade show {{ $index == 0 ? 'active' : '' }}" id="bordered-justified-{{$set->id}}"
                                                     role="tabpanel" aria-labelledby="{{$set->id}}-tab">
                                                    <table class="table table-sm table">
                                                        <thead>
                                                        <tr>
                                                            <th scope="col">Участник</th>
                                                            <th scope="col">Группа</th>
                                                            <th scope="col">Город</th>
                                                            <th scope="col">Команда</th>
                                                        </tr>
                                                        </thead>
                                                        <tbody>
                                                        @foreach($participants as $participant)
                                                            @if($participant['number_set'] == $set->number_set)
                                                                <tr>
                                                                    <td>{{$participant['middlename']}}</td>
                                                                    <td>{{$participant['category']}}</td>
                                                                    <td>{{$participant['city']}}</td>
                                                                    <td>{{$participant['team']}}</td>
                                                                </tr>
                                                            @endif
                                                        </tbody>
                                                        @endforeach
                                                    </table>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div><!-- End Bordered Tabs Justified -->
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2"></div>
                    @endforeach
                @else
                    <div class="col mb-3">
                        <div class="card">
                            <div class="card-body">
                                <ul class="nav nav-pills nav-tabs-bordered d-flex" id="borderedTabJustified" role="tablist">
                                    @foreach(['male', 'female'] as $index => $var)
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link {{ $index == 0 ? 'active' : '' }}" id="{{$var}}-tab"
                                                    data-bs-toggle="tab"
                                                    data-bs-target="#bordered-justified-{{$var}}" type="button"
                                                    role="tab" aria-controls="{{$var}}"
                                                    aria-selected="true">@lang('somewords.'.$var)</button>
                                        </li>
                                    @endforeach
                                </ul>
                                <div class="tab-content pt-2" id="borderedTabJustifiedContent">
                                    @foreach(['male', 'female'] as $index => $var)
                                        <div class="tab-pane fade show {{ $index == 0 ? 'active' : '' }}" id="bordered-justified-{{$var}}"
                                             role="tabpanel" aria-labelledby="{{$var}}-tab">
                                            <table class="table table-sm table-striped">
                                                <thead>
                                                <tr>
                                                    <th scope="col">Участник</th>
                                                    <th scope="col">Группа</th>
                                                    <th scope="col">Город</th>
                                                    <th scope="col">Команда</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach($participants as $participant)
                                                    @if($participant['gender'] == $var)

                                                        <tr>
                                                            <td>{{$participant['middlename']}}</td>
                                                            <td>{{$participant['category']}}</td>
                                                            <td>{{$participant['city']}}</td>
                                                            <td>{{$participant['team']}}</td>
                                                        </tr>

                                                   @endif
                                                @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @endforeach
                                </div><!-- End Bordered Tabs Justified -->
                            </div>
                        </div>
                    </div>
               @endif
            </div>
        </section>
    <script>
        if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
            $(document).ready(function () {
                {
                    let navs = document.querySelectorAll('li.nav-item')
                    navs.forEach(el => {
                        el.classList.add("w-100");
                    });
                    let nav_link = document.querySelectorAll('button.nav-link')
                    nav_link.forEach(el => {
                        el.classList.add("w-100");
                    });

                }
            });
        }
    </script>
@endsection
