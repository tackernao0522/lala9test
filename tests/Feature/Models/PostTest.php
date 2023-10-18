<?php

namespace Tests\Feature\Models;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PostTest extends TestCase
{
    // use RefreshDatabase;

    /**
     * @test
     */
    function userリレーションを返す()
    {
        $post = Post::factory()->create();

        $this->assertInstanceOf(User::class, $post->user); // ユーザークラスのインスタンになっていればOK
    }

    /**
     * @test
     */
    function commentsリレーションのテスト()
    {
        $post = Post::factory()->create();

        // $post->comments; // eroguentコレクションが返ってくる

        $this->assertInstanceOf(Collection::class, $post->comments);
    }
}
