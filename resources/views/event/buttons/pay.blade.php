@if(\App\Models\ResultRouteQualificationClassic::is_sended_bill(Auth()->user()->id, $event->id))
    <button href="#" data-bs-toggle="modal" data-bs-target="#scrollingModal" class="btn btn-warning rounded-pill"
            disabled>
        Чек отправлен (На проверке..)
    </button>
@else
    @if($event->registration_time_expired)
        <button id="btn-payment"
                class="btn btn-warning rounded-pill">
            Необходимо оплатить стартовый взнос или регистрация сгорит через
            - {{$event->registration_time_expired}} {{\App\Helpers\Helpers::echo_days($event->registration_time_expired)}}
        </button>
    @else
        <button id="btn-payment"
                class="btn btn-warning rounded-pill">
            Необходимо оплатить
        </button>
    @endif
    <button id="bill"
            class="btn btn-warning rounded-pill">
        Необходимо приложить чек
    </button>
@endif
@if(\App\Models\ResultRouteQualificationClassic::is_sended_document(Auth()->user()->id, $event->id))
    <button href="#" data-bs-toggle="modal" data-bs-target="#scrollingModal" class="btn btn-warning rounded-pill"
            disabled>
        Документ отправлен (На проверке..)
    </button>
@else
    <button id="document"
            class="btn btn-warning rounded-pill">
        Необходимо приложить документ
    </button>
@endif

<script>
    let btn_pay_and_bill = document.querySelector('#btn-payment')
    if(btn_pay_and_bill){
        btn_pay_and_bill.addEventListener('click', function (){
            document.querySelector('#contact-tab').click()
            document.querySelector('[data-bs-target="#flush-collapseTwo"]').click()
            if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
                window.scrollTo({
                    top: document.querySelector("#btn-payment").offsetTop + 300,
                    left: 0,
                    behavior: 'smooth'
                });

            }

        })
    }

    let bill = document.querySelector('#bill')
    if(bill){
        bill.addEventListener('click', function (){
            document.querySelector('#contact-tab').click()
            document.querySelector('[data-bs-target="#flush-collapseThree"]').click()

            if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
                window.scrollTo({
                    top: document.querySelector("#btn-payment").offsetTop + 300,
                    left: 0,
                    behavior: 'smooth'
                });
            }
        })
    }

    let document_attach = document.querySelector('#document')
    if(document_attach){
        document_attach.addEventListener('click', function (){
            document.querySelector('#contact-tab').click()
            document.querySelector('[data-bs-target="#flush-collapseThreeDocument"]').click()

            if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
                window.scrollTo({
                    top: document.querySelector("#btn-payment").offsetTop + 300,
                    left: 0,
                    behavior: 'smooth'
                });
            }
        })
    }
</script>


