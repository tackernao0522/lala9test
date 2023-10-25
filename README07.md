# Section2

## 31. クリスマスの日は、メリークリスマス！と表示される

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
    <div>{!! nl2br(e($post->body)) !!}</div>

    <p>書き手: {{ $post->user->name }}</p>
@endsection
```

`tests/Feature/Http/Controllers/PostControllerTest.php`を編集  

```php:PostControllerTest.php
<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PostControllerTest extends TestCase
{
    // use RefreshDatabase;

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
    function ブログの詳細画面が表示できる()
    {
        $post = Post::factory()->create();

        $this->get('posts/' . $post->id)
            ->assertOk()
            ->assertSee($post->title)
            ->assertSee($post->user->name);
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

    // 追加
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
    // ここまで

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
    {{-- @if (today()->is('12-25'))
        <h1>メリークリスマス！</h1>
    @endif --}} // 一旦全部コメントアウト


    <h1>{{ $post->title }}</h1>
    <div>{!! nl2br(e($post->body)) !!}</div>

    <p>書き手: {{ $post->user->name }}</p>
@endsection
```

- `$ php artisan test --filter クリスマスの日は、メリークリスマス！と表示される`を実行  

```:terminal
 FAIL  Tests\Feature\Http\Controllers\PostControllerTest
  ⨯ クリスマスの日は、メリークリスマス！と表示される

  ---

 <!-- 省略 -->

  at tests/Feature/Http/Controllers/PostControllerTest.php:115
    111▕         Carbon::setTestNow('2020-12-25');
    112▕ 
    113▕         $this->get('posts/' . $post->id)
    114▕             ->assertOk()
  ➜ 115▕             ->assertSee('メリークリスマス！');
    116▕     }
    117▕ 
    118▕     /**
    119▕      * @test


  Tests:  1 failed
  Time:   0.67s
```

`resources/views/posts/show.blade.php`を編集  

```php:show.blade.php
@extends('layouts.index')

@section('content')
    {{-- @if (date('md') == '1225')
        <h1>メリークリスマス！</h1>
    @endif --}}

    {{-- @if (today()->date('md') == '1225') --}}
    {{-- @if (today()->is('12-25')) --}} // if文だけコメントアウト
        <h1>メリークリスマス！</h1>
    {{-- @endif --}}


    <h1>{{ $post->title }}</h1>
    <div>{!! nl2br(e($post->body)) !!}</div>

    <p>書き手: {{ $post->user->name }}</p>
@endsection
```

- `$ php artisan test --filter クリスマスの日は、メリークリスマス！と表示される`を実行  

```:terminal
   FAIL  Tests\Feature\Http\Controllers\PostControllerTest
  ⨯ クリスマスの日は、メリークリスマス！と表示される

  ---

  <!-- 省略 -->

  at tests/Feature/Http/Controllers/PostControllerTest.php:109
    105▕         Carbon::setTestNow('2020-12-24');
    106▕ 
    107▕         $this->get('posts/' . $post->id)
    108▕             ->assertOk()
  ➜ 109▕             ->assertDontSee('メリークリスマス！');
    110▕ 
    111▕         Carbon::setTestNow('2020-12-25');
    112▕ 
    113▕         $this->get('posts/' . $post->id)


  Tests:  1 failed
  Time:   0.34s
```

`resources/views/posts/show.blade.php`を編集  

```php:show.blade.php
@extends('layouts.index')

@section('content')
    {{-- @if (date('md') == '1225')
        <h1>メリークリスマス！</h1>
    @endif --}}

    {{-- @if (today()->date('md') == '1225') --}}
    @if (today()->id('12-25')) // コメントアウト解除
        <h1>メリークリスマス！</h1>
    @endif // コメントアウト解除


    <h1>{{ $post->title }}</h1>
    <div>{!! nl2br(e($post->body)) !!}</div>

    <p>書き手: {{ $post->user->name }}</p>
@endsection
```

`$ php artisan test --filter クリスマスの日は、メリークリスマス！と表示される`を実行  

```:terminal
  PASS  Tests\Feature\Http\Controllers\PostControllerTest
  ✓ クリスマスの日は、メリークリスマス！と表示される

  Tests:  1 passed
  Time:   0.35s
```
