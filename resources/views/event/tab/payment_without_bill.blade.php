@if($event->is_need_pay_for_reg)
    <div class="container text-center pt-2 pb-2">
        <label> Стартовый взнос : </label>
        <span class="badge bg-success" style="font-size: 22px"> {{$event->amount_start_price}} руб </span><br>
    </div>
    <div class="container text-center pt-2 pb-2">
        <label> Чек можно приложить в разделе стартовый взнос</label>
    </div>
    @auth
        @if(\App\Models\User::user_participant($event->id))
            @if(\App\Models\ResultRouteQualificationClassic::is_pay_participant(Auth()->user()->id, $event->id))
                <div class="container text-center pt-2 pb-2">
                    <span class="badge bg-success" style="font-size: 22px"> ОПЛАЧЕНО </span><br>
                </div>
            @else
                @if(\App\Models\ResultRouteQualificationClassic::is_sended_bill(Auth()->user()->id, $event->id))
                    <div class="container text-center pt-2 pb-2">
                        <div class="text-dark large mt-1" style="font-size: 22px"> Чек отправлен (На проверке..)</div>
                        <br>
                    </div>
                @else
                    <div id="checkingBill" class="container text-center pt-2 pb-2" style="display: None">
                        <span class="badge bg-dark" style="font-size: 22px">  Чек отправлен (На проверке..)  </span><br>
                    </div>
                @endif
                @if($event->info_payment)
                    <p>{!! $event->info_payment !!}</p>
                @endif
                @if($event->link_payment)
                    <div class="container text-center pt-2 pb-2">
                        <a class="btn btn-primary" style="font-size: 22px"
                           href="{{$event->link_payment}}">Оплатить</a><br>
                    </div>
                @endif
                @if($event->img_payment)
                    <img class="img-fluid img-responsive" src="{{asset('storage/'.$event->img_payment)}}" alt="qr">
                @endif
            @endif
        @endif

    @endauth
@else
    @if($event->info_payment)
        <p>{!! $event->info_payment !!}</p>
    @endif
@endif
