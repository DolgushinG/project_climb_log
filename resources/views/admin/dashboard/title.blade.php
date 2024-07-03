<style>
    .title {
        font-size: 50px;
        color: #636b6f;
        font-family: 'Raleway', sans-serif;
        font-weight: 100;
        display: block;
        text-align: center;
        margin: 20px 0 10px 0px;
    }

    .links {
        text-align: center;
        margin-bottom: 20px;
    }

    .links > a {
        color: #636b6f;
        padding: 0 25px;
        font-size: 12px;
        font-weight: 600;
        letter-spacing: .1rem;
        text-decoration: none;
        text-transform: uppercase;
    }
</style>

<div class="title">
    {{$admin_info->climbing_gym_name}}
</div>
<div class="links">
    <a href="{{$admin_info->climbing_gym_link}}" target="_blank">Мой сайт скалодром</a>
    <a href="{{$admin_info->current_event}}"  target="_blank">Текущие соревнование</a>
</div>
