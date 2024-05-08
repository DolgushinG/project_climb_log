@extends('emails.layout')
@section('content')
    <h3>Полные результаты прикреплены в письме<h3/><br>
    <h4> <a href="{{asset($details['event_url'])}}">На всякий случай оставляем ссылку на соревнование</a></h4><br>
@endsection
