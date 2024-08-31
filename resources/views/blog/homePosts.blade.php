<!-- ======= Recent Blog Posts Section ======= -->
<section id="recent-blog-posts" class="recent-blog-posts">

    <div class="container" data-aos="fade-up">
        <div class="section-title">
            <h2>Статьи</h2>
        </div>

        <div class="row">
            @foreach($recentlyPost as $post)
            <div class="col-lg-4">
                <div class="post-box">
                    <div class="post-img"><img src="{{asset('storage/'.$post->image)}}" class="img-fluid" alt=""></div>
                    <span class="post-date">{{$post->created_at}}</span>
                    <h3 class="post-title">{{$post->title}}</h3>
                    <a href="{{route('post',$post->id)}}" class="readmore stretched-link mt-auto"><span>Подробнее</span><i class="bi bi-arrow-right"></i></a>
                </div>
            </div>
            @endforeach
        </div>

    </div>

</section><!-- End Recent Blog Posts Section -->
