# Section02

## 29. ブログの詳細画面の表示

`app/Http/Controllers/PostListController`を編集  

```php:PostListController.php
<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PostController extends Controller // PostControllerにする
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
}
```

- ファイル名も`PostController.php`に変更  

`routes/web.php`を編集  

```php:web.php
<?php

use App\Http\Controllers\PostController; // 修正
use Illuminate\Support\Facades\Route;

Route::get('', [PostController::class, 'index']); // 修正
```

`tests/Feature/Http/Controllers/PostListControllerTest.php`を編集  

```php:PostListControllerTest
<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PostControllerTest extends TestCase // PostControllerTestに変更
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

- ファイル名も`PostControllerTest.php`に変更  

`tests/Feature/Http/Controllers/PostControllerTest.php`を編集  

```php:PostControllerTest.php
<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Post;
use App\Models\User;
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

    // 追加
    /**
     * @test
     */
    function ブログの詳細画面が表示できる()
    {

    }

    /**
     * @test
     */
    function ブログで非公開のものは、詳細画面は表示できない()
    {

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

- `$ vendor/bin/phpunit`を実行  

```:terminal
PHPUnit 9.6.13 by Sebastian Bergmann and contributors.

..RR....                                                            8 / 8 (100%)

Time: 00:00.632, Memory: 42.00 MB

There were 2 risky tests:

1) Tests\Feature\Http\Controllers\PostControllerTest::ブログの詳細画面が表示できる
This test did not perform any assertions

/Applications/MAMP/htdocs/lara9test/tests/Feature/Http/Controllers/PostControllerTest.php:76

2) Tests\Feature\Http\Controllers\PostControllerTest::ブログで非公開のものは、詳細画面は表示できない
This test did not perform any assertions

/Applications/MAMP/htdocs/lara9test/tests/Feature/Http/Controllers/PostControllerTest.php:84

OK, but incomplete, skipped, or risky tests!
Tests: 8, Assertions: 15, Risky: 2.
```

`tests/Feature/Http/Controllers/PostControllerTest.php`を編集  

```php:PostControllerTest.php
<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PostControllerTest extends TestCase
{
    // use RefreshDatabase;

    // 〜省略〜

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

    }

    // 〜省略〜
}
```

- `$ php artisan test --filter ブログの詳細画面が表示できる`を実行  

```:terminal
  FAIL  Tests\Feature\Http\Controllers\PostControllerTest
  ⨯ ブログの詳細画面が表示できる

  ---

  • Tests\Feature\Http\Controllers\PostControllerTest > ブログの詳細画面が表示できる
  Expected response status code [200] but received 404.
  Failed asserting that 200 is identical to 404.

  at tests/Feature/Http/Controllers/PostControllerTest.php:81
     77▕     {
     78▕         $post = Post::factory()->create();
     79▕ 
     80▕         $this->get('posts/' . $post->id)
  ➜  81▕             ->assertOk()
     82▕             ->assertSee($post->title)
     83▕             ->assertSee($post->user->name);
     84▕     }
     85▕ 


  Tests:  1 failed
  Time:   0.34s
```

`routes/web.php`を編集  

```php:web.php
<?php

use App\Http\Controllers\PostController;
use Illuminate\Support\Facades\Route;

Route::get('', [PostController::class, 'index']);
// 追加
Route::get('posts/{post}', [PostController::class, 'show'])
->name('posts.show')
->whereNumber('post'); // 'post'は数値のみに限定という意味 
```

- `$ php artisan test --filter ブログの詳細画面が表示できる`を実行  

```:terminal
 FAIL  Tests\Feature\Http\Controllers\PostControllerTest
  ⨯ ブログの詳細画面が表示できる

  ---

  • Tests\Feature\Http\Controllers\PostControllerTest > ブログの詳細画面が表示できる
  Expected response status code [200] but received 500.
  Failed asserting that 200 is identical to 500.
  
  The following exception occurred during the last request:
  
  <!-- 〜省略〜 -->
  
  Method App\Http\Controllers\PostController::show does not exist.

  at tests/Feature/Http/Controllers/PostControllerTest.php:81
     77▕     {
     78▕         $post = Post::factory()->create();
     79▕ 
     80▕         $this->get('posts/' . $post->id)
  ➜  81▕             ->assertOk()
     82▕             ->assertSee($post->title)
     83▕             ->assertSee($post->user->name);
     84▕     }
     85▕ 


  Tests:  1 failed
  Time:   0.50s
```

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

    // 追加
    public function show(Post $post)
    {

    }
}
```

- `$ php artisan test --filter ブログの詳細画面が表示できる`を実行  

```:terminal
 FAIL  Tests\Feature\Http\Controllers\PostControllerTest
  ⨯ ブログの詳細画面が表示できる

  ---

  • Tests\Feature\Http\Controllers\PostControllerTest > ブログの詳細画面が表示できる
  Failed asserting that '' contains "たりと遠くかねて立っているのでしたが、。".

  at tests/Feature/Http/Controllers/PostControllerTest.php:82
     78▕         $post = Post::factory()->create();
     79▕ 
     80▕         $this->get('posts/' . $post->id)
     81▕             ->assertOk()
  ➜  82▕             ->assertSee($post->title)
     83▕             ->assertSee($post->user->name);
     84▕     }
     85▕ 
     86▕     /**


  Tests:  1 failed
  Time:   0.34s
```

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
            ->onlyOpen() // ->where('status', Post::OPEN)
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
        return view('posts.show', compact('post')); // 追加
    }
}
```

- `$ mkdir resources/views/posts && touch $_/show.blade.php`を実行  

`resources/views/posts/show.blade.php`を編集  

```php:show.blade.php
@extends('layouts.index')

@section('content')
    <h1>{{ $post->title }}</h1>
    <div>{!! nl2br(e($post->body)) !!}</div>

    <p>書き手: {{ $post->user->name }}</p>
@endsection
```

- `$ php artisan test --filter ブログの詳細画面が表示できる`を実行  

```:terminal
   PASS  Tests\Feature\Http\Controllers\PostControllerTest
  ✓ ブログの詳細画面が表示できる

  Tests:  1 passed
  Time:   0.36s
```

`resources/views/index.blade.php`を編集  

```php:index.blade.php
@extends('layouts.index')

@section('content')
    <h1>ブログ一覧</h1>

    <ul>
        @foreach ($posts as $post)
            <li>
                // 編集
                <a href="{{ route('posts.show', $post) }}">
                    {{ $post->title }}
                </a>
                // ここまで
                {{ $post->user->name }}
                ({{ $post->comments_count }}件のコメント)
            </li>
        @endforeach
    </ul>
@endsection
```

## 30. ブログの詳細画面で非公開のものは表示されない

- [ブログ記事](https://zenn.dev/nshiro/articles/73c98a4d19d486)  

`tests/Feature/Http/Controllers/PostControllerTest.php`を編集  

```php:PostControllerTest.php
<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Post;
use App\Models\User;
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
        // 追加
        $post = Post::factory()->closed()->create(); // 非公開のテストデータ

        $this->get('posts/' . $post->id)
            ->assertForbidden();
        // ここまで
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

- `$ php artisan test --filter ブログで非公開のものは、詳細画面は表示できない`を実行  

```:terminal
    FAIL  Tests\Feature\Http\Controllers\PostControllerTest
  ⨯ ブログで非公開のものは、詳細画面は表示できない

  ---

  • Tests\Feature\Http\Controllers\PostControllerTest > ブログで非公開のものは、詳細画面は表示できない
  Expected response status code [403] but received 200.
  Failed asserting that 403 is identical to 200.

  at tests/Feature/Http/Controllers/PostControllerTest.php:94
     90▕     {
     91▕         $post = Post::factory()->closed()->create(); // 非公開のテストデータ
     92▕ 
     93▕         $this->get('posts/' . $post->id)
  ➜  94▕             ->assertForbidden();
     95▕     }
     96▕ 
     97▕     /**
     98▕      * @test


  Tests:  1 failed
  Time:   0.38s
```

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
        // 追加
        if ($post->status == Post::CLOSED) {
            abort(403);
        }
        // ここまで

        return view('posts.show', compact('post'));
    }
}
```

- `$ php artisan test --filter ブログで非公開のものは、詳細画面は表示できない`を実行  

```:terminal
  PASS  Tests\Feature\Http\Controllers\PostControllerTest
  ✓ ブログで非公開のものは、詳細画面は表示できない

  Tests:  1 passed
  Time:   0.34s
```

- __リファクタリング__  

`app/Models/Post.php`を編集  

```php:Post.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    const OPEN = 1;
    const CLOSED = 0;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function scopeOnlyOpen($query)
    {
        $query->where('status', self::OPEN);
    }

    // 追加
    public function isClosed()
    {
        return $this->status == self::CLOSED;
    }
}
```

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
        // 編集
        if ($post->isClosed()) {
            abort(403);
        }
        // ここまで

        return view('posts.show', compact('post'));
    }
}
```

`$ php artisan test --filter ブログで非公開のものは、詳細画面は表示できない`を実行  

```:terminal
  PASS  Tests\Feature\Http\Controllers\PostControllerTest
  ✓ ブログで非公開のものは、詳細画面は表示できない

  Tests:  1 passed
  Time:   0.45s
```

`tests/Feature/Models/PostTest.php`を編集  

```php:PostTest.php
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

    /**
     * @test
     */
    function ブログの公開・非公開のscope()
    {
        $post1 = Post::factory()->closed()->create();
        $post2 = Post::factory()->create(); // 公開されているデータ

        $posts = Post::onlyOpen()->get();

        $this->assertFalse($posts->contains($post1));
        $this->assertTrue($posts->contains($post2));
    }

    // 追加
    /**
     * @test
     */
    function ブログで非公開の時は、trueを返し、公開時は、falseを返す()
    {
        $open = Post::factory()->create();
        $closed = Post::factory()->closed()->create();

        $this->assertFalse($open->isClosed());
        $this->assertTrue($closed->isClosed());
    }
    // ここまで
}
```

`app/Models/Post.php`を編集  

```php:Post.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    const OPEN = 1;
    const CLOSED = 0;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function scopeOnlyOpen($query)
    {
        $query->where('status', self::OPEN);
    }

    public function isClosed()
    {
        return true; // 追加
        return $this->status == self::CLOSED;
    }
}
```

- `$ php artisan test --filter ブログで非公開の時は、trueを返し、公開時は、falseを返す`を実行  

```:terminal
   FAIL  Tests\Feature\Models\PostTest
  ⨯ ブログで非公開の時は、trueを返し、公開時は、falseを返す

  ---

  • Tests\Feature\Models\PostTest > ブログで非公開の時は、trueを返し、公開時は、falseを返す
  Failed asserting that true is false.

  at tests/Feature/Models/PostTest.php:60
     56▕     {
     57▕         $open = Post::factory()->create();
     58▕         $closed = Post::factory()->closed()->create();
     59▕ 
  ➜  60▕         $this->assertFalse($open->isClosed());
     61▕         $this->assertTrue($closed->isClosed());
     62▕     }
     63▕ }
     64▕ 


  Tests:  1 failed
  Time:   0.61s
```

`app/Models/Post.php`を編集  

```php:Post.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    const OPEN = 1;
    const CLOSED = 0;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function scopeOnlyOpen($query)
    {
        $query->where('status', self::OPEN);
    }

    public function isClosed()
    {
        return false; // 追加
        return $this->status == self::CLOSED;
    }
}
```

- `$ php artisan test --filter ブログで非公開の時は、trueを返し、公開時は、falseを返す`を実行  

```:terminal

   FAIL  Tests\Feature\Models\PostTest
  ⨯ ブログで非公開の時は、trueを返し、公開時は、falseを返す

  ---

  • Tests\Feature\Models\PostTest > ブログで非公開の時は、trueを返し、公開時は、falseを返す
  Failed asserting that false is true.

  at tests/Feature/Models/PostTest.php:61
     57▕         $open = Post::factory()->create();
     58▕         $closed = Post::factory()->closed()->create();
     59▕ 
     60▕         $this->assertFalse($open->isClosed());
  ➜  61▕         $this->assertTrue($closed->isClosed());
     62▕     }
     63▕ }
     64▕ 


  Tests:  1 failed
  Time:   0.30s
```

`app/Models/Post.php`を編集  

```php:Post.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    const OPEN = 1;
    const CLOSED = 0;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function scopeOnlyOpen($query)
    {
        $query->where('status', self::OPEN);
    }

    public function isClosed()
    {
        // return false; 削除
        return $this->status == self::CLOSED;
    }
}
```

- `$ php artisan test --filter ブログで非公開の時は、trueを返し、公開時は、falseを返す`を実行  

```:terminal
 PASS  Tests\Feature\Models\PostTest
  ✓ ブログで非公開の時は、trueを返し、公開時は、falseを返す

  Tests:  1 passed
  Time:   0.40s
```

`tests/Feature/Models/PostTest.php`を編集  

```php:PostTest.php
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

    /**
     * @test
     */
    function ブログの公開・非公開のscope()
    {
        $post1 = Post::factory()->closed()->create();
        $post2 = Post::factory()->create(); // 公開されているデータ

        $posts = Post::onlyOpen()->get();

        $this->assertFalse($posts->contains($post1));
        $this->assertTrue($posts->contains($post2));
    }

    /**
     * @test
     */
    function ブログで非公開の時は、trueを返し、公開時は、falseを返す()
    {
        $open = Post::factory()->make(); // instanceを作成しないmakeでもOK
        $closed = Post::factory()->closed()->make(); // instanceを作成しないmakeでもOK

        $this->assertFalse($open->isClosed());
        $this->assertTrue($closed->isClosed());
    }
}
```

`$ php artisan test --filter ブログで非公開の時は、trueを返し、公開時は、falseを返す`を実行  

```:terminal
   PASS  Tests\Feature\Models\PostTest
  ✓ ブログで非公開の時は、trueを返し、公開時は、falseを返す

  Tests:  1 passed
  Time:   0.30s
```
