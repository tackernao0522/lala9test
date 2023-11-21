# Section2

## 49. ブラウザ表示の確認

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
    Route::get('mypage/posts', [PostManageController::class, 'index'])->name('mypage.posts'); // 編集
    Route::post('mypage/logout', [UserLoginController::class, 'logout'])->name('logout'); // 編集
});
```

`resources/views/layouts/index.blade.php`を編集  

```php:index.blade.php
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>ブログ</title>
    <link type="text/css" rel="stylesheet" href="/css/style.css">
</head>

<body>
    // 追加
    <nav>
        <li><a href="/">TOP（ブログ一覧）</a></li>

        @auth
            <li><a href="{{ route('maypage.posts') }}">マイブログ一覧</a></li>
            <li>
                <form action="{{ route('logout') }}" mthod=POST>
                    @csrf
                    <input type="submit" value="ログアウト">
                </form>
            </li>
        @else
            <li><a href="{{ route('login') }}">ログイン</a></li>
        @endauth
    </nav>
    // ここまで
    @yield('content')
</body>

</html>
```

- ユーザー登録してみる  

`database/seeders/DatabaseSeeder.php`を編集  

```php:DatabaseSeeder.php
<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        // Post::factory(30)->create();

        // 編集
        [$first] = User::factory(15)->create()->each(function ($user) {
            // ランダムで2〜5件のブログ投稿をする
            Post::factory(random_int(2, 5))->random()->create(['user_id' => $user])
                ->each(function ($post) {
                    Comment::factory(random_int(1, 5))->create(['post_id' => $post]);
                });
        });

        $first->update([
            'name' => 'takaki',
            'email' => 'takaki55730317@gmail.com',
            'password' => Hash::make('password123'),
        ]);
        // ここまで
    }
}
```

- `$ php artisan migrate:fresh --seed`を実行  

`lang/ja/validation.php`を編集  

```php:validation.php
<?php

return [

    /*
    |--------------------------------------------------------------------------
    | バリデーション言語行
    |--------------------------------------------------------------------------
    |
    | 以下の言語行はバリデタークラスにより使用されるデフォルトのエラー
    | メッセージです。サイズルールのようにいくつかのバリデーションを
    | 持っているものもあります。メッセージはご自由に調整してください。
    |
    */

    'accepted'             => ':attributeを承認してください。',
    'accepted_if' => ':otherが:valueの場合、:attributeを承認してください。',
    'active_url'           => ':attributeが有効なURLではありません。',
    'after'                => ':attributeには、:dateより後の日付を指定してください。',
    'after_or_equal'       => ':attributeには、:date以降の日付を指定してください。',
    'alpha'                => ':attributeはアルファベットのみがご利用できます。',
    'alpha_dash'           => ':attributeはアルファベットとダッシュ(-)及び下線(_)がご利用できます。',
    'alpha_num'            => ':attributeはアルファベット数字がご利用できます。',
    'array'                => ':attributeは配列でなくてはなりません。',
    'before'               => ':attributeには、:dateより前の日付をご利用ください。',
    'before_or_equal'      => ':attributeには、:date以前の日付をご利用ください。',
    'between'              => [
        'numeric' => ':attributeは、:minから:maxの間で指定してください。',
        'file'    => ':attributeは、:min kBから、:max kBの間で指定してください。',
        'string'  => ':attributeは、:min文字から、:max文字の間で指定してください。',
        'array'   => ':attributeは、:min個から:max個の間で指定してください。',
    ],
    'boolean'              => ':attributeは、trueかfalseを指定してください。',
    'confirmed'            => ':attributeと、確認フィールドとが、一致していません。',
    'current_password'     => 'パスワードが正しくありません。',
    'date'                 => ':attributeには有効な日付を指定してください。',
    'date_equals'          => ':attributeには、:dateと同じ日付けを指定してください。',
    'date_format'          => ':attributeは:format形式で指定してください。',
    'different'            => ':attributeと:otherには、異なった内容を指定してください。',
    'digits'               => ':attributeは:digits桁で指定してください。',
    'digits_between'       => ':attributeは:min桁から:max桁の間で指定してください。',
    'dimensions'           => ':attributeの図形サイズが正しくありません。',
    'distinct'             => ':attributeには異なった値を指定してください。',
    'email'                => ':attributeには、有効なメールアドレスを指定してください。',
    'ends_with'            => ':attributeには、:valuesのどれかで終わる値を指定してください。',
    'exists'               => '選択された:attributeは正しくありません。',
    'file'                 => ':attributeにはファイルを指定してください。',
    'filled'               => ':attributeに値を指定してください。',
    'gt'                   => [
        'numeric' => ':attributeには、:valueより大きな値を指定してください。',
        'file'    => ':attributeには、:value kBより大きなファイルを指定してください。',
        'string'  => ':attributeは、:value文字より長く指定してください。',
        'array'   => ':attributeには、:value個より多くのアイテムを指定してください。',
    ],
    'gte'                  => [
        'numeric' => ':attributeには、:value以上の値を指定してください。',
        'file'    => ':attributeには、:value kB以上のファイルを指定してください。',
        'string'  => ':attributeは、:value文字以上で指定してください。',
        'array'   => ':attributeには、:value個以上のアイテムを指定してください。',
    ],
    'image'                => ':attributeには画像ファイルを指定してください。',
    'in'                   => '選択された:attributeは正しくありません。',
    'in_array'             => ':attributeには:otherの値を指定してください。',
    'integer'              => ':attributeは整数で指定してください。',
    'ip'                   => ':attributeには、有効なIPアドレスを指定してください。',
    'ipv4'                 => ':attributeには、有効なIPv4アドレスを指定してください。',
    'ipv6'                 => ':attributeには、有効なIPv6アドレスを指定してください。',
    'json'                 => ':attributeには、有効なJSON文字列を指定してください。',
    'lt'                   => [
        'numeric' => ':attributeには、:valueより小さな値を指定してください。',
        'file'    => ':attributeには、:value kBより小さなファイルを指定してください。',
        'string'  => ':attributeは、:value文字より短く指定してください。',
        'array'   => ':attributeには、:value個より少ないアイテムを指定してください。',
    ],
    'lte'                  => [
        'numeric' => ':attributeには、:value以下の値を指定してください。',
        'file'    => ':attributeには、:value kB以下のファイルを指定してください。',
        'string'  => ':attributeは、:value文字以下で指定してください。',
        'array'   => ':attributeには、:value個以下のアイテムを指定してください。',
    ],
    'max'                  => [
        'numeric' => ':attributeには、:max以下の数字を指定してください。',
        'file'    => ':attributeには、:max kB以下のファイルを指定してください。',
        'string'  => ':attributeは、:max文字以下で指定してください。',
        'array'   => ':attributeは:max個以下指定してください。',
    ],
    'mimes'                => ':attributeには:valuesタイプのファイルを指定してください。',
    'mimetypes'            => ':attributeには:valuesタイプのファイルを指定してください。',
    'min'                  => [
        'numeric' => ':attributeには、:min以上の数字を指定してください。',
        'file'    => ':attributeには、:min kB以上のファイルを指定してください。',
        'string'  => ':attributeは、:min文字以上で指定してください。',
        'array'   => ':attributeは:min個以上指定してください。',
    ],
    'multiple_of' => ':attributeには、:valueの倍数を指定してください。',
    'not_in'               => '選択された:attributeは正しくありません。',
    'not_regex'            => ':attributeの形式が正しくありません。',
    'numeric'              => ':attributeには、数字を指定してください。',
    'password'             => '正しいパスワードを指定してください。',
    'present'              => ':attributeが存在していません。',
    'regex'                => ':attributeに正しい形式を指定してください。',
    'required'             => ':attributeは必ず指定してください。',
    'required_if'          => ':otherが:valueの場合、:attributeも指定してください。',
    'required_unless'      => ':otherが:valuesでない場合、:attributeを指定してください。',
    'required_with'        => ':valuesを指定する場合は、:attributeも指定してください。',
    'required_with_all'    => ':valuesを指定する場合は、:attributeも指定してください。',
    'required_without'     => ':valuesを指定しない場合は、:attributeを指定してください。',
    'required_without_all' => ':valuesのどれも指定しない場合は、:attributeを指定してください。',
    'prohibited'           => ':attributeは入力禁止です。',
    'prohibited_if' => ':otherが:valueの場合、:attributeは入力禁止です。',
    'prohibited_unless'    => ':otherが:valueでない場合、:attributeは入力禁止です。',
    'prohibits'            => 'attributeは:otherの入力を禁じています。',
    'same'                 => ':attributeと:otherには同じ値を指定してください。',
    'size'                 => [
        'numeric' => ':attributeは:sizeを指定してください。',
        'file'    => ':attributeのファイルは、:sizeキロバイトでなくてはなりません。',
        'string'  => ':attributeは:size文字で指定してください。',
        'array'   => ':attributeは:size個指定してください。',
    ],
    'starts_with'          => ':attributeには、:valuesのどれかで始まる値を指定してください。',
    'string'               => ':attributeは文字列を指定してください。',
    'timezone'             => ':attributeには、有効なゾーンを指定してください。',
    'unique'               => ':attributeの値は既に存在しています。',
    'uploaded'             => ':attributeのアップロードに失敗しました。',
    'url'                  => ':attributeに正しい形式を指定してください。',
    'uuid'                 => ':attributeに有効なUUIDを指定してください。',

    /*
    |--------------------------------------------------------------------------
    | Custom バリデーション言語行
    |--------------------------------------------------------------------------
    |
    | "属性.ルール"の規約でキーを指定することでカスタムバリデーション
    | メッセージを定義できます。指定した属性ルールに対する特定の
    | カスタム言語行を手早く指定できます。
    |
    */

    'custom' => [
        '属性名' => [
            'ルール名' => 'カスタムメッセージ',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | カスタムバリデーション属性名
    |--------------------------------------------------------------------------
    |
    | 以下の言語行は、例えば"email"の代わりに「メールアドレス」のように、
    | 読み手にフレンドリーな表現でプレースホルダーを置き換えるために指定する
    | 言語行です。これはメッセージをよりきれいに表示するために役に立ちます。
    |
    */

    'attributes' => [
        'email' => 'メールアドレス', // 追加
        'password' => 'パスワード', // 追加
    ],

];
```

## 50. マイページ、自分のブログのみ一覧表示

`tests/Feature/Http/Controllers/Mypage/UserLoginControllerTest.php`を編集  

```php:UserLoginControllerTest.php
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
    }

    /**
     * @test
     */
    // 編集
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
    // ここまで
}
```

- `$ php artisan test --filter マイページ、ブログ一覧で自分のデータのみ表示される`を実行  

```:console
   FAIL  Tests\Feature\Http\Controllers\Mypage\PostManageControllerTest
  ⨯ マイページ、ブログ一覧で自分のデータのみ表示される

  ---

  • Tests\Feature\Http\Controllers\Mypage\PostManageControllerTest > マイページ、ブログ一覧で自分のデータのみ表示される
  Failed asserting that '<!DOCTYPE html>\n
  <html lang="ja">\n
  \n
  <head>\n
      <meta charset="UTF-8">\n
      <title>ブログ</title>\n
      <link rel="stylesheet" type="text/css" href="/css/style.css" />\n
  </head>\n
  \n
  <body>\n
      <nav>\n
          <li><a href="/">TOP（ブログ一覧）</a></li>\n
  \n
                      <li><a href="http://localhost/mypage/posts">マイブログ一覧</a></li>\n
              <li>\n
                  <form method="post" action="http://localhost/mypage/logout">\n
                      <input type="hidden" name="_token" value="O9jDTkZZHJEAaGSORuxNGdpHHTQDlxknoNbc36c5"><input type="submit" value="ログアウト">\n
                  </form>\n
              </li>\n
              </nav>\n
          <h1>マイブログ一覧</h1>\n
  \n
      <a href="/mypage/posts/create">ブログ新規登録</a>\n
  \n
      <hr>\n
  </body>\n
  \n
  </html>\n
  ' contains "いったら、少し汽車のずうっとついたりの。".

  at tests/Feature/Http/Controllers/Mypage/PostManageControllerTest.php:38
     34▕ 
     35▕         $this->get('mypage/posts')
     36▕             ->assertOk()
     37▕             ->assertDontSee($other->title)
  ➜  38▕             ->assertSee($mypost->title);
     39▕     }
     40▕ }
     41▕ 


  Tests:  1 failed
  Time:   0.36s
```

`app/Http/Controllers/Mypage/PostManageController.php`を編集  

```php:PostManageController.php
<?php

namespace App\Http\Controllers\Mypage;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;

class PostManageController extends Controller
{
    public function index()
    {
        $posts = Post::get(); // 追加

        return view('mypage/posts/index', compact('posts')); // 編集
    }
}
```

`resources/views/mypage/posts/index.blade.php`を編集  

```php:index.blade.php
@extends('layouts.index')
@section('content')
    <h1>マイブログ一覧</h1>

    <a href="/mypage/posts/create">ブログ新規登録</a>

    <hr>

    // 追加
    <table>
        <tr>
            <th>ブログ名</th>
        </tr>

        @foreach ($posts as $post)
            <tr>
                {{ $post->title }}
            </tr>
        @endforeach
    </table>
    // ここまで
@endsection
```

- `$ php artisan test --filter マイページ、ブログ一覧で自分のデータのみ表示される`を実行  

```:console
 FAIL  Tests\Feature\Http\Controllers\Mypage\PostManageControllerTest
  ⨯ マイページ、ブログ一覧で自分のデータのみ表示される

  ---

  • Tests\Feature\Http\Controllers\Mypage\PostManageControllerTest > マイページ、ブログ一覧で自分のデータのみ表示される
  Failed asserting that '<!DOCTYPE html>\n
  <html lang="ja">\n
  \n
  <head>\n
      <meta charset="UTF-8">\n
      <title>ブログ</title>\n
      <link rel="stylesheet" type="text/css" href="/css/style.css" />\n
  </head>\n
  \n
  <body>\n
      <nav>\n
          <li><a href="/">TOP（ブログ一覧）</a></li>\n
  \n
                      <li><a href="http://localhost/mypage/posts">マイブログ一覧</a></li>\n
              <li>\n
                  <form method="post" action="http://localhost/mypage/logout">\n
                      <input type="hidden" name="_token" value="jyOqJSaxEA2zeeayjV4hQIhO8iN2eXODFC8p2cpo"><input type="submit" value="ログアウト">\n
                  </form>\n
              </li>\n
              </nav>\n
          <h1>マイブログ一覧</h1>\n
  \n
      <a href="/mypage/posts/create">ブログ新規登録</a>\n
  \n
      <hr>\n
  \n
      <table>\n
          <tr>\n
              <th>ブログ名</th>\n
          </tr>\n
  \n
                      <tr>\n
                  ぶぐらい戸口とのためになって光りながら。\n
              </tr>\n
                      <tr>\n
                  むこうとしまい、なんだい」ジョバンニさ。\n
              </tr>\n
              </table>\n
  </body>\n
  \n
  </html>\n
  ' does not contain "ぶぐらい戸口とのためになって光りながら。".

  at tests/Feature/Http/Controllers/Mypage/PostManageControllerTest.php:37
     33▕         $mypost = Post::factory()->create(['user_id' => $user->id]); // ログインしているユーザーのブログ投稿が作成できる
     34▕ 
     35▕         $this->get('mypage/posts')
     36▕             ->assertOk()
  ➜  37▕             ->assertDontSee($other->title)
     38▕             ->assertSee($mypost->title);
     39▕     }
     40▕ }
     41▕ 


  Tests:  1 failed
  Time:   0.35s
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
        $posts = Post::where('user_id', Auth::id())->get(); // 編集

        return view('mypage/posts/index', compact('posts'));
    }
}
```

- `$ php artisan test --filter マイページ、ブログ一覧で自分のデータのみ表示される`を編集  

```:terminal
   PASS  Tests\Feature\Http\Controllers\Mypage\PostManageControllerTest
  ✓ マイページ、ブログ一覧で自分のデータのみ表示される

  Tests:  1 passed
  Time:   0.44s
```

※ リファクタリング

`app/Models/User.php`を編集  

```php:User.php
<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // 追加
    public function posts()
    {
        return $this->hasMany(Post::class);
    }
    // ここまで
}
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
        $posts = auth()->user()->posts; // 編集

        return view('mypage/posts/index', compact('posts'));
    }
}
```

- `$ php artisan test --filter マイページ、ブログ一覧で自分のデータのみ表示される`を編集  

```:terminal
   PASS  Tests\Feature\Http\Controllers\Mypage\PostManageControllerTest
  ✓ マイページ、ブログ一覧で自分のデータのみ表示される

  Tests:  1 passed
  Time:   0.44s
```
