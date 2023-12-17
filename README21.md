# Section2

## 61. モック

`app/Http/Controllers/PostController.php`を編集  

```php:PostController.php
<?php

namespace App\Http\Controllers;

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

    public function show(Post $post)
    {
        // if ($post->status == Post::CLOSED) {
        //     abort(403);
        // }

        if ($post->isClosed()) {
            abort(403);
        }

        $random = \Str::random(10); // 追加

        return view('posts.show', compact('post', 'random')); // 編集
    }
}
```

`resources/views/posts/show.blade.php`を編集  

```php:show.blade.php
@extends('layouts.index')

@section('content')
    {{-- @if (date('md') == '1225')
        <h1>メリークリスマス！</h1>
    @endif --}}

    {{-- @if (today()->date('md') == '1225') --}}
    @if (today()->is('12-25'))
        <h1>メリークリスマス！</h1>
    @endif


    <h1>{{ $post->title }}</h1>
    <h5>{{ $random }}</h5> // 追加
    <div>{!! nl2br(e($post->body)) !!}</div>

    <p>書き手: {{ $post->user->name }}</p>

    <h2>コメント</h2>
    @foreach ($post->comments()->oldest()->get() as $comment)
        <hr>
        <p>{{ $comment->name }} ({{ $comment->created_at }})</p>
        <p>{!! nl2br(e($comment->body)) !!}</p>
    @endforeach
@endsection
```

`tests/Feature/Http/Controllers/PostControllerTest.php`を編集  

```php:PostControllerTest.php
<?php

namespace Tests\Feature\Http\Controllers;

use App\Http\Middleware\PostShowLimit;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class PostControllerTest extends TestCase
{
    // use RefreshDatabase;
    // use WithoutMiddleware;

    /**
     * @test
     */
    function TOPページで、ブログ一覧が表示される()
    {
        // Ver.8.51未満の場合で、500エラーが出た場合のエラー確認方法
        //
        // $this->withoutExceptionHandling();
        // ブラウザで確認できる場合は、ブラウザで確認する方法もある
        // エラーログを確認する

        // $this->withoutExceptionHandling();

        // $post1 = Post::factory()->create();
        // $post2 = Post::factory()->create();

        // $this->get('/')
        //     ->assertOk()
        //     ->assertSee($post1->title)
        //     ->assertSee($post2->title);

        $post1 = Post::factory()->hasComments(3)->create(['title' => 'ブログのタイトル1']);
        $post2 = Post::factory()->hasComments(5)->create(['title' => 'ブログのタイトル2']);
        Post::factory()->hasComments(1)->create();

        $this->get('/')
            ->assertOk()
            ->assertSee('ブログのタイトル1')
            ->assertSee('ブログのタイトル2')
            ->assertSee($post1->user->name)
            ->assertSee($post2->user->name)
            ->assertSee('(3件のコメント)')
            ->assertSee('(5件のコメント)')
            ->assertSeeInOrder([
                '(5件のコメント)',
                '(3件のコメント)',
                '(1件のコメント)',
            ]);
    }

    /**
     * @test
     */
    function ブログの一覧で、非公開のブログは表示されない()
    {
        $post1 = Post::factory()->closed()->create([
            'title' => 'これは非公開のブログです',
        ]);

        $post2 = Post::factory()->create([
            'title' => 'これは公開済みのブログです',
        ]); // 公開されているデータ

        $this->get('/')
            ->assertDontSee('これは非公開のブログです')
            ->assertSee('これは公開済みのブログです');
    }

    /**
     * @test
     */
    function ブログの詳細画面が表示でき、コメントが古い順に表示される()
    {
        // $this->withoutMiddleware(PostShowLimit::class);

        $post = Post::factory()->create();

        [$comment1, $comment2, $comment3] = Comment::factory()->createMany([
            ['created_at' => now()->sub('2 days'), 'name' => 'コメント太郎', 'post_id' => $post->id,],
            ['created_at' => now()->sub('3 days'), 'name' => 'コメント次郎', 'post_id' => $post->id,],
            ['created_at' => now()->sub('1 days'), 'name' => 'コメント三郎', 'post_id' => $post->id,],
        ]);

        $this->get('posts/' . $post->id)
            ->assertOk()
            ->assertSee($post->title)
            ->assertSee($post->user->name)
            ->assertSeeInOrder(
                [
                    'コメント次郎',
                    'コメント太郎',
                    'コメント三郎'
                ]
            );
    }

    /**
     * @test
     */
    function ブログで非公開のものは、詳細画面は表示できない()
    {
        $post = Post::factory()->closed()->create(); // 非公開のテストデータ

        $this->get('posts/' . $post->id)
            ->assertForbidden();
    }

    /**
     * @test
     */
    function クリスマスの日は、メリークリスマス！と表示される()
    {
        $post = Post::factory()->create();

        Carbon::setTestNow('2020-12-24');

        $this->get('posts/' . $post->id)
            ->assertOk()
            ->assertDontSee('メリークリスマス！');

        Carbon::setTestNow('2020-12-25');

        $this->get('posts/' . $post->id)
            ->assertOk()
            ->assertSee('メリークリスマス！');
    }

    /**
     * @test
     */
    function factoryの観察()
    {
        // $post = Post::factory()->make(['user_id' => null]);
        // dump($post);
        // dump($post->toArray());

        // dump(User::get()->toArray());

        $this->assertTrue(true);
    }

    // 追加
    /**
     * @test
     */
    function ブログの詳細画面がランダムな文字列が表示されている()
    {
        \Str::shouldReceive('random')
            ->once()
            ->with(10)
            ->andReturn('HELLOWORLD');

        $post = Post::factory()->create();

        $this->get('posts/' . $post->id)
            ->assertOk()
            ->assertSee('HELLOWORLD');
    }
    // ここまで
}
```

- `$ php artisan test --filter ブログの詳細画面がランダムな文字列が表示されている`を実行  

```:terminal
   FAIL  Tests\Feature\Http\Controllers\PostControllerTest
  ⨯ ブログの詳細画面がランダムな文字列が表示されている

  ---

  • Tests\Feature\Http\Controllers\PostControllerTest > ブログの詳細画面がランダムな文字列が表示されている
   PHPUnit\Framework\ExceptionWrapper 

  Method Illuminate\Support\Str::shouldReceive does not exist.

  at vendor/laravel/framework/src/Illuminate/Macroable/Traits/Macroable.php:87
     83▕      */
     84▕     public static function __callStatic($method, $parameters)
     85▕     {
     86▕         if (! static::hasMacro($method)) {
  ➜  87▕             throw new BadMethodCallException(sprintf(
     88▕                 'Method %s::%s does not exist.', static::class, $method
     89▕             ));
     90▕         }
     91▕ 


  Tests:  1 failed
  Time:   0.30s
```

- `$ mkdir app/Actions && touch $_/StrRandom.php`を実行  

`app/Actions/StrRandom.php`を編集  

```php:StrRandom.php
<?php

namespace App\Actions;

class StrRandom
{
    public function get($length)
    {
        return \Str::random($length);
    }
}
```

`app/Http/Controllers/PostController.php`を編集  

```php:PostController.php
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

    public function show(Post $post, StrRandom $strRandom) // 編集
    {
        // if ($post->status == Post::CLOSED) {
        //     abort(403);
        // }

        if ($post->isClosed()) {
            abort(403);
        }

        // $random = \Str::random(10);

        $random = $strRandom->get(10); // 追加

        return view('posts.show', compact('post', 'random'));
    }
}
```

`tests/Feature/Http/Controllers/PostControllerTest.php`を編集  

```php:PostControllerTest.php
<?php

namespace Tests\Feature\Http\Controllers;

use App\Actions\StrRandom;
use App\Http\Middleware\PostShowLimit;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class PostControllerTest extends TestCase
{
    // use RefreshDatabase;
    // use WithoutMiddleware;

    /**
     * @test
     */
    function TOPページで、ブログ一覧が表示される()
    {
        // Ver.8.51未満の場合で、500エラーが出た場合のエラー確認方法
        //
        // $this->withoutExceptionHandling();
        // ブラウザで確認できる場合は、ブラウザで確認する方法もある
        // エラーログを確認する

        // $this->withoutExceptionHandling();

        // $post1 = Post::factory()->create();
        // $post2 = Post::factory()->create();

        // $this->get('/')
        //     ->assertOk()
        //     ->assertSee($post1->title)
        //     ->assertSee($post2->title);

        $post1 = Post::factory()->hasComments(3)->create(['title' => 'ブログのタイトル1']);
        $post2 = Post::factory()->hasComments(5)->create(['title' => 'ブログのタイトル2']);
        Post::factory()->hasComments(1)->create();

        $this->get('/')
            ->assertOk()
            ->assertSee('ブログのタイトル1')
            ->assertSee('ブログのタイトル2')
            ->assertSee($post1->user->name)
            ->assertSee($post2->user->name)
            ->assertSee('(3件のコメント)')
            ->assertSee('(5件のコメント)')
            ->assertSeeInOrder([
                '(5件のコメント)',
                '(3件のコメント)',
                '(1件のコメント)',
            ]);
    }

    /**
     * @test
     */
    function ブログの一覧で、非公開のブログは表示されない()
    {
        $post1 = Post::factory()->closed()->create([
            'title' => 'これは非公開のブログです',
        ]);

        $post2 = Post::factory()->create([
            'title' => 'これは公開済みのブログです',
        ]); // 公開されているデータ

        $this->get('/')
            ->assertDontSee('これは非公開のブログです')
            ->assertSee('これは公開済みのブログです');
    }

    /**
     * @test
     */
    function ブログの詳細画面が表示でき、コメントが古い順に表示される()
    {
        // $this->withoutMiddleware(PostShowLimit::class);

        $post = Post::factory()->create();

        [$comment1, $comment2, $comment3] = Comment::factory()->createMany([
            ['created_at' => now()->sub('2 days'), 'name' => 'コメント太郎', 'post_id' => $post->id,],
            ['created_at' => now()->sub('3 days'), 'name' => 'コメント次郎', 'post_id' => $post->id,],
            ['created_at' => now()->sub('1 days'), 'name' => 'コメント三郎', 'post_id' => $post->id,],
        ]);

        $this->get('posts/' . $post->id)
            ->assertOk()
            ->assertSee($post->title)
            ->assertSee($post->user->name)
            ->assertSeeInOrder(
                [
                    'コメント次郎',
                    'コメント太郎',
                    'コメント三郎'
                ]
            );
    }

    /**
     * @test
     */
    function ブログで非公開のものは、詳細画面は表示できない()
    {
        $post = Post::factory()->closed()->create(); // 非公開のテストデータ

        $this->get('posts/' . $post->id)
            ->assertForbidden();
    }

    /**
     * @test
     */
    function クリスマスの日は、メリークリスマス！と表示される()
    {
        $post = Post::factory()->create();

        Carbon::setTestNow('2020-12-24');

        $this->get('posts/' . $post->id)
            ->assertOk()
            ->assertDontSee('メリークリスマス！');

        Carbon::setTestNow('2020-12-25');

        $this->get('posts/' . $post->id)
            ->assertOk()
            ->assertSee('メリークリスマス！');
    }

    /**
     * @test
     */
    function factoryの観察()
    {
        // $post = Post::factory()->make(['user_id' => null]);
        // dump($post);
        // dump($post->toArray());

        // dump(User::get()->toArray());

        $this->assertTrue(true);
    }

    /**
     * @test
     */
    function ブログの詳細画面がランダムな文字列が表示されている()
    {
        // \Str::shouldReceive('random')
        //     ->once()
        //     ->with(10)
        //     ->andReturn('HELLOWORLD');

        // 追加
        $this->instance(
            StrRandom::class,
            Mockery::mock(StrRandom::class, function (MockInterface $mock) {
                $mock->shouldReceive('get')
                    ->once()
                    ->with(10)
                    ->andReturn('HELLOWORLD');
            })
        );
        // ここまで

        $post = Post::factory()->create();

        $this->get('posts/' . $post->id)
            ->assertOk()
            ->assertSee('HELLOWORLD'); // 編集
    }
}
```

- `$ php artisan test --filter ブログの詳細画面がランダムな文字列が表示されている`を実行  

```:terminal
   PASS  Tests\Feature\Http\Controllers\PostControllerTest
  ✓ ブログの詳細画面がランダムな文字列が表示されている

  Tests:  1 passed
  Time:   0.48s
```

## 62. モック補足

`tests/Feature/Http/Controllers/PostControllerTest.php`を編集  

```php:PostControllerTest.php
<?php

namespace Tests\Feature\Http\Controllers;

use App\Actions\StrRandom;
use App\Http\Middleware\PostShowLimit;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class PostControllerTest extends TestCase
{
    // use RefreshDatabase;
    // use WithoutMiddleware;

    /**
     * @test
     */
    function TOPページで、ブログ一覧が表示される()
    {
        // Ver.8.51未満の場合で、500エラーが出た場合のエラー確認方法
        //
        // $this->withoutExceptionHandling();
        // ブラウザで確認できる場合は、ブラウザで確認する方法もある
        // エラーログを確認する

        // $this->withoutExceptionHandling();

        // $post1 = Post::factory()->create();
        // $post2 = Post::factory()->create();

        // $this->get('/')
        //     ->assertOk()
        //     ->assertSee($post1->title)
        //     ->assertSee($post2->title);

        $post1 = Post::factory()->hasComments(3)->create(['title' => 'ブログのタイトル1']);
        $post2 = Post::factory()->hasComments(5)->create(['title' => 'ブログのタイトル2']);
        Post::factory()->hasComments(1)->create();

        $this->get('/')
            ->assertOk()
            ->assertSee('ブログのタイトル1')
            ->assertSee('ブログのタイトル2')
            ->assertSee($post1->user->name)
            ->assertSee($post2->user->name)
            ->assertSee('(3件のコメント)')
            ->assertSee('(5件のコメント)')
            ->assertSeeInOrder([
                '(5件のコメント)',
                '(3件のコメント)',
                '(1件のコメント)',
            ]);
    }

    /**
     * @test
     */
    function ブログの一覧で、非公開のブログは表示されない()
    {
        $post1 = Post::factory()->closed()->create([
            'title' => 'これは非公開のブログです',
        ]);

        $post2 = Post::factory()->create([
            'title' => 'これは公開済みのブログです',
        ]); // 公開されているデータ

        $this->get('/')
            ->assertDontSee('これは非公開のブログです')
            ->assertSee('これは公開済みのブログです');
    }

    /**
     * @test
     */
    function ブログの詳細画面が表示でき、コメントが古い順に表示される()
    {
        // $this->withoutMiddleware(PostShowLimit::class);

        $post = Post::factory()->create();

        [$comment1, $comment2, $comment3] = Comment::factory()->createMany([
            ['created_at' => now()->sub('2 days'), 'name' => 'コメント太郎', 'post_id' => $post->id,],
            ['created_at' => now()->sub('3 days'), 'name' => 'コメント次郎', 'post_id' => $post->id,],
            ['created_at' => now()->sub('1 days'), 'name' => 'コメント三郎', 'post_id' => $post->id,],
        ]);

        $this->get('posts/' . $post->id)
            ->assertOk()
            ->assertSee($post->title)
            ->assertSee($post->user->name)
            ->assertSeeInOrder(
                [
                    'コメント次郎',
                    'コメント太郎',
                    'コメント三郎'
                ]
            );
    }

    /**
     * @test
     */
    function ブログで非公開のものは、詳細画面は表示できない()
    {
        $post = Post::factory()->closed()->create(); // 非公開のテストデータ

        $this->get('posts/' . $post->id)
            ->assertForbidden();
    }

    /**
     * @test
     */
    function クリスマスの日は、メリークリスマス！と表示される()
    {
        $post = Post::factory()->create();

        Carbon::setTestNow('2020-12-24');

        $this->get('posts/' . $post->id)
            ->assertOk()
            ->assertDontSee('メリークリスマス！');

        Carbon::setTestNow('2020-12-25');

        $this->get('posts/' . $post->id)
            ->assertOk()
            ->assertSee('メリークリスマス！');
    }

    /**
     * @test
     */
    function factoryの観察()
    {
        // $post = Post::factory()->make(['user_id' => null]);
        // dump($post);
        // dump($post->toArray());

        // dump(User::get()->toArray());

        $this->assertTrue(true);
    }

    /**
     * @test
     */
    function ブログの詳細画面がランダムな文字列が表示されている()
    {
        // \Str::shouldReceive('random')
        //     ->once()
        //     ->with(10)
        //     ->andReturn('HELLOWORLD');

        // $this->instance(
        //     StrRandom::class,
        //     Mockery::mock(StrRandom::class, function (MockInterface $mock) {
        //         $mock->shouldReceive('get')
        //             ->once()
        //             ->with(10)
        //             ->andReturn('HELLOWORLD');
        //     })
        // );

        // 編集
        $mock = Mockery::mock(StrRandom::class);

        $mock->shouldReceive('get')->once()->with(10)->andReturn('HELLOWORLD');

        $this->instance(StrRandom::class, $mock);
        // ここまで

        $post = Post::factory()->create();

        $this->get('posts/' . $post->id)
            ->assertOk()
            ->assertSee('HELLOWORLD');
    }
}
```

- `$ php artisan test --filter ブログの詳細画面がランダムな文字列が表示されている`を実行  

```:terminal

   PASS  Tests\Feature\Http\Controllers\PostControllerTest
  ✓ ブログの詳細画面がランダムな文字列が表示されている

  Tests:  1 passed
  Time:   0.46s
```

`tests/Feature/Http/Controllers/PostControllerTest.php`を編集  

```php:PostControllerTest.php
<?php

namespace Tests\Feature\Http\Controllers;

use App\Actions\StrRandom;
use App\Http\Middleware\PostShowLimit;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class PostControllerTest extends TestCase
{
    // use RefreshDatabase;
    // use WithoutMiddleware;

    /**
     * @test
     */
    function TOPページで、ブログ一覧が表示される()
    {
        // Ver.8.51未満の場合で、500エラーが出た場合のエラー確認方法
        //
        // $this->withoutExceptionHandling();
        // ブラウザで確認できる場合は、ブラウザで確認する方法もある
        // エラーログを確認する

        // $this->withoutExceptionHandling();

        // $post1 = Post::factory()->create();
        // $post2 = Post::factory()->create();

        // $this->get('/')
        //     ->assertOk()
        //     ->assertSee($post1->title)
        //     ->assertSee($post2->title);

        $post1 = Post::factory()->hasComments(3)->create(['title' => 'ブログのタイトル1']);
        $post2 = Post::factory()->hasComments(5)->create(['title' => 'ブログのタイトル2']);
        Post::factory()->hasComments(1)->create();

        $this->get('/')
            ->assertOk()
            ->assertSee('ブログのタイトル1')
            ->assertSee('ブログのタイトル2')
            ->assertSee($post1->user->name)
            ->assertSee($post2->user->name)
            ->assertSee('(3件のコメント)')
            ->assertSee('(5件のコメント)')
            ->assertSeeInOrder([
                '(5件のコメント)',
                '(3件のコメント)',
                '(1件のコメント)',
            ]);
    }

    /**
     * @test
     */
    function ブログの一覧で、非公開のブログは表示されない()
    {
        $post1 = Post::factory()->closed()->create([
            'title' => 'これは非公開のブログです',
        ]);

        $post2 = Post::factory()->create([
            'title' => 'これは公開済みのブログです',
        ]); // 公開されているデータ

        $this->get('/')
            ->assertDontSee('これは非公開のブログです')
            ->assertSee('これは公開済みのブログです');
    }

    /**
     * @test
     */
    function ブログの詳細画面が表示でき、コメントが古い順に表示される()
    {
        // $this->withoutMiddleware(PostShowLimit::class);

        $post = Post::factory()->create();

        [$comment1, $comment2, $comment3] = Comment::factory()->createMany([
            ['created_at' => now()->sub('2 days'), 'name' => 'コメント太郎', 'post_id' => $post->id,],
            ['created_at' => now()->sub('3 days'), 'name' => 'コメント次郎', 'post_id' => $post->id,],
            ['created_at' => now()->sub('1 days'), 'name' => 'コメント三郎', 'post_id' => $post->id,],
        ]);

        $this->get('posts/' . $post->id)
            ->assertOk()
            ->assertSee($post->title)
            ->assertSee($post->user->name)
            ->assertSeeInOrder(
                [
                    'コメント次郎',
                    'コメント太郎',
                    'コメント三郎'
                ]
            );
    }

    /**
     * @test
     */
    function ブログで非公開のものは、詳細画面は表示できない()
    {
        $post = Post::factory()->closed()->create(); // 非公開のテストデータ

        $this->get('posts/' . $post->id)
            ->assertForbidden();
    }

    /**
     * @test
     */
    function クリスマスの日は、メリークリスマス！と表示される()
    {
        $post = Post::factory()->create();

        Carbon::setTestNow('2020-12-24');

        $this->get('posts/' . $post->id)
            ->assertOk()
            ->assertDontSee('メリークリスマス！');

        Carbon::setTestNow('2020-12-25');

        $this->get('posts/' . $post->id)
            ->assertOk()
            ->assertSee('メリークリスマス！');
    }

    /**
     * @test
     */
    function factoryの観察()
    {
        // $post = Post::factory()->make(['user_id' => null]);
        // dump($post);
        // dump($post->toArray());

        // dump(User::get()->toArray());

        $this->assertTrue(true);
    }

    /**
     * @test
     */
    function ブログの詳細画面がランダムな文字列が表示されている()
    {
        // \Str::shouldReceive('random')
        //     ->once()
        //     ->with(10)
        //     ->andReturn('HELLOWORLD');

        // $this->instance(
        //     StrRandom::class,
        //     Mockery::mock(StrRandom::class, function (MockInterface $mock) {
        //         $mock->shouldReceive('get')
        //             ->once()
        //             ->with(10)
        //             ->andReturn('HELLOWORLD');
        //     })
        // );

        // $mock = Mockery::mock(StrRandom::class);

        // $mock->shouldReceive('get')->once()->with(10)->andReturn('HELLOWORLD');

        // $this->instance(StrRandom::class, $mock);

        // 編集
        $mock = $this->mock(StrRandom::class, function (MockInterface $mock) {
            $mock->shouldReceive('get')->once()->with(10)->andReturn('HELLOWORLD');
        });
        // ここまで

        $post = Post::factory()->create();

        $this->get('posts/' . $post->id)
            ->assertOk()
            ->assertSee('HELLOWORLD');
    }
}
```

- `$ php artisan test --filter ブログの詳細画面がランダムな文字列が表示されている`を実行  

```:terminal
   PASS  Tests\Feature\Http\Controllers\PostControllerTest
  ✓ ブログの詳細画面がランダムな文字列が表示されている

  Tests:  1 passed
  Time:   0.35s
```

## 64. aliaseについて

`app/Actions/StrRandom.php`を編集  

```php:StrRandom.php
<?php

namespace App\Actions;

// use Illuminate\Support\Str; // 追加
// or
use Str;

class StrRandom
{
    public function get($length)
    {
        // return \Str::random($length);
        return Str::random($length); // エイリアスを使用しない場合
    }
}
```

## 65. Unitテスト

- `$ php artisan make:test Actions/StrRandomTest --unit`を実行  

`tests/unit/Actions/StrRamdomTest.php`を編集  

```php:StrRandomTest.php
<?php

namespace Tests\Unit\Actions;

use App\Actions\StrRandom; // 追加
use PHPUnit\Framework\TestCase;

class StrRandomTest extends TestCase
{
    // 追加
    /**
     * @test
     */
    function StrRandom_正しい文字数を返す()
    {
        $random = new StrRandom();

        $ret1 = $random->get(8);
        $ret2 = $random->get(10);

        $this->assertTrue(strlen($ret1) === 8);
        $this->assertTrue(strlen($ret2) === 10);
    }

    /**
     * @test
     */
    function strRandom_ランダムの文字列を返す()
    {
        $random = new StrRandom();

        $ret1 = $random->get(8);
        $ret2 = $random->get(8);

        $this->assertFalse($ret1 === $ret2);
    }
    // ここまで
}
```

`app/Actions/StrRandom.php`を編集  

```php:StrRandom.php
<?php

namespace App\Actions;

use Illuminate\Support\Str; // こっちにする
// use Str;

class StrRandom
{
    public function get($length)
    {
        // return \Str::random($length);
        return Str::random($length);
    }
}
```

- `$ php artisan test --filter StrRandomTest`を実行  

```:terminal
   PASS  Tests\Unit\Actions\StrRandomTest
  ✓ str random 正しい文字数を返す
  ✓ str random ランダムの文字列を返す

  Tests:  2 passed
  Time:   0.02s
```

※ エイリアスを使用した場合(試したら戻す)

`app/Actions/StrRandom.php`を編集  

```php:StrRandom.php
<?php

namespace App\Actions;

// use Illuminate\Support\Str;
// use Str;

class StrRandom
{
    public function get($length)
    {
        return \Str::random($length);
        // return Str::random($length);
    }
}
```

- `$ php artisan test --filter StrRandomTest`を実行  

```:terminal
   FAIL  Tests\Unit\Actions\StrRandomTest
  ⨯ str random 正しい文字数を返す
  ⨯ str random ランダムの文字列を返す

  ---

  • Tests\Unit\Actions\StrRandomTest > str random 正しい文字数を返す
   PHPUnit\Framework\ExceptionWrapper 

  Class "Str" not found

  at app/Actions/StrRandom.php:12
      8▕ class StrRandom
      9▕ {
     10▕     public function get($length)
     11▕     {
  ➜  12▕         return \Str::random($length);
     13▕         // return Str::random($length);
     14▕     }
     15▕ }
     16▕ 

  • Tests\Unit\Actions\StrRandomTest > str random ランダムの文字列を返す
   PHPUnit\Framework\ExceptionWrapper 

  Class "Str" not found

  at app/Actions/StrRandom.php:12
      8▕ class StrRandom
      9▕ {
     10▕     public function get($length)
     11▕     {
  ➜  12▕         return \Str::random($length);
     13▕         // return Str::random($length);
     14▕     }
     15▕ }
     16▕ 


  Tests:  2 failed
  Time:   0.03s
```

※ エイリアスを使用したい場合  

`tests/unit/Actions/StrRandom.php`を編集  

```php:StrRandom.php
<?php

namespace Tests\Unit\Actions;

use App\Actions\StrRandom;
use PHPUnit\Framework\TestCase;

class StrRandomTest extends TestCase
{
    /**
     * @test
     */
    function StrRandom_正しい文字数を返す()
    {
        class_alias(\Illuminate\Support\Str::class, \Str::class); // 追加

        $random = new StrRandom();

        $ret1 = $random->get(8);
        $ret2 = $random->get(10);

        $this->assertTrue(strlen($ret1) === 8);
        $this->assertTrue(strlen($ret2) === 10);
    }

    /**
     * @test
     */
    function strRandom_ランダムの文字列を返す()
    {
        $random = new StrRandom();

        $ret1 = $random->get(8);
        $ret2 = $random->get(8);

        $this->assertFalse($ret1 === $ret2);
    }
}
```

- `$ php artisan test --filter StrRandomTest`を実行  

```:terminal
   PASS  Tests\Unit\Actions\StrRandomTest
  ✓ str random 正しい文字数を返す
  ✓ str random ランダムの文字列を返す

  Tests:  2 passed
```

- __全て元に戻しておく(エイリアスを使用しない方法に)__  

スピードの確認をしてみる  

- `$ php artisan test --filter StrRandomTest`を実行  

```:terminal
  PASS  Tests\Unit\Actions\StrRandomTest
  ✓ str random 正しい文字数を返す
  ✓ str random ランダムの文字列を返す

  Tests:  2 passed
  Time:   0.02s
```

Feature側のをインポートして使用してみる  

`tests/unit/Actions/StrRandomTest.php`を編集  

```php:StrRandomTest.php
<?php

namespace Tests\Unit\Actions;

use App\Actions\StrRandom;
// use PHPUnit\Framework\TestCase; // コメントアウト
use Tests\TestCase; // 追加

class StrRandomTest extends TestCase
{
    /**
     * @test
     */
    function StrRandom_正しい文字数を返す()
    {
        $random = new StrRandom();

        $ret1 = $random->get(8);
        $ret2 = $random->get(10);

        $this->assertTrue(strlen($ret1) === 8);
        $this->assertTrue(strlen($ret2) === 10);
    }

    /**
     * @test
     */
    function strRandom_ランダムの文字列を返す()
    {
        $random = new StrRandom();

        $ret1 = $random->get(8);
        $ret2 = $random->get(8);

        $this->assertFalse($ret1 === $ret2);
    }
}
```

- `$ php artisan test --filter StrRandomTest`を実行  

```:terminal
   PASS  Tests\Unit\Actions\StrRandomTest
  ✓ str random 正しい文字数を返す
  ✓ str random ランダムの文字列を返す

  Tests:  2 passed
  Time:   0.30s
```

## 66. Unitテスト (補足)

```:text
前回の講義で、Illuminate\Support\Arr とそのエイリアスを使った場合の話をさせていただきました。

例が例だけに誤解を生みかねませんので補足させていただきます。



Arr は、ファサードのようで、実質ファサードではなく、単にエイリアスが設定されているのみです。

通常のファサードの場合は、名前空間をインポートする形で書いても、Laravel 内部でアプリの構築が必要な為、単体テストは行えません。



また、Arr の場合でも、使用するArr のメソッド内で、仮に Laravel アプリに依存する記述があったとしたら、その場合は、単体テストは行えません。（見た感じは無さそうではあります）



予めご了承下さい。
```
