<div class="col-lg-4">

    <div class="sidebar">

{{--        <h3 class="sidebar-title">Search</h3>--}}
{{--        <div class="sidebar-item search-form">--}}
{{--            <form action="">--}}
{{--                <input type="text">--}}
{{--                <button type="submit"><i class="bi bi-search"></i></button>--}}
{{--            </form>--}}
{{--        </div><!-- End sidebar search formn-->--}}

{{--        <h3 class="sidebar-title">Категории</h3>--}}
{{--        <div class="sidebar-item categories">--}}
{{--            <ul>--}}
{{--                <li><a href="#">Советы новичкам <span>(25)</span></a></li>--}}
{{--                <li><a href="#">Тренировки <span>(12)</span></a></li>--}}
{{--                <li><a href="#">Подготовка трасс <span>(5)</span></a></li>--}}
{{--                <li><a href="#">Скалодромы <span>(22)</span></a></li>--}}
{{--                <li><a href="#">Прочие <span>(8)</span></a></li>--}}
{{--            </ul>--}}
{{--        </div><!-- End sidebar categories-->--}}

        <h3 class="sidebar-title">Недавние статьи</h3>
        <div class="sidebar-item recent-posts">
            @if($recentlyPost)
                @foreach($recentlyPost as $post)
                    <div class="post-item clearfix">
                        <img src="{{asset('storage/'.$post->image)}}" alt="">
                        <h4><a href="{{route('post', $post->id)}}">{{$post->title}}</a></h4>
                        <time datetime="{{$post->created_at}}">{{$post->created_at}}</time>
                    </div>
                @endforeach
            @endif
        </div><!-- End sidebar recent posts-->

{{--        <h3 class="sidebar-title">Tags</h3>--}}
{{--        <div class="sidebar-item tags">--}}
{{--            <ul>--}}
{{--                <li><a href="#">Тренировка</a></li>--}}
{{--            </ul>--}}
{{--        </div><!-- End sidebar tags-->--}}

    </div><!-- End sidebar -->

</div><!-- End blog sidebar -->
