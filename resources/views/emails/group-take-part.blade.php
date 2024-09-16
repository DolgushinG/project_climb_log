@extends('emails.layout')
@section('content')
    <h3>Заявленная группа приняла участие в соревновании <h3/><br>
    <h2><strong>{{ $details['title'] }}</strong><h2/><br>
     @foreach($created_users as $index => $user)
            <h3>Участник {{ $index + 1 }}<h3/><br>
            <h3>Фамилия и Имя : <br> {{ $user->middlename }}<h3/><br>
            <h3>Созданный Email :  {{ $user->email}} (логин для входа)<h3/><br>
            <h3>Сет : {{ $user->number_set}}<h3/><br>
     @endforeach
                <h3>Пароль у всех участников, тот же что и у вас, <br>
                    если у вас не было пароля, то пароль ваша указанная в профиле фамилия <br>
                    (рекомендуется сменить пароль)<h3/><br>
    <h3>Дата старта  : <strong>{{ $details['event_start_date'] }}</strong><h3/><br>
    @isset($details['is_need_pay_for_reg'])
        @if($details['is_need_pay_for_reg'])
            <h3><strong>Для завершения регистрации, свяжитесь с организатором, пожалуйста, оплатите участие</strong><br>
        @endif
    @endisset
    @isset($details['pay_time_expired'])
        <h3 style="color:red"><strong>или регистрация сгорит через {{$details['pay_time_expired']}} - {{\App\Helpers\Helpers::echo_days($details['pay_time_expired'])}}</strong><br>
    @endisset
    @isset($details['info_payment'])
        <h2>Информация об оплате:</h2><br>
        <h3>{!! $details['info_payment'] !!}</h3> <br>
    @endisset
    @isset($details['img_payment'])
        <h3 class="mt-2">Или QR код</h3><br>
        <img width="40%" src="{{asset('storage/'.$details['img_payment'])}}" alt="">   <br>
    @endisset
    <h3> <a href="{{$details['event_url']}}">Ссылка на соревнование</a><h3/><br>
@endsection
