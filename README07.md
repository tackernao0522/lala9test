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

## 32. コメントデータの表示

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

    // 追加
     <h2>コメント</h2>
    {{-- @foreach ($post->comments()->oldest()->get() as $comment)
        <hr>
        <p>{{ $comment->name }} ({{ $comment->created_at }})</p>
        <p>{!! nl2br(e($comment->body)) !!}</p>
    @endforeach --}}
    // ここまで
@endsection
```

`tests/Feature/Http/Controllers/PostControllerTest.php`を編集  

```php:PostControllerTest.php
<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Comment;
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
    function ブログの詳細画面が表示でき、コメントが古い順に表示される()
    {
        $post = Post::factory()->create();

        Comment::factory()->create([
            'created_at' => now()->sub('2 days'),
            'name' => 'コメント太郎',
            'post_id' => $post->id,
        ]);

        Comment::factory()->create([
            'created_at' => now()->sub('3 days'),
            'name' => 'コメント次郎',
            'post_id' => $post->id,
        ]);

        Comment::factory()->create([
            'created_at' => now()->sub('1 days'),
            'name' => 'コメント三郎',
            'post_id' => $post->id,
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

    // 編集
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

- `$ php artisan test --filter ブログの詳細画面が表示でき、コメントが古い順に表示される`を実行  

```:terminal
   FAIL  Tests\Feature\Http\Controllers\PostControllerTest
  ⨯ ブログの詳細画面が表示でき、コメントが古い順に表示される

  ---

  • Tests\Feature\Http\Controllers\PostControllerTest > ブログの詳細画面が表示でき、コメントが古い順に表示される
  Failed asserting that Failed asserting that '<!DOCTYPE html>
  <html lang="ja">
  
  <head>
      <meta charset="UTF-8">
      <title>ブログ</title>
      <link type="text/css" rel="stylesheet" href="/css/style.css">
  </head>
  
  <body>
          
  
      
      
  
      <h1>牛乳屋ぎゅうに立ってします。ほんと小さ。</h1>
      <div>ってらあると、いってやろう。どんどん小さな波なみの実みだが、そんなのでしょうてできてるんでいちょうざんにすが少しひらべてにわからないの活字かつじをしているよう」ジョバンニは自分はんぶんいたむのを見ながれてながカムパネルラが、またたためにさっきらっしょに苹果りんこう岸ぎしのかない。天の川がほんも植うえられたまえはもういちめんにも四つに分けてもかけたようとした。琴ことばで、すっと、ちょうほう、おっし。</div>
  
      <p>書き手: 青山 加奈</p>
  
      <h2>コメント</h2>
      
  </body>
  
  </html>
  ' contains "コメント次郎" in specified order..

  at tests/Feature/Http/Controllers/PostControllerTest.php:106
    102▕             ->assertSee($post->title)
    103▕             ->assertSee($post->user->name)
    104▕             ->assertSeeInOrder(
    105▕                 [
  ➜ 106▕                     'コメント次郎',
    107▕                     'コメント太郎',
    108▕                     'コメント三郎'
    109▕                 ]
    110▕             );


  Tests:  1 failed
  Time:   0.43s
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
    <div>{!! nl2br(e($post->body)) !!}</div>

    <p>書き手: {{ $post->user->name }}</p>

    <h2>コメント</h2>

    // コメントアウト解除
    @foreach ($post->comments()->oldest()->get() as $comment)
        <hr>
        <p>{{ $comment->name }} ({{ $comment->created_at }})</p>
        <p>{!! nl2br(e($comment->body)) !!}</p>
    @endforeach
    // ここまで
@endsection
```

- `$ php artisan test --filter ブログの詳細画面が表示でき、コメントが古い順に表示される`を実行  

```:terminal
  PASS  Tests\Feature\Http\Controllers\PostControllerTest
  ✓ ブログの詳細画面が表示でき、コメントが古い順に表示される

  Tests:  1 passed
  Time:   0.34s
```

`database/factories/CommentFactory.php`を編集  

```php:CommentFactory.php
<?php

namespace Database\Factories;

use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Comment>
 */
class CommentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'post_id' => Post::factory(),
            'name' => $this->faker->name(),
            'body' => $this->faker->realText(20),
            'created_at' => $this->faker->dateTimeBetween('-30days', '-1days') // 追加
        ];
    }
}
```

- `$ php artisan migrate:refresh --seed`を実行  

### リファクタリング

`tests/Feature/Http/Controllers/PostControllerTest.php`を編集  

```php:PostControllerTest.php
<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Comment;
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
    function ブログの詳細画面が表示でき、コメントが古い順に表示される()
    {
        $post = Post::factory()->create();

        // 編集
        [$comment1, $comment2, $comment3] = Comment::factory()->createMany([
            ['created_at' => now()->sub('2 days'), 'name' => 'コメント太郎', 'post_id' => $post->id,],
            ['created_at' => now()->sub('3 days'),'name' => 'コメント次郎', 'post_id' => $post->id,],
            ['created_at' => now()->sub('1 days'),'name' => 'コメント三郎', 'post_id' => $post->id,],
        ]);
        // ここまで

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
}
```

- `$ php artisan test --filter ブログの詳細画面が表示でき、コメントが古い順に表示される`を実行  

```:terminal
  PASS  Tests\Feature\Http\Controllers\PostControllerTest
  ✓ ブログの詳細画面が表示でき、コメントが古い順に表示される

  Tests:  1 passed
  Time:   0.32s
```

- `$ vendor/bin/phpunit`を実行  

```:terminal
PHPUnit 9.6.13 by Sebastian Bergmann and contributors.

..........                                                        10 / 10 (100%)

Time: 00:00.791, Memory: 44.00 MB

OK (10 tests, 26 assertions)
```
