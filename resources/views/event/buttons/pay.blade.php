@if(\App\Models\ResultParticipant::is_sended_bill(Auth()->user()->id, $event->id))
    <button href="#" data-bs-toggle="modal" data-bs-target="#scrollingModal" class="btn btn-warning rounded-pill" disabled>
        Чек отправлен (На проверке..)
    </button>
@else
    <button id="btn-payment" data-bs-toggle="modal" data-bs-target="#scrollingModal" class="btn btn-warning rounded-pill">
         Оплатить
    </button>
@endif


