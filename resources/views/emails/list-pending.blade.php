@extends('emails.layout')
@section('content')
    <h3>Вы были добавлены в лист ожидания <h3/><br>
    <h2><strong>{{ $details['title'] }}</strong><h2/><br>
    <h3>Дата старта  : <strong>{{ $details['event_start_date'] }}</strong><h3/><br>
    <h3>Ваши выбранные для ожидания сеты  :
            <strong> {{join(' и ', array_filter(array_merge(array(join(', ', array_slice($details['number_sets'], 0, -1))), array_slice($details['number_sets'], -1)), 'strlen'))}}, </strong>
        <h3/><br>
    <h3>Это не 100% участие и в случае если места в сетах не освободятся, вы не сможете принять участие в указанных сета.<h3/><br>
    <h3> <a href="{{$details['event_url']}}">Ссылка на соревнование</a><h3/><br>
@endsection
