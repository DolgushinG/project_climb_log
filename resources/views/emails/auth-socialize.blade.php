@extends('emails.layout')
@section('content')

    <h3>Уважаемый пользователь {{$details['middlename']}}!<h3/><br>
    <h3>Вы вошли в свою учетную запись на Climbing Events используя учётные данные: {{$details['socialize']}}<h3/><br>
    <h3>Вход совершён: {{$details['time']}}:<h3/><br>
    <h3>Если это были не вы, возможно, кто-то вошёл от вашего имени. В этом случае обратитесь в службу поддержки<h3/><br>
@endsection
