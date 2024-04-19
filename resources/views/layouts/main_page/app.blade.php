<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Climbing Events - Регистрация и подсчет соревнований</title>
    <meta content="" name="description">
    <meta content="" name="keywords">
    <script src="{{asset('plugins/jquery/jquery.min.js')}}"></script>
    <!-- Favicons -->
    <link href="{{asset('storage/img/favicon.png')}}" rel="icon">
    <link href="{{asset('storage/img/apple-touch-icon.png')}}" rel="icon">
    <!-- Google Fonts -->
{{--    <link rel="stylesheet" href="{{asset('vendor/helpers/font-awesome.css')}}">--}}
{{--    <script src="{{ mix('js/app.js') }}"></script>--}}
{{--    <link rel="stylesheet" href="{{ mix('css/app.css') }}" />--}}
    <link href="{{ asset('css/font-awesome.min.css') }}" rel="stylesheet" />
    <!-- Vendor CSS Files -->
    <link href="{{asset('plugins/cropper/cropper.css')}}" rel="stylesheet">
    <link href="{{asset('vendor/bootstrap/css/bootstrap.min.css')}}" rel="stylesheet">
    <link href="{{asset('vendor/bootstrap-icons/bootstrap-icons.css')}}" rel="stylesheet">
    <link href="{{asset('vendor/boxicons/css/boxicons.min.css')}}" rel="stylesheet">
    <link href="{{asset('vendor/quill/quill.snow.css')}}" rel="stylesheet">
    <link href="{{asset('vendor/remixicon/remixicon.css')}}" rel="stylesheet">
    <link href="{{asset('vendor/simple-datatables/style.css')}}" rel="stylesheet">

    <link href="{{asset('vendor/aos/aos.css" rel="stylesheet')}}">
    <link href="{{asset('vendor/glightbox/css/glightbox.min.css')}}" rel="stylesheet">
    <link href="{{asset('vendor/swiper/swiper-bundle.min.css')}}" rel="stylesheet">
    <!-- Template Main CSS File -->
    <link href="{{asset('css/style.css?v=0.5')}}" rel="stylesheet">

</head>

<body>
@include('layouts.main_page.header')
@yield("content")
@include('layouts.main_page.footer')
<div id="preloader"></div>
<a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i
        class="bi bi-arrow-up-short"></i></a>
<script src="{{asset('plugins/cropper/cropper.js')}}"></script>
<script src="{{asset('vendor/apexcharts/apexcharts.min.js')}}"></script>
<script src="{{asset('vendor/bootstrap/js/bootstrap.bundle.min.js')}}"></script>
<script src="{{asset('vendor/chart.js/chart.umd.js')}}"></script>
<script src="{{asset('vendor/echarts/echarts.min.js')}}"></script>
<script src="{{asset('vendor/quill/quill.min.js')}}"></script>
<script src="{{asset('vendor/simple-datatables/simple-datatables.js')}}"></script>
<script src="{{asset('vendor/tinymce/tinymce.min.js')}}"></script>
<script src="{{asset('vendor/php-email-form/validate.js')}}"></script>
<script src="{{asset('vendor/purecounter/purecounter_vanilla.js')}}"></script>
<script src="{{asset('vendor/aos/aos.js')}}"></script>
<script src="{{asset('vendor/glightbox/js/glightbox.min.js')}}"></script>
<script src="{{asset('vendor/isotope-layout/isotope.pkgd.min.js')}}"></script>
<script src="{{asset('vendor/swiper/swiper-bundle.min.js')}}"></script>
<script src="{{asset('vendor/waypoints/noframework.waypoints.js')}}"></script>

<!-- Template Main JS File -->
<script src="{{asset('js/main.js')}}"></script>
</body>
</html>
