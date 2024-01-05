@extends('layouts.main.app')
@section('content')
    <!-- Slider Start -->
    <main>
        <div class="container">

            <section class="section error-404 min-vh-100 d-flex flex-column align-items-center justify-content-center">
                <h1>404</h1>
                <h2>The page you are looking for doesn't exist.</h2>
                <a class="btn" href="{{route('main')}}">Back to home</a>
                <img src="{{asset('storage/img/not-found.svg')}}" class="img-fluid py-5" alt="Page Not Found">
            </section>

        </div>
    </main><!-- End #main -->

@endsection('content')
