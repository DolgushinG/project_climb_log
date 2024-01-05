



@extends('layouts.main.app')
@section('content')
    <!-- Slider Start -->
    <main>
        <link href="https://cdn.jsdelivr.net/npm/suggestions-jquery@20.3.0/dist/css/suggestions.min.css"
              rel="stylesheet"/>
        <script src="https://cdn.jsdelivr.net/npm/suggestions-jquery@20.3.0/dist/js/jquery.suggestions.min.js"></script>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.1/dist/css/bootstrap.min.css">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.1.1/dist/js/bootstrap.bundle.min.js"></script>
        <div class="container">

            <section class="section error-404 min-vh-100 d-flex flex-column align-items-center justify-content-center">
                <h1>Climb log</h1>
                <h2>Регистрация и подсчет скалолазных соревнований</h2>
                @if(empty($events))

                @else
                    @foreach($events as $event)
                        <div class="container pt-2 pb-2">
                            <a class="btn btn-outline-primary" href="event/{{$event->climbing_gym_name}}/{{$event->title}}">{{$event->title}}</a>
                        </div>
                    @endforeach
                @endif

                <img src="{{asset('storage/img/not-found.svg')}}" class="img-fluid py-5" alt="Page Not Found">
            </section>

        </div>
    </main><!-- End #main -->

@endsection('content')
