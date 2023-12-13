# Section2

## 59. 他人のブログは削除できない

`tests/Feature/Http/Controllers/Mypage/PostManageControllerTest.php`を編集  

```php:PostManageControllerTest.php
<?php

namespace Tests\Feature\Http\Controllers\Mypage;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PostManageControllerTest extends TestCase
{
    /**
     * @test
     */
    function ゲストはブログを管理できない()
    {
        $loginUrl = 'mypage/login';

        // 認証していない場合
        $this->get('mypage/posts')
            ->assertRedirect($loginUrl);
        $this->get('mypage/posts/create', [])->assertRedirect($loginUrl);
        $this->post('mypage/posts/create', [])->assertRedirect($loginUrl);
        $this->get('mypage/posts/edit/1')->assertRedirect($loginUrl);
        $this->post('mypage/posts/edit/1', [])->assertRedirect($loginUrl);
        $this->delete('mypage/posts/delete/1')->assertRedirect($loginUrl);
    }

    /**
     * @test
     */
    function マイページ、ブログ一覧で自分のデータのみ表示される()
    {
        $user = $this->login();

        $other = Post::factory()->create(); // 他人のプログ投稿の作成
        $mypost = Post::factory()->create(['user_id' => $user->id]); // ログインしているユーザーのブログ投稿が作成できる

        $this->get('mypage/posts')
            ->assertOk()
            ->assertDontSee($other->title)
            ->assertSee($mypost->title);
    }

    /**
     * @test
     */
    function マイページ、ブログの新規登録画面を開ける()
    {
        $this->login();

        $this->get('mypage/posts/create')
            ->assertOk();
    }

    /**
     * @test
     */
    function マイページ、ブログを新規登録できる、公開の場合()
    {
        // $this->withoutExceptionHandling();
        [$taro, $me, $jiro] = User::factory(3)->create();

        $this->login($me);

        $validData = [
            'title' => '私のブログタイトル',
            'body' => '私のブログ本文',
            'status' => '1',
        ];

        // $this->post('mypage/posts/create', $validData)
        //     ->assertRedirect('mypage/posts/edit/1'); // SQLiteのインメモリの場合はこの書き方でも良い

        $response = $this->post('mypage/posts/create', $validData);

        $post = Post::first();

        $response->assertRedirect('mypage/posts/edit/' . $post->id);

        $this->assertDatabaseHas('posts', array_merge($validData, ['user_id' => $me->id]));
    }

    /**
     * @test
     */
    function マイページ、ブログを新規登録できる、非公開の場合()
    {
        // $this->markTestIncomplete(); // テスト作成途中であることを示す。 incempletedと表示される

        [$taro, $me, $jiro] = User::factory(3)->create();

        $this->login($me);

        $validData = [
            'title' => '私のブログタイトル',
            'body' => '私のブログ本文',
            // 'status' => '1',
        ];

        $this->post('mypage/posts/create', $validData);

        $this->assertDatabaseHas(
            'posts',
            array_merge(
                $validData,
                [
                    'user_id' => $me->id,
                    'status' => 0,
                ]
            )
        );
    }

    /**
     * @test
     */
    function マイページ、ブログの登録時の入力チェック()
    {
        $url = 'mypage/posts/create';

        $this->login();

        $this->from($url)->post($url, [])
            ->assertRedirect($url);

        app()->setLocale('testing');

        $this->post($url, ['title' => ''])->assertInvalid(['title' => 'required']);
        $this->post($url, ['title' => str_repeat('a', 256)])->assertInvalid(['title' => 'max']);
        $this->post($url, ['title' => str_repeat('a', 255)])->assertValid('title');
        $this->post($url, ['body' => ''])->assertInvalid(['body' => 'required']);
    }

    /**
     * @test
     */
    function 自分のブログの編集画面は開ける()
    {
        $post = Post::factory()->create();

        $this->login($post->user);

        $this->get('mypage/posts/edit/' . $post->id)
            ->assertOk();
    }

    /**
     * @test
     */
    function 他人様のブログの編集画面は開けない()
    {
        $post = Post::factory()->create();

        $this->login();

        $this->get('mypage/posts/edit/' . $post->id)
            ->assertForbidden();
    }

    /**
     * @test
     */
    function 自分のブログは更新できる()
    {
        $validData = [
            'title' => '新タイトル',
            'body' => '新本文',
            'status' => '1',
        ];

        $post = Post::factory()->create();

        $this->login($post->user);

        $this->post('mypage/posts/edit/' . $post->id, $validData)
            ->assertRedirect('mypage/posts/edit/' . $post->id);

        $this->get('mypage/posts/edit/' . $post->id)
            ->assertSee('ブログを更新しました');

        // DBに登録されている事は確認したが、新規で追加されたかもしれない。なので不完全と言えば、不完全
        $this->assertDatabaseHas('posts', $validData);

        // 一件の投稿を更新していて新しく投稿が追加されていないかの確認
        $this->assertCount(1, Post::all());
        $this->assertSame(1, Post::count());

        // 別の方法のアプローチ
        // 項目が少ない場ときは、fresh()を使う
        $this->assertSame('新タイトル', $post->fresh()->title);
        $this->assertSame('新本文', $post->fresh()->body);

        // 項目が多い時はrefresh()を使うといい
        $post->refresh();
        $this->assertSame('新本文', $post->body);
        $this->assertSame('新本文', $post->body);
        $this->assertSame('新本文', $post->body);
        $this->assertSame('新本文', $post->body);
        $this->assertSame('新本文', $post->body);
    }

    /**
     * @test
     */
    function 他人様のブログは更新できない()
    {
        $validData = [
            'title' => '新タイトル',
            'body' => '新本文',
            'status' => '1',
        ];

        $post = Post::factory()->create(['title' => '元のブログタイトル']);

        $this->login(); // 他人がログインしている

        $this->post('mypage/posts/edit/' . $post->id, $validData)
            ->assertForbidden();

        $this->assertSame('元のブログタイトル', $post->fresh()->title);
    }

    /**
     * @test
     */
    function 自分のブログは削除できる、且つ付随するコメントも削除される()
    {
        $post = Post::factory()->create();

        $myPostComment = Comment::factory()->create(['post_id' => $post->id]);
        $otherPostComment = Comment::factory()->create();

        $this->login($post->user);

        $this->delete('mypage/posts/delete/' . $post->id)
            ->assertRedirect('mypage/posts');

        // assertDeletedは、Ver.9で削除(Delete)された。
        // Ver.8.6.1〜以降は、assertModelMissing()を使いましょう。
        // ブログの削除の確認
        $this->assertModelMissing($post);

        $this->assertModelMissing($myPostComment);
        $this->assertModelExists($otherPostComment);
    }

    /**
     * @test
     */
    function 他人様のブログを削除はできない()
    {
        // 追加
        $post = Post::factory()->create();

        $this->login(); // 他人のログイン

        $this->delete('mypage/posts/delete/' . $post->id)
            ->assertForbidden();

        $this->assertModelExists($post);
        // ここまで
    }
}
```

- `$ php artisan test --filter 他人様のブログを削除はできない`を実行  

```:terminal
  FAIL  Tests\Feature\Http\Controllers\Mypage\PostManageControllerTest
  ⨯ 他人様のブログを削除はできない

  ---

  • Tests\Feature\Http\Controllers\Mypage\PostManageControllerTest > 他人様のブログを削除はできない
  Expected response status code [403] but received 302.
  Failed asserting that 403 is identical to 302.

  at tests/Feature/Http/Controllers/Mypage/PostManageControllerTest.php:260
    256▕ 
    257▕         $this->login(); // 他人のログイン
    258▕ 
    259▕         $this->delete('mypage/posts/delete/' . $post->id)
  ➜ 260▕             ->assertForbidden();
    261▕ 
    262▕         $this->assertModelExists($post);
    263▕     }
    264▕ }


  Tests:  1 failed
  Time:   0.48s
```

`app/Http/Controllers/Mypage/PostManageController.php`を編集  

```php:PostManageController.php
<?php

namespace App\Http\Controllers\Mypage;

use App\Http\Controllers\Controller;
use App\Models\Comment;
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
        $this->authorize('manage-post', $post);

        $data = old() ?: $post;

        return view('mypage.posts.edit', compact('post', 'data'));
    }

    public function update(Request $request, Post $post)
    {
        // 所有チェック
        $this->authorize('manage-post', $post);

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

    public function destroy(Post $post)
    {
        // 所有チェック
        $this->authorize('manage-post', $post); // 追加

        $post->delete(); // 付随するコメントはDBの制約を使って削除する

        return redirect('mypage/posts');
    }
}
```

- `$ php artisan test --filter 他人様のブログを削除はできない`を実行  

```:terminal
   PASS  Tests\Feature\Http\Controllers\Mypage\PostManageControllerTest
  ✓ 他人様のブログを削除はできない

  Tests:  1 passed
  Time:   0.47s
```

`resources/views/mypage/posts/index.blade.php`を編集  

```php:index.blade.php
@extends('layouts.index')

@section('content')
    <h1>マイブログ一覧</h1>

    <a href="/mypage/posts/create">ブログ新規登録</a>
    <hr>


    <table>
        <tr>
            <th>ブログ名</th>
        </tr>

        @foreach ($posts as $post)
            <tr>
                <td>
                    <a href="{{ route('mypage.posts.edit', $post) }}">{{ $post->title }}</a>
                </td>
                // 追加
                <td>
                    <form mthods="post" action="{{ route('mypage.post.delete', $post) }}">
                        @csrf
                        @method('DELETE')
                        <input type="submit" value="削除">
                    </form>
                </td>
                // ここまで
            </tr>
        @endforeach
    </table>
@endsection
```

`routes/web.php`を編集  

```php:web.php
<?php

use App\Http\Controllers\Mypage\PostManageController;
use App\Http\Controllers\Mypage\UserLoginController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\SignupController;
use Illuminate\Support\Facades\Route;

Route::get('', [PostController::class, 'index']);
Route::get('posts/{post}', [PostController::class, 'show'])
    ->name('posts.show')
    ->whereNumber('post'); // 'post'は数値のみに限定という意味

Route::get('signup', [SignupController::class, 'index']);
Route::post('signup', [SignupController::class, 'store']);

Route::get('mypage/login', [UserLoginController::class, 'index'])->name('login');
Route::post('mypage/login', [UserLoginController::class, 'login']);

Route::middleware('auth')->group(function () {
    Route::get('mypage/posts', [PostManageController::class, 'index'])->name('mypage.posts');
    Route::post('mypage/logout', [UserLoginController::class, 'logout'])->name('logout');
    Route::get('mypage/posts/create', [PostManageController::class, 'create']);
    Route::post('mypage/posts/create', [PostManageController::class, 'store']);
    Route::get('mypage/posts/edit/{post}', [PostManageController::class, 'edit'])->name('mypage.posts.edit');
    Route::post('mypage/posts/edit/{post}', [PostManageController::class, 'update']);
    Route::delete('mypage/posts/delete/{post}', [PostManageController::class, 'destroy'])->name('mypage.posts.delete'); // 編集
});
```

- `$ vendor/bin/phpunit`を実行  

```:terminal
PHPUnit 9.6.13 by Sebastian Bergmann and contributors.

................F.........."testing" // tests/Feature/Http/Controllers/SignupControllerTest.php:77
."ja" // tests/Feature/Http/Controllers/SignupControllerTest.php:99
.....                                 33 / 33 (100%)

Time: 00:02.353, Memory: 48.50 MB

There was 1 failure:

1) Tests\Feature\Http\Controllers\Mypage\UserLoginControllerTest::認証エラーなのでvalidationExceptionの例外が発生する
Failed asserting that two strings are identical.
--- Expected
+++ Actual
@@ @@
-'emailは必ず指定してください。'
+'メールアドレスは必ず指定してください。'

/Applications/MAMP/htdocs/lara9test/tests/Feature/Http/Controllers/Mypage/UserLoginControllerTest.php:105

FAILURES!
Tests: 33, Assertions: 133, Failures: 1.
```

`tests/Feature/Http/Controllers/Mypage/UserLoginControllerTest.php`を編集  

```php:UserLoginControllerTest.php
<?php

namespace Tests\Feature\Http\Controllers\Mypage;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class UserLoginControllerTest extends TestCase
{
    /**
     * @test
     */
    function ログイン画面を開ける()
    {
        $this->get('mypage/login')
            ->assertOk();
    }

    /**
     * @test
     */
    function ログイン時の入力チェック()
    {
        $url = 'mypage/login';

        $this->from($url)->post($url, [])
            ->assertRedirect($url);

        app()->setlocale('testing');

        $this->post($url, ['email' => ''])->assertInvalid(['email' => 'required']);
        $this->post($url, ['email' => 'aa@bb@cc'])->assertInvalid(['email' => 'email']);
        $this->post($url, ['email' => 'aa@ああ.いい'])->assertInvalid(['email' => 'email']);
        $this->post($url, ['password' => ''])->assertInvalid(['password' => 'required']);
    }

    /**
     * @test
     */
    function ログインできる()
    {
        $user = User::factory()->create([
            'email' => 'aaa@bbb.net',
            'password' => Hash::make('abcd1234'),
        ]);

        $this->post('mypage/login', [
            'email' => 'aaa@bbb.net',
            'password' => 'abcd1234',
        ])->assertRedirect('mypage/posts');

        $this->assertAuthenticatedAs($user);
    }

    /**
     * @test
     */
    function パスワードを間違えているのでログインできず、適切なエラーメッセージが表示される()
    {
        $url = 'mypage/login';

        $user = User::factory()->create([
            'email' => 'aaa@bbb.net',
            'password' => Hash::make('abcd1234'),
        ]);

        // $this->from($url)->post('mypage/login', [
        //     'email' => 'aaa@bbb.net',
        //     'password' => '11112222',
        // ])->assertRedirect($url);

        // $this->get($url)
        //     ->assertOk()
        //     ->assertSee('メールアドレスかパスワードが間違っています。');

        $this->from($url)->followingRedirects()->post($url, [
            'email' => 'aaa@bbb.net',
            'password' => '11112222',
        ])
            ->assertOk()
            ->assertSee('メールアドレスかパスワードが間違っています。')
            ->assertSee('<h1>ログイン画面</h1>', false);
    }

    /**
     * @test
     */
    function 認証エラーなのでvalidationExceptionの例外が発生する()
    {
        $this->withoutExceptionHandling();

        // $this->expectException(ValidationException::class);

        try {
            $this->post('mypage/login', [])
                ->assertRedirect();
            $this->fail('例外が発生しませんでしたよ。');
        } catch (ValidationException $e) {
            $this->assertSame(
                'メールアドレスは必ず指定してください。', // 編集
                $e->errors()['email'][0],
            );
        }
    }

    /**
     * @test
     */
    function 認証OKなのでvalidationExceptionの例外が発生しない()
    {
        $this->withoutExceptionHandling();

        $user = User::factory()->create([
            'email' => 'aaa@bbb.net',
            'password' => Hash::make('abcd1234'),
        ]);

        try {
            $this->post('mypage/login', [
                'email' => 'aaa@bbb.net',
                'password' => 'abcd1234',
            ])->assertRedirect();
        } catch (ValidationException $e) {
            $this->vail('例外が発生してしまいましたよ。');
        }
    }

    /**
     * @test
     */
    function ログアウトできる()
    {
        $this->login();

        $this->post('mypage/logout')
            ->assertRedirect('mypage/login');

        $this->get('mypage/login')
            ->assertSee('ログアウトしました。');

        $this->assertGuest();
    }
}
```

- `$ vendor/bin/phpunit`を実行  

```:terminal
PHPUnit 9.6.13 by Sebastian Bergmann and contributors.

..........................."testing" // tests/Feature/Http/Controllers/SignupControllerTest.php:77
."ja" // tests/Feature/Http/Controllers/SignupControllerTest.php:99
.....                                 33 / 33 (100%)

Time: 00:02.415, Memory: 48.50 MB

OK (33 tests, 133 assertions)
```

## 60. withoutMiddlewareの注意点

`app/Http/Controllers/Mypage/PostManageController.php`を編集  

```php:PostManageController.php
<?php

namespace App\Http\Controllers\Mypage;

use App\Http\Controllers\Controller;
use App\Models\Comment;
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

        return redirect('mypage/posts/edit/' . $post->id)
            ->with('status', 'ブログを登録しました'); // 編集
    }

    public function edit(Post $post)
    {
        $this->authorize('manage-post', $post);

        $data = old() ?: $post;

        return view('mypage.posts.edit', compact('post', 'data'));
    }

    public function update(Request $request, Post $post)
    {
        // 所有チェック
        $this->authorize('manage-post', $post);

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

    public function destroy(Post $post)
    {
        // 所有チェック
        $this->authorize('manage-post', $post);

        $post->delete(); // 付随するコメントはDBの制約を使って削除する

        return redirect(route('mypage.posts'));
    }
}
```

- `$ php artisan make:middleware PostShowLimit`を実行  

`app/Http/Middleware/PostShowLimit.php`を編集  

```php:PostShowLimit.php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PostShowLimit
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // 追加
        if (!in_array($request->ip(), ['192.168.255.255'], true)) {
            abort(403, 'Your IP is not valid.');
        }
        // ここまで

        return $next($request);
    }
}
```

`routes/web.php`を編集  

```php:web.php
<?php

use App\Http\Controllers\Mypage\PostManageController;
use App\Http\Controllers\Mypage\UserLoginController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\SignupController;
use App\Http\Middleware\PostShowLimit;
use Illuminate\Support\Facades\Route;

Route::get('', [PostController::class, 'index']);
// 編集
Route::get('posts/{post}', [PostController::class, 'show'])
    ->name('posts.show')
    ->whereNumber('post')
    ->middleware(PostShowLimit::class); // 'post'は数値のみに限定という意味
// ここまで

Route::get('signup', [SignupController::class, 'index']);
Route::post('signup', [SignupController::class, 'store']);

Route::get('mypage/login', [UserLoginController::class, 'index'])->name('login');
Route::post('mypage/login', [UserLoginController::class, 'login']);

Route::middleware('auth')->group(function () {
    Route::get('mypage/posts', [PostManageController::class, 'index'])->name('mypage.posts');
    Route::post('mypage/logout', [UserLoginController::class, 'logout'])->name('logout');
    Route::get('mypage/posts/create', [PostManageController::class, 'create']);
    Route::post('mypage/posts/create', [PostManageController::class, 'store']);
    Route::get('mypage/posts/edit/{post}', [PostManageController::class, 'edit'])->name('mypage.posts.edit');
    Route::post('mypage/posts/edit/{post}', [PostManageController::class, 'update']);
    Route::delete('mypage/posts/delete/{post}', [PostManageController::class, 'destroy'])->name('mypage.posts.delete');
});
```

- `$ php artisan test --filter ブログの詳細画面が表示でき、コメントが古い順に表示される`を実行  

```:terminal
 FAIL  Tests\Feature\Http\Controllers\PostControllerTest
  ⨯ ブログの詳細画面が表示でき、コメントが古い順に表示される

  ---

  • Tests\Feature\Http\Controllers\PostControllerTest > ブログの詳細画面が表示でき、コメントが古い順に表示される
  Expected response status code [200] but received 403.
  Failed asserting that 200 is identical to 403.

  at tests/Feature/Http/Controllers/PostControllerTest.php:89
     85▕             ['created_at' => now()->sub('1 days'),'name' => 'コメント三郎', 'post_id' => $post->id,],
     86▕         ]);
     87▕ 
     88▕         $this->get('posts/' . $post->id)
  ➜  89▕             ->assertOk()
     90▕             ->assertSee($post->title)
     91▕             ->assertSee($post->user->name)
     92▕             ->assertSeeInOrder(
     93▕                 [


  Tests:  1 failed
  Time:   0.48s
```

`tests/Feature/Http/Controller/PostControllerTest.php`を編集  

```php:PostControllerTest.php
<?php

namespace Tests\Feature\Http\Controllers;

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
        $this->withoutMiddleware(); // 追加

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
}
```

- `$ php artisan test --filter ブログの詳細画面が表示でき、コメントが古い順に表示される`を実行  

```:terminal
 FAIL  Tests\Feature\Http\Controllers\PostControllerTest
  ⨯ ブログの詳細画面が表示でき、コメントが古い順に表示される

  ---

  • Tests\Feature\Http\Controllers\PostControllerTest > ブログの詳細画面が表示でき、コメントが古い順に表示される
  Expected response status code [200] but received 403.
  Failed asserting that 200 is identical to 403.

  at tests/Feature/Http/Controllers/PostControllerTest.php:93
     89▕             ['created_at' => now()->sub('1 days'), 'name' => 'コメント三郎', 'post_id' => $post->id,],
     90▕         ]);
     91▕ 
     92▕         $this->get('posts/' . $post->id)
  ➜  93▕             ->assertOk()
     94▕             ->assertSee($post->title)
     95▕             ->assertSee($post->user->name)
     96▕             ->assertSeeInOrder(
     97▕                 [


  Tests:  1 failed
  Time:   0.48s
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
        $this->withoutMiddleware(PostShowLimit::class); // 編集

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
}
```

- `$ php artisan test --filter ブログの詳細画面が表示でき、コメントが古い順に表示される`を実行  

```:terminal
   PASS  Tests\Feature\Http\Controllers\PostControllerTest
  ✓ ブログの詳細画面が表示でき、コメントが古い順に表示される

  Tests:  1 passed
  Time:   0.45s
```

- ipを調べる  

`app/Http/Middleware/PostShowLimit.php`を編集  

```php:PostShowLimit.php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PostShowLimit
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        dd($request->ip()); // 追加 確認したら削除する

        if (!in_array($request->ip(), ['192.168.255.255'], true)) {
            abort(403, 'Your IP is not valid.');
        }

        return $next($request);
    }
}
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
        // $this->withoutMiddleware(PostShowLimit::class); // コメントアウトしておく 確認後コメンアウトを解除

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
}
```

- `$ php artisan test --filter ブログの詳細画面が表示でき、コメントが古い順に表示される`を実行  

```:terminal
"127.0.0.1" // app/Http/Middleware/PostShowLimit.php:19
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
        // $this->withoutMiddleware(PostShowLimit::class); 再度コメントアウト

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
}
```

`app/Http/Middleware/PostShowLimit.php`を編集  

```php:PostShowLimit.php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PostShowLimit
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // 追加
        if ($this->runningUnitTests()) {
            return $next($request);
        }
        // ここまで testの時は無効になるようにする

        if (!in_array($request->ip(), ['192.168.255.255'], true)) {
            abort(403, 'Your IP is not valid.');
        }

        return $next($request);
    }

    // 追加
    protected function runningUnitTests()
    {
        return app()->runningInConsole() && app()->runningUnitTests();
    }
    // ここまで
}
```

- `$ php artisan test --filter ブログの詳細画面が表示でき、コメントが古い順に表示される`を実行  

```:terminal
   PASS  Tests\Feature\Http\Controllers\PostControllerTest
  ✓ ブログの詳細画面が表示でき、コメントが古い順に表示される

  Tests:  1 passed
  Time:   0.64s
```

`routes/web.php`を編集  

```php:web.php
<?php

use App\Http\Controllers\Mypage\PostManageController;
use App\Http\Controllers\Mypage\UserLoginController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\SignupController;
use App\Http\Middleware\PostShowLimit;
use Illuminate\Support\Facades\Route;

Route::get('', [PostController::class, 'index']);
Route::get('posts/{post}', [PostController::class, 'show'])
    ->name('posts.show')
    ->whereNumber('post'); // 'post'は数値のみに限定という意味
// ->middleware(PostShowLimit::class); // コメントアウト

Route::get('signup', [SignupController::class, 'index']);
Route::post('signup', [SignupController::class, 'store']);

Route::get('mypage/login', [UserLoginController::class, 'index'])->name('login');
Route::post('mypage/login', [UserLoginController::class, 'login']);

Route::middleware('auth')->group(function () {
    Route::get('mypage/posts', [PostManageController::class, 'index'])->name('mypage.posts');
    Route::post('mypage/logout', [UserLoginController::class, 'logout'])->name('logout');
    Route::get('mypage/posts/create', [PostManageController::class, 'create']);
    Route::post('mypage/posts/create', [PostManageController::class, 'store']);
    Route::get('mypage/posts/edit/{post}', [PostManageController::class, 'edit'])->name('mypage.posts.edit');
    Route::post('mypage/posts/edit/{post}', [PostManageController::class, 'update']);
    Route::delete('mypage/posts/delete/{post}', [PostManageController::class, 'destroy'])->name('mypage.posts.delete');
});
```
