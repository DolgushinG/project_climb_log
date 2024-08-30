@extends('layouts.main_page.app')
@section('content')
    <!-- ======= Breadcrumbs ======= -->
    <section class="breadcrumbs">
        <div class="container">
            <ol>
                <li><a href="{{route('main')}}">Домой</a></li>
                <li><a href=" {{route('posts')}}">Статьи</a></li>
                <li>{{$post->slug}}</li>
            </ol>
            <h2>Просмотр статьи</h2>
        </div>
    </section><!-- End Breadcrumbs -->

    <!-- ======= Blog Single Section ======= -->
    <section id="blog" class="blog">
        <div class="container" data-aos="fade-up">

            <div class="row">

                <div class="col-lg-8 entries">

                    <article class="entry entry-single">

                        <div class="entry-img">
                            <img src="{{ asset('storage/' . $post->image) }}" alt="" class="img-fluid">
                        </div>

                        <h2 class="entry-title">
                            <a href="#">{{ $post->title}}</a>
                        </h2>

                        <div class="entry-meta">
                            <ul>
                                <li class="d-flex"><i class="bi bi-hand-thumbs-up"></i>
                                    <a>{{ $post->likes() }}</a></li>
                                <li class="d-flex"><i class="bi bi-hand-thumbs-down"></i>
                                    <a>{{ $post->dislikes() }}</a></li>
                            </ul>
                        </div>

                        <div class="entry-content">
                            <p>
                                {!! $post->body !!}
                            </p>

                        </div>

                        <div class="entry-footer">
                            <div class="row mb-5">
                                <div class="col-lg-10" style="margin-top: 2rem;!important" data-aos="fade-up">
                                    <small class="float-right">
                                        <span title="Likes" id="saveLikeDislike" data-type="like" data-post="{{ $post->id }}"
                                              class="mr-2 btn btn-sm btn-outline-primary d-inline font-weight-bold">
                                            <i class="bi bi-hand-thumbs-up"></i>
                                            <span class="like-count">{{ $post->likes() }}</span>
                                        </span>
                                        <span title="Dislikes" id="saveLikeDislike" data-type="dislike"
                                              data-type="dislike" data-post="{{ $post->id }}"
                                              class="mr-2 btn btn-sm btn-outline-danger d-inline font-weight-bold"
                                              style="margin-left: 5px;}">
                                            <i class="bi bi-hand-thumbs-down" style="color: red"></i>
                                        <span class="dislike-count">{{ $post->dislikes() }}</span>
                                        </span>
                                    </small>
                                </div>
                            </div>
                        </div>

                    </article><!-- End blog entry -->
                </div><!-- End blog entries list -->
                @include('blog.sidebar')
            </div>
        </div>
    </section><!-- End Blog Single Section -->

    <script type="text/javascript" src="{{ asset('js/like.js') }}"></script>
@endsection
