@extends('emails.layout')
@section('content')
    <h3>Вы приняли участие в соревновании <h3/><br>
    <h2><strong>{{ $details['title'] }}</strong><h2/><br>
    <img width="100%" src="{{asset('storage/'.$details['image'])}}" alt="">   <br>
    <h3>Дата старта  : <strong>{{ $details['event_start_date'] }}</strong><h3/><br>
    <h3><strong>Для завершения регистрации пожалуйста оплатите участие</strong><br>
    @isset($details['info_payment'])
        <h2>Информация об оплате:</h2><br>
    @endisset
    <h3>{{ $details['info_payment'] }}</h3> <br>
    @isset($details['link_payment'])
        <a class="btn btn-primary mt-2 mb-2" href="{{ $details['link_payment'] }}">Оплатить</a><br>
    @endisset
    @isset($details['img_payment'])
        <h3 class="mt-2">Или QR код</h3><br>
        <img width="40%" src="{{asset('storage/'.$details['img_payment'])}}" alt="">   <br>
    @endisset
    <h3> <a href="{{$details['event_url']}}">Ссылка на соревнование</a><h3/><br>
@endsection
