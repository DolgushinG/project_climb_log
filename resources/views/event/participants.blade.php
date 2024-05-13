@extends('layouts.main_page.app')
@section('content')
    <section id="contact" class="d-flex align-items-center">
        <div class="container" data-aos="zoom-out" data-aos-delay="100">
        </div>
    </section><!-- End Hero -->
    <main id="main" class="main">
        <section class="section contact">
            <div class="row m-4">
                @if($days)
                    @foreach($days as $day)
                        <div class="col-xl-12">
                            <div class="card">
                                <div class="card-body">
                                    <!-- Bordered Tabs Justified -->
                                    <ul class="nav nav-pills nav-tabs-bordered d-flex" id="borderedTabJustified" role="tablist">
                                        @foreach($sets as $set)
                                            @if($day->day_of_week === $set->day_of_week)
                                                <li class="nav-item flex-fill" role="presentation" style="margin-right: 8px">
                                                    <button class="nav-link w-100" id="{{$set->id}}-tab"
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
                                        @foreach($sets as $set)
                                            @if($day->day_of_week == $set->day_of_week)
                                                <div class="tab-pane fade show" id="bordered-justified-{{$set->id}}"
                                                     role="tabpanel" aria-labelledby="{{$set->id}}-tab">
                                                    <table class="table table-sm table-striped">
                                                        <thead>
                                                        <tr>
                                                            <th scope="col">Участник</th>
                                                            <th scope="col">Город</th>
                                                            <th scope="col">Команда</th>
                                                        </tr>
                                                        </thead>
                                                        <tbody>
                                                        @foreach($participants as $participant)
                                                            @if($participant['number_set'] == $set->number_set)
                                                                <tr>
                                                                    <td>{{$participant['middlename']}}</td>
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
                    @endforeach
                @else
                    <div class="col">
                        <div class="card">
                            <div class="card-body">
                                <ul class="nav nav-pills nav-tabs-bordered d-flex" id="borderedTabJustified" role="tablist">
                                    @foreach(['male', 'female'] as $var)
                                        <li class="nav-item flex-fill" role="presentation" style="margin-right: 8px">
                                            <button class="nav-link w-100" id="{{$var}}-tab"
                                                    data-bs-toggle="tab"
                                                    data-bs-target="#bordered-justified-{{$var}}" type="button"
                                                    role="tab" aria-controls="{{$var}}"
                                                    aria-selected="true">@lang('somewords.'.$var)</button>
                                        </li>
                                    @endforeach
                                </ul>
                                <div class="tab-content pt-2" id="borderedTabJustifiedContent">
                                    @foreach(['male', 'female'] as $var)
                                        <div class="tab-pane fade show" id="bordered-justified-{{$var}}"
                                             role="tabpanel" aria-labelledby="{{$var}}-tab">
                                            <table class="table table-sm table-striped">
                                                <thead>
                                                <tr>
                                                    <th scope="col">Участник</th>
                                                    <th scope="col">Город</th>
                                                    <th scope="col">Команда</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach($participants as $participant)
                                                    @if($participant['gender'] == $var)

                                                        <tr>
                                                            <td>{{$participant['middlename']}}</td>
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
    </main><!-- End #main -->
@endsection
