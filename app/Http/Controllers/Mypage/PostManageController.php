<?php

namespace App\Http\Controllers\Mypage;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostManageController extends Controller
{
    public function index()
    {
        $posts = auth()->user()->posts;

        return view('mypage.posts.index', compact('posts'));
    }

    public function create()
    {
        return view('mypage.posts.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'max:255'],
            'body' => ['required'],
            'status' => ['nullable', 'boolean'] // あってもなくても良い
        ]);

        $data = $request->only('title', 'body');

        $data['status'] = $request->boolean('status');

        $post = auth()->user()->posts()->create($data);

        return redirect('mypage/posts/edit/' . $post->id);
    }

    public function edit(Post $post)
    {
        // if (auth()->user()->id !== $post->user_id) {
        //     abort(403);
        // }

        // 別の書き方
        if (auth()->user()->isNot($post->user)) {
            abort(403);
        }

        $data = old() ?: $post;

        return view('mypage.posts.edit', compact('post', 'data'));
    }

    public function update(Request $request, Post $post)
    {
        // 所有チェック

        $data = $request->validate([
            'title' => ['required', 'max:255'],
            'body' => ['required'],
            'status' => ['nullable', 'boolean'],
        ]);

        $data['status'] = $request->boolean('status');

        $post->update($data);

        return redirect(route('mypage.posts.edit', $post))
            ->with('status', 'ブログを更新しました');
    }
}
