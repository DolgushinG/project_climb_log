<?php

namespace App\Http\Controllers;

use App\Models\LikeDislike;
use App\Models\Posts;
use App\Models\User;
use ErrorException;
use Illuminate\Http\Request;

class PostsController extends Controller
{
    public function index()
    {
        if(!env('BLOG')){
            return view('errors.404');
        }
        $recentlyPost = Posts::latest('created_at')->where('status', '=', 'PUBLISHED')->paginate(6);
        $posts = Posts::where('status', '=', 'PUBLISHED')->latest('created_at')->paginate(6);
        return view('blog.index', compact(['posts', 'recentlyPost']))->render();
    }

    public function saveLikeDislike(Request $request)
    {
        $likePost = LikeDislike::where('posts_id','=', $request->post)->get();
        $likeCheckInPost = LikeDislike::where('posts_id','=', $request->post)->first();
        $data = new LikeDislike;
        if($likeCheckInPost){
            foreach($likePost as $like){
                if($like->user_ip === $request->ip()){
                    if($like->like == 1 && $request->type === 'like'){
                        LikeDislike::find($like->id)->delete();
                        return response()->json([
                            'bool' => true
                        ]);
                    } else if ($like->dislike == 1 && $request->type === 'dislike') {
                        LikeDislike::find($like->id)->delete();
                        return response()->json([
                            'bool' => true
                        ]);
                    }
                } else {
                    if ($request->type === 'like'){
                        $data->like = 1;
                    } else {
                        $data->dislike = 1;
                    }
                    $data->posts_id = $request->post;
                    $data->user_ip = $request->ip();
                    $data->save();
                    return response()->json([
                        'bool' => true
                    ]);
                }
            }
        } else {
            if ($request->type === 'like'){
                $data->like = 1;
            } else {
                $data->dislike = 1;
            }
            $data->posts_id = $request->post;
            $data->user_ip = $request->ip();
            $data->save();
            return response()->json([
                'bool' => true
            ]);
        }

    }
    public function show(Posts $post, Request $request)
    {
        if(!env('BLOG')){
            return view('errors.404');
        }
        $postStatus = Posts::find($post->id);
        if($postStatus->status !== 'PUBLISHED') {
            return redirect('/');
        }
        $recentlyPost = Posts::latest('created_at')->where('status', '=', 'PUBLISHED')->paginate(6);
        return view('blog.post', compact(['post','recentlyPost']));
    }
}
