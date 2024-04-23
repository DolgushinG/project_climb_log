@extends('emails.layout')
@section('content')
    <h3>Вы получили это письмо так как хотели восстановить ваш пароль<h3/><br>
    <a href="{{$url}}" class="btn btn-primary">Сброс пароля<a/><br>
    <h3>{{Lang::get('Это ссылка для сброса пароль действует :count минут.', ['count' => config('auth.passwords.'.config('auth.defaults.passwords').'.expire')])}}<h3/><br>
    <h3>Если вы не хотели сбрасывать пароль, то просто ничего не делайте.<h3/><br>
@endsection
