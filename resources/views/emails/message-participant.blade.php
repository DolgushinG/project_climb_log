@extends('emails.layout')
@section('content')
    <h3>Уважаемый пользователь {{$details['middlename']}}!<h3/><br>
    <h3>Сообщение от скалодрома {{ $details['climbing_gym_name'] }}<h3/><br>
    <h2><strong>{{ $details['message'] }}</strong><h2/><br>
@endsection
