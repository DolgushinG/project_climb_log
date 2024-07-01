@extends('emails.layout')
@section('content')
    <h3>Вы приняли участие в соревновании <h3/><br>
    <h2><strong>{{ $details['title'] }}</strong><h2/><br>
{{--        <img width="100%" src="{{asset('storage/'.$details['image'])}}" alt="">   <br>--}}
        @isset($details['number_set'])
    <h3>Ваш сет  : <strong>{{ $details['number_set']}} @lang('somewords.'.$details['set_day_of_week']) {{$details['set_date']}} {{$details['set_time']}}</strong><h3/><br>
        @endisset
    <h3>Дата старта  : <strong>{{ $details['event_start_date'] }}</strong><h3/><br>
    @isset($details['is_need_pay_for_reg'])
        @if($details['is_need_pay_for_reg'])
            <h3><strong>Для завершения регистрации, пожалуйста, оплатите участие</strong><br>
        @endif
    @endisset
    @isset($details['pay_time_expired'])
        <h3 style="color:red"><strong>или регистрация сгорит через {{$details['pay_time_expired']}} - {{\App\Helpers\Helpers::echo_days($details['pay_time_expired'])}}</strong><br>
    @endisset
    @isset($details['info_payment'])
        <h2>Информация об оплате:</h2><br>
    @endisset
    @isset($details['link_payment'])
        <h3>{{ $details['info_payment'] }}</h3> <br>
    @endisset
    @isset($details['link_payment'])
        <a class="btn btn-primary mt-2 mb-2" href="{{ $details['link_payment'] }}">Оплатить</a><br>
    @endisset
    @isset($details['img_payment'])
        <h3 class="mt-2">Или QR код</h3><br>
        <img width="40%" src="{{asset('storage/'.$details['img_payment'])}}" alt="">   <br>
    @endisset
    <h3> <a href="{{$details['event_url']}}">Ссылка на соревнование</a><h3/><br>
@endsection
