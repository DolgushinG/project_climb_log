@extends('layouts.main_page.app')
@section('content')
        <!-- ======= Breadcrumbs ======= -->
        <section class="breadcrumbs" style="padding-top: 0!important;">
            <div class="container">
                <ol>
                    <li><a href="{{route('main')}}">Домой</a></li>
                    <li>Статьи</li>
                </ol>
            </div>
        </section><!-- End Breadcrumbs -->
        <!-- ======= Blog Section ======= -->
        <section id="blog" class="blog">
            <div class="container" data-aos="fade-up">
                <div class="row">
                    <div class="col-lg-8 entries">
                        @foreach($posts as $post)
                        <article class="entry">

                            <div class="entry-img">
                                <img src="/storage/{{$post->image}}" alt="" class="img-fluid">
                            </div>

                            <h2 class="entry-title">
                                <a href="{{route('post',$post->id)}}">{{$post->title}}</a>
                            </h2>

                            <div class="entry-meta">
                                <ul>
                                    <li class="d-flex"><i class="bi bi-hand-thumbs-up"></i> <p>{{ $post->likes() }}</p></li>
                                    <li class="d-flex"><i class="bi bi-hand-thumbs-down" style="color:red;"></i> <p style="color: red">{{ $post->dislikes() }}</p></li>
                                    <li class="d-flex"><i class="bi bi-clock"></i> <p><time datetime="{{$post->created_at}}">{{$post->created_at}}</time></p></li>
{{--                                    <li class="d-flex align-items-center"><i class="bi bi-eye-fill"></i> <a href="blog-single.html">Просмотров {{$postView[$post->id]}}</a></li>--}}
                                </ul>
                            </div>

                            <div class="entry-content">
                                <p>
                                <p>{!!mb_strcut($post->body,0,400)!!}{{'...'}}</p>
                                </p>
                                <div class="read-more">
                                    <a href="{{route('post',$post->id)}}">Подробнее</a>
                                </div>
                            </div>

                        </article><!-- End blog entry -->
                        @endforeach

                        <div class="blog-pagination" style="margin-bottom: 2em">
                            <ul class="justify-content-center">
                                <div class="col-lg-6">
                                    <div class="container">
                                        {{$posts->links('pagination::bootstrap-4')}}
                                    </div>
                                </div>
                            </ul>
                        </div>

                    </div><!-- End blog entries list -->

                    @include('blog.sidebar')

                </div>

            </div>
        </section><!-- End Blog Section -->

@endsection
