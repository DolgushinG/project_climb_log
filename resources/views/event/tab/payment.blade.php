<link href="{{asset('plugins/cropper/cropper.css')}}" rel="stylesheet">
<script src="{{asset('plugins/cropper/cropper.js')}}"></script>
<script src="{{asset('js/dinamic-payment.js')}}"></script>
@if($event->is_need_pay_for_reg)
    @auth
        @if(\App\Models\User::user_participant($event->id))
            @if(\App\Models\ResultRouteQualificationClassic::is_pay_participant(Auth()->user()->id, $event->id))
                <div class="container text-center pt-2 pb-2">
                    <span class="badge bg-success" style="font-size: 22px"> ОПЛАЧЕНО </span><br>
                </div>
            @else
                <div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="modalLabel"
                     aria-hidden="true">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-footer">
                                <h5 class="modal-title" id="modalLabel">Выберите область чек
                                </h5>
                                <div class="row">
                                    <div class="col">
                                        <button type="button" style="color: white!important;"
                                                class="btn btn-primary showbuttonsave" id="crop">Отправить чек
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
                                <button type="button" style="color: white!important;" class="btn btn-secondary"
                                        id="modalclose" data-dismiss="modal">
                                    Отмена
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                @if($event->setting_payment == \App\Models\OwnerPaymentOperations::DINAMIC)
                    <div class="modal fade" id="modal-document" tabindex="-1" role="dialog" aria-labelledby="modalLabelDocument"
                         aria-hidden="true">
                        <div class="modal-dialog modal-lg" role="document">
                            <div class="modal-content">
                                <div class="modal-footer">
                                    <h5 class="modal-title" id="modalLabelDocument">Выберите область документа
                                    </h5>
                                    <div class="row">
                                        <div class="col">
                                            <button type="button" style="color: white!important;"
                                                    class="btn btn-primary showbuttonsave" id="crop-document">Отправить документ
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-body">
                                    <div class="img-container">
                                        <div class="row">
                                            <div class="col-md-8">
                                                <img id="image-document" src="">
                                            </div>
                                            <div class="col-md-4">
                                                <div class="preview"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" style="color: white!important;" class="btn btn-secondary"
                                            id="modalclose" data-dismiss="modal">
                                        Отмена
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="card-body">
                    <!-- Accordion without outline borders -->
                    <div class="accordion accordion-flush" id="accordionFlushExample">
                        @if($event->info_payment)
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="flush-headingOne">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                            data-bs-target="#flush-collapseOne" aria-expanded="false"
                                            aria-controls="flush-collapseOne">
                                        Важная информация по оплате
                                    </button>
                                </h2>
                                <div id="flush-collapseOne" class="accordion-collapse collapse"
                                     aria-labelledby="flush-headingOne" data-bs-parent="#accordionFlushExample">
                                    <div class="accordion-body">
                                        <p>{!! $event->info_payment !!}</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="flush-headingTwo">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#flush-collapseTwo" aria-expanded="false"
                                        aria-controls="flush-collapseTwo">
                                    Стартовые взнос и оплата
                                </button>
                            </h2>
                            <div id="flush-collapseTwo" class="accordion-collapse collapse"
                                 aria-labelledby="flush-headingTwo" data-bs-parent="#accordionFlushExample">
                                <div class="accordion-body">
                                    @if($event->amount_start_price)
                                        <div class="container text-center pt-2 pb-2">
                                            <label> Стартовый взнос: </label>
                                            <span id="price" data-main-price="{{$event->amount_start_price}}" class="badge bg-success" style="font-size: 22px"> <span id="price-value">{{$event->amount_start_price}}</span> руб. </span><br>
                                            @if($event->setting_payment == \App\Models\OwnerPaymentOperations::DINAMIC)
                                                <label> Эту сумму вы должны ввести для оплаты </label>
                                            @endif
                                        </div>
                                        @if($event->setting_payment == \App\Models\OwnerPaymentOperations::DINAMIC)
                                            <div class="container mt-5">
                                                <div id="products">
                                                    <label for="products" class="mb-1">Выберите мерч:</label>
                                                    @foreach($event->products as $index => $product)
                                                        @if($participant_products_and_discounts && isset($participant_products_and_discounts['products']))
                                                            @if(in_array($product['Название'], $participant_products_and_discounts['products']))
                                                                <div class="form-check form-switch">
                                                                    <input autocomplete="off" data-name="{{ $product['Название'] }}" id="{{ $product['Цена'] }}{{ $index }}" data-price="{{ $product['Цена'] }}" class="form-check-input" type="checkbox" checked>
                                                                    <label class="form-check-label" for="{{ $product['Цена'] }}{{ $index }}">{{ $product['Название'] }} ({{ $product['Цена'] }} руб.)</label>
                                                                </div>
                                                            @else
                                                                <div class="form-check form-switch">
                                                                    <input autocomplete="off" data-name="{{ $product['Название'] }}" id="{{ $product['Цена'] }}{{ $index }}" data-price="{{ $product['Цена'] }}" class="form-check-input" type="checkbox">
                                                                    <label class="form-check-label" for="{{ $product['Цена'] }}{{ $index }}">{{ $product['Название'] }} ({{ $product['Цена'] }} руб.)</label>
                                                                </div>
                                                            @endif
                                                        @else
                                                            <div class="form-check form-switch">
                                                                <input autocomplete="off" data-name="{{ $product['Название'] }}" id="{{ $product['Цена'] }}{{ $index }}" data-price="{{ $product['Цена'] }}" class="form-check-input" type="checkbox">
                                                                <label class="form-check-label" for="{{ $product['Цена'] }}{{ $index }}">{{ $product['Название'] }} ({{ $product['Цена'] }} руб.)</label>
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                </div>
                                                <div class="form-floating mt-2">
                                                    <select autocomplete="off" class="form-select" id="discounts" aria-label="Выберите скидку">
                                                        <option data-value="0" selected="">Нет скидок</option>
                                                        @foreach($event->discounts as $index => $discount)
                                                            @if($participant_products_and_discounts && isset($participant_products_and_discounts['discount']))
                                                                @if($discount['Название'] == $participant_products_and_discounts['discount'])
                                                                    <option selected data-name="{{ $discount['Название'] }}" data-value="{{ $discount['Проценты'] }}" value="{{ $discount['Проценты'] }}">{{ $discount['Название'] }} скидка ({{ $discount['Проценты'] }} %)</option>
                                                                @else
                                                                    <option data-name="{{ $discount['Название'] }}" data-value="{{ $discount['Проценты'] }}" value="{{ $discount['Проценты'] }}">{{ $discount['Название'] }} скидка ({{ $discount['Проценты'] }} %)</option>
                                                                @endif
                                                            @else
                                                                <option data-name="{{ $discount['Название'] }}" data-value="{{ $discount['Проценты'] }}" value="{{ $discount['Проценты'] }}">{{ $discount['Название'] }} скидка ({{ $discount['Проценты'] }} %)</option>
                                                            @endif
                                                        @endforeach
                                                    </select>
                                                    <label for="floatingSelect">Выберите скидку</label>
                                                </div>
                                                <div class="container text-center pt-2 pb-2">
                                                    <button data-user_id="{{Auth()->user()->id}}" data-id="{{$event->id}}" id="save_products_discount" class="btn btn-outline-success" style="font-size: 22px">
                                                        Сохранить выбранный мерч и скидку</button><br>
                                                </div>
                                            </div>
                                            @if($event->registration_time_expired)
                                                <div class="container text-center pt-2 pb-2">
                                                    <span style="font-size: 22px">Без оплаты регистрация сгорит через {{$event->registration_time_expired}} - {{\App\Helpers\Helpers::echo_days($event->registration_time_expired)}}</span><br>
                                                </div>
                                            @endif
                                            @if($event->link_payment)
                                                <div class="container text-center pt-2 pb-2">
                                                    <a class="btn btn-primary" style="font-size: 22px"
                                                       href="{{$event->link_payment}}">Оплатить</a><br>
                                                </div>
                                            @endif
                                            @if($event->img_payment)
                                                <div class="container w-50 h-25 text-center pt-2 pb-2">
                                                    <img class="img img-responsive"
                                                         src="{{asset('storage/'.$event->img_payment)}}" alt="qr">
                                                </div>
                                            @endif
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>
                        @if($event->setting_payment == \App\Models\OwnerPaymentOperations::DINAMIC)
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="flush-headingThreeDocument">
                                    <button class="accordion-button collapsed" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#flush-collapseThreeDocument"
                                            aria-expanded="false" aria-controls="flush-collapseThreeDocument">
                                        Приложить документ (основание для скидки)
                                    </button>
                                </h2>
                                <div id="flush-collapseThreeDocument" class="accordion-collapse collapse"
                                     aria-labelledby="flush-headingThreeDocument" data-bs-parent="#accordionFlushExample">
                                    <div class="accordion-body">
                                        @if(\App\Models\ResultRouteQualificationClassic::is_sended_document(Auth()->user()->id, $event->id))
                                            <div class="container text-center pt-2 pb-2">
                                                <div class="text-dark large mt-1" style="font-size: 22px"> Документ
                                                    отправлен (На проверке..)
                                                </div>
                                                <br>
                                            </div>
                                        @else
                                            <div id="attachDocument" class="container text-center pt-2 pb-2"
                                                 data-event-id="{{$event->id}}">
                                                <label> Приложить документ</label>
                                                <input type="file" id="imageDocument" name="image" class="imageDocument">
                                                <div class="text-dark small mt-1">Допустимые форматы JPG, JPEG, PNG.
                                                    Макс. размер 8 мб
                                                </div>
                                            </div>
                                            <div id="checkingDocument" class="container text-center pt-2 pb-2"
                                                 style="display: None">
                                                <span style="font-size: 22px">  Документ отправлен (На проверке..)  </span><br>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="flush-headingThree">
                                <button class="accordion-button collapsed" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#flush-collapseThree"
                                        aria-expanded="false" aria-controls="flush-collapseThree">
                                    Приложить чек
                                </button>
                            </h2>
                            <div id="flush-collapseThree" class="accordion-collapse collapse"
                                 aria-labelledby="flush-headingThree" data-bs-parent="#accordionFlushExample">
                                <div class="accordion-body">
                                    @if(\App\Models\ResultRouteQualificationClassic::is_sended_bill(Auth()->user()->id, $event->id))
                                        <div class="container text-center pt-2 pb-2">
                                            <div class="text-dark large mt-1" style="font-size: 22px"> Чек
                                                отправлен (На проверке..)
                                            </div>
                                            <br>
                                        </div>
                                    @else
                                        <div id="attachBill" class="container text-center pt-2 pb-2"
                                             data-event-id="{{$event->id}}">
                                            <label> Приложить чек после оплаты</label>
                                            <input type="file" id="image" name="image" class="imageBill">
                                            <div class="text-dark small mt-1">Допустимые форматы JPG, JPEG, PNG.
                                                Макс. размер 8 мб
                                            </div>
                                        </div>
                                        <div id="checkingBillTab" class="container text-center pt-2 pb-2"
                                             style="display: None">
                                            <span style="font-size: 22px">  Чек отправлен (На проверке..)  </span><br>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @else
            <div class="card-body">
                <!-- Accordion without outline borders -->
                <div class="accordion accordion-flush" id="accordionFlushExample">
                    @if($event->info_payment)
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="flush-headingOne">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#flush-collapseOne" aria-expanded="false"
                                        aria-controls="flush-collapseOne">
                                    Важная информация по оплате
                                </button>
                            </h2>
                            <div id="flush-collapseOne" class="accordion-collapse collapse"
                                 aria-labelledby="flush-headingOne" data-bs-parent="#accordionFlushExample">
                                <div class="accordion-body">
                                    <p>{!! $event->info_payment !!}</p>
                                </div>
                            </div>
                        </div>
                    @endif
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="flush-headingTwo">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#flush-collapseTwo" aria-expanded="false"
                                    aria-controls="flush-collapseTwo">
                                Стартовые взнос и оплата
                            </button>
                        </h2>
                        <div id="flush-collapseTwo" class="accordion-collapse collapse"
                             aria-labelledby="flush-headingTwo" data-bs-parent="#accordionFlushExample">
                            <div class="accordion-body">
                                @if($event->amount_start_price)
                                    <div class="container text-center pt-2 pb-2">
                                        <label style="font-size: 15px"> Оплата доступна только после регистрации</label> <br>
                                        <label> Стартовый взнос: </label>
                                        <span class="badge bg-success" style="font-size: 22px"> {{$event->amount_start_price}} руб </span><br>
                                    </div>
                                @endif
                                @if($event->options_amount_price)
                                    <section id="pricing" class="pricing">
                                        <div class="container aos-init aos-animate"
                                             data-aos="fade-up">
                                            <div class="section-title">
                                                <div class="row">
                                                    @foreach($event->options_amount_price as $options)
                                                        <div class="col-md-4 aos-init aos-animate"
                                                             data-aos="fade-up"
                                                             data-aos-delay="100">
                                                            <div class="box">
                                                                <h3>{{$options['Название']}}</h3>
                                                                <h4>{{$options['Сумма']}}
                                                                    <sub>руб</sub></h4>
                                                                <ul>
                                                                    <li>{!! $options['Описание'] !!}</li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </section>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endauth
    @guest
        @if($event->amount_start_price)
            <div class="container text-center pt-2 pb-2">
                <label> Стартовый взнос: </label>
                <span class="badge bg-success" style="font-size: 22px"> <span id="price-value">{{$event->amount_start_price}}</span> руб. </span><br>
            </div>
        @endif
        @if(!$event->info_payment && !$event->options_amount_price)
            <p>Информации нет</p>
        @endif
        @if($event->info_payment)
            <p>{!! $event->info_payment !!}</p>
        @endif
        @if($event->options_amount_price)
            <section id="pricing" class="pricing">
                <div class="container aos-init aos-animate"
                     data-aos="fade-up">
                    <div class="section-title">
                        <div class="row">
                            @foreach($event->options_amount_price as $options)
                                <div class="col-md-4 aos-init aos-animate"
                                     data-aos="fade-up"
                                     data-aos-delay="100">
                                    <div class="box">
                                        <h3>{{$options['Название']}}</h3>
                                        <h4>{{$options['Сумма']}}
                                            <sub>руб</sub></h4>
                                        <ul>
                                            <li>{!! $options['Описание'] !!}</li>
                                        </ul>
                                        @auth
                                            {{--                                    @isset($options['QR код на оплату'])--}}
                                            {{--                                        <img class="img-fluid img-responsive" src="{{asset('storage/'.$options['QR код на оплату'])}}" alt="qr">--}}
                                            {{--                                    @endif--}}
                                            @if(\App\Models\User::user_participant($event->id))
                                                @isset($options['Ссылка на оплату'])
                                                    <div class="btn-wrap">
                                                        <a href="{{$options['Ссылка на оплату']}}"
                                                           class="btn-buy">Оплатить</a>
                                                    </div>
                                                @endisset
                                            @endif
                                        @endauth
                                    </div>
                                </div>
                            @endforeach

                        </div>
                    </div>
                </div>
            </section>
        @endif
    @endguest
@else
    @if(!$event->info_payment && !$event->options_amount_price)
        <p>Информации нет</p>
    @endif
    @if($event->info_payment)
        <p>{!! $event->info_payment !!}</p>
    @endif
@endif
