@extends('emails.layout')
@section('content')
    <h3>Уважаемый пользователь {{$details['middlename']}}!<h3/><br>
    <h3>Вы вошли в свою учетную запись на Climbing Events со следующего устройства:<h3/><br>
    <h3>Девайс: {{$details['device']}}:<h3/><br>
    <h3>IP-адрес: {{$details['ip']}}:<h3/><br>
    <h3>Время: {{$details['time']}}:<h3/><br>
    <h3>Если вы не производили данный вход в систему, рекомендуем немедленно связаться с нами по электронной почте admin@climbing-events.ru<h3/><br>
@endsection
