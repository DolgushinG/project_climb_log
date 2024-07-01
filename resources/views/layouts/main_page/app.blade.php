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
    <link rel="apple-touch-icon" sizes="180x180" href="{{asset('favicon_io/apple-touch-icon.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{asset('favicon_io/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{asset('favicon_io/favicon-16x16.png') }}">
    <link rel="manifest" href="{{asset('favicon_io/site.webmanifest') }}">
    <!-- Google Fonts -->
{{--    <link rel="stylesheet" href="{{asset('vendor/helpers/font-awesome.css')}}">--}}
{{--    <script src="{{ mix('js/app.js') }}"></script>--}}
{{--    <link rel="stylesheet" href="{{ mix('css/app.css') }}" />--}}
    <link href="{{ asset('css/font-awesome.min.css') }}" rel="stylesheet" />
    <!-- Vendor CSS Files -->
{{--    <link href="{{asset('plugins/cropper/cropper.css')}}" rel="stylesheet">--}}
    <link href="{{asset('vendor/bootstrap/css/bootstrap.css')}}" rel="stylesheet">
    <link href="{{asset('vendor/bootstrap-icons/bootstrap-icons.css')}}" rel="stylesheet">
    <link href="{{asset('vendor/boxicons/css/boxicons.min.css')}}" rel="stylesheet">
{{--    <link href="{{asset('vendor/quill/quill.snow.css')}}" rel="stylesheet">--}}
{{--    <link href="{{asset('vendor/remixicon/remixicon.css')}}" rel="stylesheet">--}}
{{--    <link href="{{asset('vendor/simple-datatables/style.css')}}" rel="stylesheet">--}}

    <link href="{{asset('vendor/aos/aos.css')}}"  rel="stylesheet">
    <link href="{{asset('vendor/glightbox/css/glightbox.min.css')}}" rel="stylesheet">
    <link href="{{asset('vendor/swiper/swiper-bundle.min.css')}}" rel="stylesheet">
    <!-- Template Main CSS File -->
    <link href="{{asset('css/style.css?v=0.20')}}" rel="stylesheet">
    <!-- Yandex.Metrika counter -->
    <script type="text/javascript" >
        (function(m,e,t,r,i,k,a){m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
            m[i].l=1*new Date();
            for (var j = 0; j < document.scripts.length; j++) {if (document.scripts[j].src === r) { return; }}
            k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)})
        (window, document, "script", "https://mc.yandex.ru/metrika/tag.js", "ym");

        ym(97714036, "init", {
            clickmap:true,
            trackLinks:true,
            accurateTrackBounce:true,
            webvisor:true
        });
    </script>
    <noscript><div><img src="https://mc.yandex.ru/watch/97714036" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
    <!-- /Yandex.Metrika counter -->
</head>

<body class="light-mode">

{{--<div class="container-fluid">--}}
<div class="main-content-wrapper">
@include('layouts.main_page.header')
@include('cookie.cookies')
@yield("content")
@include('layouts.main_page.footer')

<div id="preloader"></div>
<a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i
        class="bi bi-arrow-up-short"></i></a>
{{--<script src="{{asset('plugins/cropper/cropper.js')}}"></script>--}}
{{--<script src="{{asset('vendor/apexcharts/apexcharts.min.js')}}"></script>--}}
<script src="{{asset('vendor/bootstrap/js/bootstrap.bundle.min.js')}}"></script>
{{--<script src="{{asset('vendor/chart.js/chart.umd.js')}}"></script>--}}
{{--<script src="{{asset('vendor/echarts/echarts.min.js')}}"></script>--}}
{{--<script src="{{asset('vendor/quill/quill.min.js')}}"></script>--}}
{{--<script src="{{asset('vendor/simple-datatables/simple-datatables.js')}}"></script>--}}
{{--<script src="{{asset('vendor/tinymce/tinymce.min.js')}}"></script>--}}
{{--<script src="{{asset('vendor/php-email-form/validate.js')}}"></script>--}}
<script src="{{asset('vendor/purecounter/purecounter_vanilla.js')}}"></script>
<script src="{{asset('vendor/aos/aos.js')}}"></script>
<script src="{{asset('vendor/glightbox/js/glightbox.min.js')}}"></script>
<script src="{{asset('vendor/isotope-layout/isotope.pkgd.min.js')}}"></script>
<script src="{{asset('vendor/swiper/swiper-bundle.min.js')}}"></script>
{{--<script src="{{asset('vendor/waypoints/noframework.waypoints.js')}}"></script>--}}

<!-- Template Main JS File -->
<script src="{{asset('js/main.js')}}"></script>
</div>
{{--</div>--}}
</body>
</html>
