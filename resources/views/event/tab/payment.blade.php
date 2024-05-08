<link href="{{asset('plugins/cropper/cropper.css')}}" rel="stylesheet">
<script src="{{asset('plugins/cropper/cropper.js')}}"></script>
@if($event->is_need_pay_for_reg)
    <div class="container text-center pt-2 pb-2">
        <label> Стартовый взнос: </label>
        <span class="badge bg-success" style="font-size: 22px"> {{$event->amount_start_price}} руб </span><br>
    </div>
    @auth
        @if(\App\Models\User::user_participant($event->id))
            @if(\App\Models\ResultParticipant::is_pay_participant(Auth()->user()->id, $event->id))
                <div class="container text-center pt-2 pb-2">
                    <span class="badge bg-success" style="font-size: 22px"> ОПЛАЧЕНО </span><br>
                </div>
            @else
                @if($event->registration_time_expired)
                    <div class="container text-center pt-2 pb-2">
                        <span style="font-size: 22px">Без оплаты регистрация сгорит через {{$event->registration_time_expired}} - {{\App\Helpers\Helpers::echo_days($event->registration_time_expired)}}</span><br>
                    </div>
                @endif
                <div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="modalLabel"
                     aria-hidden="true">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-footer">
                                <h5 class="modal-title" id="modalLabel">Выберите область чека
                                </h5>
                                <div class="row">
                                    <div class="col">
                                        <button type="button" style="color: white!important;" class="btn btn-primary showbuttonsave" id="crop">Отправить чек
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-body">
                                <div class="img-container">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <img id="image" src="">
                                        </div>
                                        <div class="col-md-4">
                                            <div class="preview"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" style="color: white!important;" class="btn btn-secondary" id="modalclose" data-dismiss="modal">
                                    Отмена
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                @if(\App\Models\ResultParticipant::is_sended_bill(Auth()->user()->id, $event->id))
                    <div class="container text-center pt-2 pb-2">
                        <div class="text-dark large mt-1" style="font-size: 22px">  Чек отправлен (На проверке..)  </div><br>
                    </div>
                @else
                    <div id="attachBill" class="container text-center pt-2 pb-2" data-event-id="{{$event->id}}">
                        <label> Приложить чек после оплаты</label>
                        <input type="file" id="image" name="image" class="image">
                        <div class="text-dark small mt-1">Допустимые форматы JPG, JPEG, PNG. Макс. размер 8 мб</div>
                    </div>
                    <div id="checkingBill" class="container text-center pt-2 pb-2" style="display: None">
                        <span class="badge bg-dark" style="font-size: 22px">  Чек отправлен (На проверке..)  </span><br>
                    </div>
                @endif
                @if($event->info_payment)
                    <p>{!! $event->info_payment !!}</p>
                @endif
                @if($event->link_payment)
                    <div class="container text-center pt-2 pb-2">
                        <a class="btn btn-primary" style="font-size: 22px" href="{{$event->link_payment}}">Оплатить</a><br>
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
