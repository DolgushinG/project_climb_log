@if(\App\Models\ResultParticipant::is_sended_bill(Auth()->user()->id, $event->id))
    <button href="#" data-bs-toggle="modal" data-bs-target="#scrollingModal" class="btn btn-warning rounded-pill" disabled>
        Чек отправлен (На проверке..)
    </button>
@else
    @if($event->registration_time_expired)
    <button id="btn-payment" data-bs-toggle="modal" data-bs-target="#scrollingModal" class="btn btn-warning rounded-pill">
         Оплатить (регистрация сгорит через - {{$event->registration_time_expired}} {{\App\Helpers\Helpers::echo_days($event->registration_time_expired)}})
    </button>
    @else
        <button id="btn-payment" data-bs-toggle="modal" data-bs-target="#scrollingModal" class="btn btn-warning rounded-pill">
            Оплатить
        </button>
    @endif
@endif


