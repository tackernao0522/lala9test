# Section2

## 52. ブログの新規登録処理、 その1

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
        $this->get('mypage/posts/create')->assertRedirect($loginUrl);
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

    // 追加
    /**
     * @test
     */
    function マイページ、ブログを新規登録できる、公開の場合()
    {

    }

    /**
     * @test
     */
    function マイページ、ブログを新規登録できる、非公開の場合()
    {

    }

    /**
     * @test
     */
    function マイページ、ブログの登録時の入力チェック()
    {

    }
    // ここまで
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
        $this->get('mypage/posts/create')->assertRedirect($loginUrl);
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
        // 追加
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

        // $response->assertRedirect('mypage/posts/edit/' . $post->id);

        $this->assertDatabaseHas('posts', array_merge($validData, ['user_id' => $me->id]));
        // ここまで
    }

    /**
     * @test
     */
    function マイページ、ブログを新規登録できる、非公開の場合()
    {
    }

    /**
     * @test
     */
    function マイページ、ブログの登録時の入力チェック()
    {
    }
}
```

- `$ php artisan test --filter マイページ、ブログを新規登録できる、公開の場合`を実行  

```:console
   FAIL  Tests\Feature\Http\Controllers\Mypage\PostManageControllerTest
  ⨯ マイページ、ブログを新規登録できる、公開の場合

  ---

  • Tests\Feature\Http\Controllers\Mypage\PostManageControllerTest > マイページ、ブログを新規登録できる、公開の場合
  Failed asserting that a row in the table [posts] matches the attributes {
      "title": "私のブログタイトル",
      "body": "私のブログ本文",
      "status": "1",
      "user_id": 2
  }.
  
  The table is empty.

  at tests/Feature/Http/Controllers/Mypage/PostManageControllerTest.php:77
     73▕         $post = Post::first();
     74▕ 
     75▕         // $response->assertRedirect('mypage/posts/edit', $post->id);
     76▕ 
  ➜  77▕         $this->assertDatabaseHas('posts', array_merge($validData, ['user_id' => $me->id]));
     78▕     }
     79▕ 
     80▕     /**
     81▕      * @test


  Tests:  1 failed
  Time:   0.57s
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
        $this->get('mypage/posts/create')->assertRedirect($loginUrl);
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
        $this->withoutExceptionHandling(); // 追加
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

        // $response->assertRedirect('mypage/posts/edit/' . $post->id);

        $this->assertDatabaseHas('posts', array_merge($validData, ['user_id' => $me->id]));
    }

    /**
     * @test
     */
    function マイページ、ブログを新規登録できる、非公開の場合()
    {
    }

    /**
     * @test
     */
    function マイページ、ブログの登録時の入力チェック()
    {
    }
}
```

- `$ php artisan test --filter マイページ、ブログを新規登録できる、公開の場合`を実行  

```:console
   FAIL  Tests\Feature\Http\Controllers\Mypage\PostManageControllerTest
  ⨯ マイページ、ブログを新規登録できる、公開の場合

  ---

  • Tests\Feature\Http\Controllers\Mypage\PostManageControllerTest > マイページ、ブログを新規登録できる、公開の場合
   PHPUnit\Framework\ExceptionWrapper 

  The POST method is not supported for route mypage/posts/create. Supported methods: GET, HEAD.

  at vendor/laravel/framework/src/Illuminate/Routing/AbstractRouteCollection.php:122
    118▕      * @throws \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException
    119▕      */
    120▕     protected function requestMethodNotAllowed($request, array $others, $method)
    121▕     {
  ➜ 122▕         throw new MethodNotAllowedHttpException(
    123▕             $others,
    124▕             sprintf(
    125▕                 'The %s method is not supported for route %s. Supported methods: %s.',
    126▕                 $method,


  Tests:  1 failed
  Time:   0.28s
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
    Route::post('mypage/posts/create', [PostManageController::class, 'store']); // 追加
});
```

- `$ php artisan test --filter マイページ、ブログを新規登録できる、公開の場合`を実行  

```:console
   FAIL  Tests\Feature\Http\Controllers\Mypage\PostManageControllerTest
  ⨯ マイページ、ブログを新規登録できる、公開の場合

  ---

  • Tests\Feature\Http\Controllers\Mypage\PostManageControllerTest > マイページ、ブログを新規登録できる、公開の場合
   PHPUnit\Framework\ExceptionWrapper 

  Method App\Http\Controllers\Mypage\PostManageController::store does not exist.

  at vendor/laravel/framework/src/Illuminate/Routing/Controller.php:68
     64▕      * @throws \BadMethodCallException
     65▕      */
     66▕     public function __call($method, $parameters)
     67▕     {
  ➜  68▕         throw new BadMethodCallException(sprintf(
     69▕             'Method %s::%s does not exist.', static::class, $method
     70▕         ));
     71▕     }
     72▕ }


  Tests:  1 failed
  Time:   0.28s
```

`app/Http/Controllers/Mypage/PostManageController.php`を編集  

```php:PostManageController.php
<?php

namespace App\Http\Controllers\Mypage;

use App\Http\Controllers\Controller;
use App\Models\Post;
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

    // 追加
    public function store(Request $request)
    {
        $data = $request->only('title', 'body', 'status');

        auth()->user()->posts()->create($data);
    }
    // ここまで
}
```

- `$ php artisan test --filter マイページ、ブログを新規登録できる、公開の場合`を実行  

```:console
   FAIL  Tests\Feature\Http\Controllers\Mypage\PostManageControllerTest
  ⨯ マイページ、ブログを新規登録できる、公開の場合

  ---

  • Tests\Feature\Http\Controllers\Mypage\PostManageControllerTest > マイページ、ブログを新規登録できる、公開の場合
   PHPUnit\Framework\ExceptionWrapper 

  Add [title] to fillable property to allow mass assignment on [App\Models\Post].

  at vendor/laravel/framework/src/Illuminate/Database/Eloquent/Model.php:523
    519▕             } elseif ($totallyGuarded || static::preventsSilentlyDiscardingAttributes()) {
    520▕                 if (isset(static::$discardedAttributeViolationCallback)) {
    521▕                     call_user_func(static::$discardedAttributeViolationCallback, $this, [$key]);
    522▕                 } else {
  ➜ 523▕                     throw new MassAssignmentException(sprintf(
    524▕                         'Add [%s] to fillable property to allow mass assignment on [%s].',
    525▕                         $key, get_class($this)
    526▕                     ));
    527▕                 }


  Tests:  1 failed
  Time:   0.29s
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

    protected $guarded = []; // 追加

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
        return $this->status == self::CLOSED;
    }
}
```

- `$ php artisan test --filter マイページ、ブログを新規登録できる、公開の場合`を実行  

```:console
   PASS  Tests\Feature\Http\Controllers\Mypage\PostManageControllerTest
  ✓ マイページ、ブログを新規登録できる、公開の場合

  Tests:  1 passed
  Time:   0.28s
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
        $this->get('mypage/posts/create')->assertRedirect($loginUrl);
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
        $this->withoutExceptionHandling();
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

        $response->assertRedirect('mypage/posts/edit/' . $post->id); // コメントアウト解除

        $this->assertDatabaseHas('posts', array_merge($validData, ['user_id' => $me->id]));
    }

    /**
     * @test
     */
    function マイページ、ブログを新規登録できる、非公開の場合()
    {
    }

    /**
     * @test
     */
    function マイページ、ブログの登録時の入力チェック()
    {
    }
}
```

- `$ php artisan test --filter マイページ、ブログを新規登録できる、公開の場合`を実行  

```:console
   FAIL  Tests\Feature\Http\Controllers\Mypage\PostManageControllerTest
  ⨯ マイページ、ブログを新規登録できる、公開の場合

  ---

  • Tests\Feature\Http\Controllers\Mypage\PostManageControllerTest > マイページ、ブログを新規登録できる、公開の場合
  Expected response status code [201, 301, 302, 303, 307, 308] but received 200.
  Failed asserting that false is true.

  at tests/Feature/Http/Controllers/Mypage/PostManageControllerTest.php:76
     72▕         $response = $this->post('mypage/posts/create', $validData);
     73▕ 
     74▕         $post = Post::first();
     75▕ 
  ➜  76▕         $response->assertRedirect('mypage/posts/edit', $post->id);
     77▕ 
     78▕         $this->assertDatabaseHas('posts', array_merge($validData, ['user_id' => $me->id]));
     79▕     }
     80▕ 


  Tests:  1 failed
  Time:   0.34s
```

`app/Http/Controllers/Mypage/PostManageController.php`を編集  

```php:PostManageController.php
<?php

namespace App\Http\Controllers\Mypage;

use App\Http\Controllers\Controller;
use App\Models\Post;
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
        $data = $request->only('title', 'body', 'status');

        $post = auth()->user()->posts()->create($data); // 編集

        return redirect('mypage/posts/edit/' . $post->id); // 追加
    }
}
```

- `$ php artisan test --filter マイページ、ブログを新規登録できる、公開の場合`を実行  

```:console
  PASS  Tests\Feature\Http\Controllers\Mypage\PostManageControllerTest
  ✓ マイページ、ブログを新規登録できる、公開の場合

  Tests:  1 passed
  Time:   0.31s
```

## 53. ブログの新規登録処理、その2

- やり忘れ  

`tests/Feature/Http/Controllers/Mypage/PostManagerControllerTest.php`を編集  

```php:PostManagerControllerTest.php
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
        $this->get('mypage/posts/create', [])->assertRedirect($loginUrl); // 編集
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
    }

    /**
     * @test
     */
    function マイページ、ブログの登録時の入力チェック()
    {
    }
}
```

- `$ php artisan test --filter ゲストはブログを管理できない`を実行  

```:terminal
   PASS  Tests\Feature\Http\Controllers\Mypage\PostManageControllerTest
  ✓ ゲストはブログを管理できない

  Tests:  1 passed
  Time:   0.33s
```

`tests/Feature/Http/Controllers/Mypage/PostManagerControllerTest.php`を編集  

```php:PostManagerControllerTest.php
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

    // 追加
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
    // ここまで

    /**
     * @test
     */
    function マイページ、ブログの登録時の入力チェック()
    {
    }
}
```

- `$ php artisan test --filter マイページ、ブログを新規登録できる、非公開の場合`を実行  

```:terminal
  FAIL  Tests\Feature\Http\Controllers\Mypage\PostManageControllerTest
  ⨯ マイページ、ブログを新規登録できる、非公開の場合

  ---

  • Tests\Feature\Http\Controllers\Mypage\PostManageControllerTest > マイページ、ブログを新規登録できる、非公開の場合
  Failed asserting that a row in the table [posts] matches the attributes {
      "title": "私のブログタイトル",
      "body": "私のブログ本文",
      "user_id": 2,
      "status": 0
  }.
  
  The table is empty.
  
  The following exception occurred during the last request:
  
    <!-- 〜省略〜 -->
  
  ----------------------------------------------------------------------------------
  
  SQLSTATE[23000]: Integrity constraint violation: 19 NOT NULL constraint failed: posts.status (SQL: insert into "posts" ("title", "body", "user_id", "updated_at", "created_at") values (私のブログタイトル, 私のブログ本文, 2, 2023-11-27 13:45:25, 2023-11-27 13:45:25))

  at tests/Feature/Http/Controllers/Mypage/PostManageControllerTest.php:106
    102▕             array_merge(
    103▕                 $validData,
    104▕                 [
    105▕                     'user_id' => $me->id,
  ➜ 106▕                     'status' => 0,
    107▕                 ]
    108▕             )
    109▕         );
    110▕     }


  Tests:  1 failed
  Time:   0.72s
```

`app/Http/Controllers/Mypage/PostManagerController.php`を編集  

```php:PostManagerController.php
<?php

namespace App\Http\Controllers\Mypage;

use App\Http\Controllers\Controller;
use App\Models\Post;
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
        $data = $request->only('title', 'body'); // 編集

        $data['status'] = $request->boolean('status'); // 追加

        $post = auth()->user()->posts()->create($data);

        return redirect('mypage/posts/edit/' . $post->id);
    }
}
```

- `$ php artisan test --filter マイページ、ブログを新規登録できる、非公開の場合`を実行  

```:terminal
   PASS  Tests\Feature\Http\Controllers\Mypage\PostManageControllerTest
  ✓ マイページ、ブログを新規登録できる、非公開の場合

  Tests:  1 passed
  Time:   0.28s
```
