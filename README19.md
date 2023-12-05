# Section2

## 57. 他人のブログは更新できない

`tests/Feature/Http/Controllers/Mypage/PostManageControllerTest.php`を編集  

```php:PostManageControllerTest.php
<?php

namespace Tests\Feature\Http\Controllers\Mypage;

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
        // 追加
        $validData = [
            'title' => '新タイトル',
            'body' => '新本文',
            'status' => '1',
        ];

        $post = Post::factory()->create();

        $this->login(); // 他人がログインしている

        $this->post('mypage/posts/edit/' . $post->id, $validData)
            ->assertForbidden();
        // ここまで
    }

    /**
     * @test
     */
    function 他人様のブログを削除はできない()
    {
    }
}
```

- `$ php artisan test --filter 他人様のブログは更新できない`を実行  

```:terminal
  FAIL  Tests\Feature\Http\Controllers\Mypage\PostManageControllerTest
  ⨯ 他人様のブログは更新できない

  ---

  • Tests\Feature\Http\Controllers\Mypage\PostManageControllerTest > 他人様のブログは更新できない
  Expected response status code [403] but received 302.
  Failed asserting that 403 is identical to 302.

  at tests/Feature/Http/Controllers/Mypage/PostManageControllerTest.php:219
    215▕ 
    216▕         $this->login(); // 他人がログインしている
    217▕ 
    218▕         $this->post('mypage/posts/edit/' . $post->id, $validData)
  ➜ 219▕             ->assertForbidden();
    220▕     }
    221▕ 
    222▕     /**
    223▕      * @test


  Tests:  1 failed
  Time:   0.35s
```

`app/Http/Controllers/Mypage/PostManageController.php`を編集  

```php:PostManageController.php
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
        // 追加
        if (auth()->user()->isNot($post->user)) {
            abort(403);
        }
        // ここまで

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
```

- `$ php artisan test --filter 他人様のブログは更新できない`を実行  

```:terminal
   PASS  Tests\Feature\Http\Controllers\Mypage\PostManageControllerTest
  ✓ 他人様のブログは更新できない

  Tests:  1 passed
  Time:   0.34s
```

- __リファクタリング__  

- `$ php artisan make:policy PostPolicy`を実行  

`app/Plicies/PostPolicy.php`を編集  

```php:PostPolicy.php
<?php

namespace App\Policies;

use App\Models\Post; // 追加
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PostPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    // 追加
    public function manage(User $user, Post $post)
    {
        return $user->is($post->user);
    }
    // ここまで
}
```

`app/Providers/AuthServiceProvider.php`を編集  

```php:AuthServiceProvider.php
<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        'App\Models\Post' => 'App\Policies\PostPolicy', // 編集
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::define('manage-post', 'App\Policies\PostPolicy@manage'); // 追加
    }
}
```

`app/Http/Controllers/Mypage/PostManageController.php`を編集  

```php:PostManageController.php
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
        $this->authorize('manage-post', $post); // 編集

        $data = old() ?: $post;

        return view('mypage.posts.edit', compact('post', 'data'));
    }

    public function update(Request $request, Post $post)
    {
        // 所有チェック
        $this->authorize('manage-post', $post); // 編集

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
```

`tests/Feature/Http/Controllers/Mypage/PostManageControllerTest.php`を編集  

```php:PostManageControllerTest.php
<?php

namespace Tests\Feature\Http\Controllers\Mypage;

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

        $post = Post::factory()->create(['title' => '元のブログタイトル']); // 編集

        $this->login(); // 他人がログインしている

        $this->post('mypage/posts/edit/' . $post->id, $validData)
            ->assertForbidden();

        $this->assertSame('元のブログタイトル', $post->fresh()->title); // 追加
    }

    /**
     * @test
     */
    function 他人様のブログを削除はできない()
    {
    }
}
```

`app/Http/Controllers/Mypage/PostManageController.php`を編集  

```php:PostManageController.php
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
        $this->authorize('manage-post', $post);

        $data = old() ?: $post;

        return view('mypage.posts.edit', compact('post', 'data'));
    }

    public function update(Request $request, Post $post)
    {
        $data = $request->validate([
            'title' => ['required', 'max:255'],
            'body' => ['required'],
            'status' => ['nullable', 'boolean'],
        ]);

        $data['status'] = $request->boolean('status');

        $post->update($data);

        // 所有チェック
        $this->authorize('manage-post', $post); // ここに移動してみる

        return redirect(route('mypage.posts.edit', $post))
            ->with('status', 'ブログを更新しました');
    }
}
```

- `$ php artisan test --filter 他人様のブログは更新できない`を実行  

```:terminal

   FAIL  Tests\Feature\Http\Controllers\Mypage\PostManageControllerTest
  ⨯ 他人様のブログは更新できない

  ---

  • Tests\Feature\Http\Controllers\Mypage\PostManageControllerTest > 他人様のブログは更新できない
  Failed asserting that two strings are identical.

  at tests/Feature/Http/Controllers/Mypage/PostManageControllerTest.php:221
    217▕ 
    218▕         $this->post('mypage/posts/edit/' . $post->id, $validData)
    219▕             ->assertForbidden();
    220▕ 
  ➜ 221▕         $this->assertSame('元のブログタイトル', $post->fresh()->title);
    222▕     }
    223▕ 
    224▕     /**
    225▕      * @test
  --- Expected
  +++ Actual
  @@ @@
  -'元のブログタイトル'
  +'新タイトル'

  Tests:  1 failed
  Time:   0.44s
```

`app/Http/Controllers/Mypage/PostManageController.php`を編集  

```php:PostManageController.php
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
        $this->authorize('manage-post', $post);

        $data = old() ?: $post;

        return view('mypage.posts.edit', compact('post', 'data'));
    }

    public function update(Request $request, Post $post)
    {
        // 所有チェック
        $this->authorize('manage-post', $post); // 元の場所に戻す

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
```

- `$ php artisan test --filter 他人様のブログは更新できない`を実行  

```:terminal
   PASS  Tests\Feature\Http\Controllers\Mypage\PostManageControllerTest
  ✓ 他人様のブログは更新できない

  Tests:  1 passed
  Time:   0.36s
```
