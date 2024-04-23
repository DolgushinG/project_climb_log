@extends('emails.layout')
@section('content')
    <h3>Вы оплатили участие в <h3/><br>
    <h2><strong>{{ $details['title'] }}</strong><h2/><br>
{{--    <img width="100%" src="{{asset('storage/'.$details['image'])}}" alt="">   <br>--}}
    <h4>Мероприятие пройдет  : <strong>{{ $details['event_start_date'] }}</strong><h4/><br>
    <h4><strong>Ваша регистрация завершена</strong><h4><br>
    <h4><strong>Пожалуйста следите за обновлением информации на сайте мероприятия.</strong><h4><br>
    <h4><strong> Желаем успехов!</strong><h4><br>
    <h4> <a href="{{asset($details['event_url'])}}">Ссылка на соревнование</a></h4><br>
@endsection
