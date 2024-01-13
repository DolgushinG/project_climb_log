<div class="tab-pane fade active show pt-3" id="tab-events">
        <div class="event-schedule-area-two bg-color pad100">
            <div class="container">
                <!-- row end-->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="tab-content" id="myTabContent">
                            <div class="tab-pane fade active show" id="home" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                        @if(count($events) == 0)
                                            <div class="event-wrap">
                                                <h5>Вы не принимали участие ни в каких соревнованиях</h5>
                                            </div>

                                        @else
                                            <tr>
                                                <th class="text-center" scope="col">Место</th>
                                                <th scope="col">Название</th>
                                                <th scope="col">Кол-во Участников</th>
                                                <th scope="col">Aфиша</th>
                                                <th scope="col">Статус</th>
                                            </tr>
                                        </thead>
                                        <tbody>

                                        @foreach($events as $event)
                                        <tr class="inner-box">
                                            <th scope="row">
                                                @if($event->user_place == 'Нет результата')
                                                    <div class="event-wrap">
                                                        <h5>{{$event->user_place}}</h5>
                                                    </div>
                                                @else
                                                    <div class="event-date">
                                                        <span>{{$event->user_place}}</span>
                                                    </div>
                                                @endif
                                            </th>
                                            <td>
                                                <div class="event-wrap">
                                                    <h5>{{$event->title}}</h5>
                                                </div>
                                            </td>
                                            <th scope="row">
                                                <div class="event-date">
                                                    <span>{{$event->amount_participant}}</span>
                                                </div>
                                            </th>
                                            <td>
                                                <div class="event-img">
                                                    <img src="storage/{{$event->image}}" alt="" />
                                                </div>
                                            </td>
                                            <td>
                                                <div class="r-no">
                                                     @if($event->participant_active == "Внес результаты")
                                                        <span class="badge rounded-pill bg-success text-white">{{$event->participant_active}}</span>
                                                    @else
                                                        <span class="badge rounded-pill bg-warning text-white">{{$event->participant_active}}</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                @if(!\App\Models\ResultParticipant::participant_with_result(Auth()->user()->id, $event->id))
                                                    <a href="{{route('listRoutesEvent', $event->title_eng)}}" class="btn btn-success">Внести результаты</a>
                                                @else
                                                    @if($event->active)
                                                    <div class="primary-btn">
                                                        <a class="btn btn-primary" href="event/{{$event->climbing_gym_name_eng}}/{{$event->title_eng}}">Подробнее</a>
                                                    </div>
                                                    @endif
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                        @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
</div>
<style>
    .event-schedule-area .section-title .title-text {
        margin-bottom: 50px;
    }

    .event-schedule-area .tab-area .nav-tabs {
        border-bottom: inherit;
    }

    .event-schedule-area .tab-area .nav {
        border-bottom: inherit;
        display: -webkit-box;
        display: -ms-flexbox;
        display: flex;
        -webkit-box-orient: vertical;
        -webkit-box-direction: normal;
        -ms-flex-direction: column;
        flex-direction: column;
        margin-top: 80px;
    }

    .event-schedule-area .tab-area .nav-item {
        margin-bottom: 75px;
    }
    .event-schedule-area .tab-area .nav-item .nav-link {
        text-align: center;
        font-size: 22px;
        color: #333;
        font-weight: 600;
        border-radius: inherit;
        border: inherit;
        padding: 0px;
        text-transform: capitalize !important;
    }
    .event-schedule-area .tab-area .nav-item .nav-link.active {
        color: #4125dd;
        background-color: transparent;
    }

    .event-schedule-area .tab-area .tab-content .table {
        margin-bottom: 0;
        width: 80%;
    }
    .event-schedule-area .tab-area .tab-content .table thead td,
    .event-schedule-area .tab-area .tab-content .table thead th {
        border-bottom-width: 1px;
        font-size: 20px;
        font-weight: 600;
        color: #252525;
    }
    .event-schedule-area .tab-area .tab-content .table td,
    .event-schedule-area .tab-area .tab-content .table th {
        border: 1px solid #b7b7b7;
        padding-left: 30px;
    }
    .event-schedule-area .tab-area .tab-content .table tbody th .heading,
    .event-schedule-area .tab-area .tab-content .table tbody td .heading {
        font-size: 16px;
        text-transform: capitalize;
        margin-bottom: 16px;
        font-weight: 500;
        color: #252525;
        margin-bottom: 6px;
    }
    .event-schedule-area .tab-area .tab-content .table tbody th span,
    .event-schedule-area .tab-area .tab-content .table tbody td span {
        color: #4125dd;
        font-size: 18px;
        text-transform: uppercase;
        margin-bottom: 6px;
        display: block;
    }
    .event-schedule-area .tab-area .tab-content .table tbody th span.date,
    .event-schedule-area .tab-area .tab-content .table tbody td span.date {
        color: #656565;
        font-size: 14px;
        font-weight: 500;
        margin-top: 15px;
    }
    .event-schedule-area .tab-area .tab-content .table tbody th p {
        font-size: 14px;
        margin: 0;
        font-weight: normal;
    }

    .event-schedule-area-two .section-title .title-text h2 {
        margin: 0px 0 15px;
    }

    .event-schedule-area-two ul.custom-tab {
        -webkit-box-pack: center;
        -ms-flex-pack: center;
        justify-content: center;
        border-bottom: 1px solid #dee2e6;
        margin-bottom: 30px;
    }
    .event-schedule-area-two ul.custom-tab li {
        margin-right: 70px;
        position: relative;
    }
    .event-schedule-area-two ul.custom-tab li a {
        color: #252525;
        font-size: 25px;
        line-height: 25px;
        font-weight: 600;
        text-transform: capitalize;
        padding: 35px 0;
        position: relative;
    }
    .event-schedule-area-two ul.custom-tab li a:hover:before {
        width: 100%;
    }
    .event-schedule-area-two ul.custom-tab li a:before {
        position: absolute;
        left: 0;
        bottom: 0;
        content: "";
        background: #4125dd;
        width: 0;
        height: 2px;
        -webkit-transition: all 0.4s;
        -o-transition: all 0.4s;
        transition: all 0.4s;
    }
    .event-schedule-area-two ul.custom-tab li a.active {
        color: #4125dd;
    }

    .event-schedule-area-two .primary-btn {
        margin-top: 40px;
    }

    .event-schedule-area-two .tab-content .table {
        -webkit-box-shadow: 0 1px 30px rgba(0, 0, 0, 0.1);
        box-shadow: 0 1px 30px rgba(0, 0, 0, 0.1);
        margin-bottom: 0;
    }
    .event-schedule-area-two .tab-content .table thead {
        background-color: #007bff;
        color: #fff;
        font-size: 20px;
    }
    .event-schedule-area-two .tab-content .table thead tr th {
        padding: 20px;
        border: 0;
    }
    .event-schedule-area-two .tab-content .table tbody {
        background: #fff;
    }
    .event-schedule-area-two .tab-content .table tbody tr.inner-box {
        border-bottom: 1px solid #dee2e6;
    }
    .event-schedule-area-two .tab-content .table tbody tr th {
        border: 0;
        padding: 30px 20px;
        vertical-align: middle;
    }
    .event-schedule-area-two .tab-content .table tbody tr th .event-date {
        color: #252525;
        text-align: center;
    }
    .event-schedule-area-two .tab-content .table tbody tr th .event-date span {
        font-size: 50px;
        line-height: 50px;
        font-weight: normal;
    }
    .event-schedule-area-two .tab-content .table tbody tr td {
        padding: 30px 20px;
        vertical-align: middle;
    }
    .event-schedule-area-two .tab-content .table tbody tr td .r-no span {
        color: #252525;
    }
    .event-schedule-area-two .tab-content .table tbody tr td .event-wrap h3 a {
        font-size: 20px;
        line-height: 20px;
        color: #cf057c;
        -webkit-transition: all 0.4s;
        -o-transition: all 0.4s;
        transition: all 0.4s;
    }
    .event-schedule-area-two .tab-content .table tbody tr td .event-wrap h3 a:hover {
        color: #4125dd;
    }
    .event-schedule-area-two .tab-content .table tbody tr td .event-wrap .categories {
        display: -webkit-inline-box;
        display: -ms-inline-flexbox;
        display: inline-flex;
        margin: 10px 0;
    }
    .event-schedule-area-two .tab-content .table tbody tr td .event-wrap .categories a {
        color: #252525;
        font-size: 16px;
        margin-left: 10px;
        -webkit-transition: all 0.4s;
        -o-transition: all 0.4s;
        transition: all 0.4s;
    }
    .event-schedule-area-two .tab-content .table tbody tr td .event-wrap .categories a:before {
        content: "\f07b";
        font-family: fontawesome;
        padding-right: 5px;
    }
    .event-schedule-area-two .tab-content .table tbody tr td .event-wrap .time span {
        color: #252525;
    }
    .event-schedule-area-two .tab-content .table tbody tr td .event-wrap .organizers {
        display: -webkit-inline-box;
        display: -ms-inline-flexbox;
        display: inline-flex;
        margin: 10px 0;
    }
    .event-schedule-area-two .tab-content .table tbody tr td .event-wrap .organizers a {
        color: #4125dd;
        font-size: 16px;
        -webkit-transition: all 0.4s;
        -o-transition: all 0.4s;
        transition: all 0.4s;
    }
    .event-schedule-area-two .tab-content .table tbody tr td .event-wrap .organizers a:hover {
        color: #4125dd;
    }
    .event-schedule-area-two .tab-content .table tbody tr td .event-wrap .organizers a:before {
        content: "\f007";
        font-family: fontawesome;
        padding-right: 5px;
    }
    .event-schedule-area-two .tab-content .table tbody tr td .primary-btn {
        margin-top: 0;
        text-align: center;
    }
    .event-schedule-area-two .tab-content .table tbody tr td .event-img img {
        width: 100px;
        height: 100px;
        border-radius: 8px;
    }
</style>
