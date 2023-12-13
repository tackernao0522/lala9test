<?php

namespace App\Http\Controllers;

use App\Actions\StrRandom;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PostController extends Controller
{
    public function index()
    {
        // $posts = Post::withCount('comments')
        //     ->orderBy('comments_count', 'desc')
        //     ->get();

        // $posts = Post::query()
        //     ->with('user')
        //     ->where('status', Post::OPEN)
        //     ->orderByDesc('comments_count')
        //     ->withCount('comments')
        //     ->get();

        $posts = Post::query()
            ->onlyOpen()
            ->with('user')
            ->withCount('comments')
            ->orderByDesc('comments_count')
            ->get();

        // $posts = Post::select(['posts.*', DB::raw('count(comments.id) as comments_count')])
        //     ->leftJoin('comments', 'posts.id', '=', 'comments.post_id')
        //     ->groupBy('posts.id')
        //     ->orderBy('comments_count', 'desc')
        //     ->get();

        // dd($posts);

        return view('index', compact('posts'));
    }

    public function show(Post $post, StrRandom $strRandom)
    {
        // if ($post->status == Post::CLOSED) {
        //     abort(403);
        // }

        if ($post->isClosed()) {
            abort(403);
        }

        // $random = \Str::random(10);

        $random = $strRandom->get(10);

        return view('posts.show', compact('post', 'random'));
    }
}
