# Section2

## 41. ログイン画面を開ける

- `$ php artisan make:controller Mypage/UserLoginController`を実行  

- `$ php artisan make:test Http/Controllers/Mypage/UserLoginTestController`を実行  

`tests/Feature/Http/Controllers/Mypage/UserLoginControllerTest.php`を編集  

```php:UserLoginControllerTest.php
<?php

namespace Tests\Feature\Http\Controllers\Mypage;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
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
}
```

- `$ php artisan test --filter ログイン画面を開ける`を実行  

```:terminal
  FAIL  Tests\Feature\Http\Controllers\Mypage\UserLoginControllerTest
  ⨯ ログイン画面を開ける

  ---

  • Tests\Feature\Http\Controllers\Mypage\UserLoginControllerTest > ログイン画面を開ける
  Expected response status code [200] but received 404.
  Failed asserting that 200 is identical to 404.

  at tests/Feature/Http/Controllers/Mypage/UserLoginControllerTest.php:17
     13▕      */
     14▕     function ログイン画面を開ける()
     15▕     {
     16▕         $this->get('mypage/login')
  ➜  17▕             ->assertOk();
     18▕     }
     19▕ }
     20▕ 


  Tests:  1 failed
  Time:   0.27s
```

`routes/web.php`を編集  

```php:web.php
<?php

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

Route::get('mypage/login', [UserLoginController::class, 'index']); // 追加
```

- `$ php artisan test --filter ログイン画面を開ける`を実行  

```:terminal

   FAIL  Tests\Feature\Http\Controllers\Mypage\UserLoginControllerTest
  ⨯ ログイン画面を開ける

  ---

  • Tests\Feature\Http\Controllers\Mypage\UserLoginControllerTest > ログイン画面を開ける
  Expected response status code [200] but received 500.
  Failed asserting that 200 is identical to 500.
  
  The following exception occurred during the last request:
  
  BadMethodCallException: Method App\Http\Controllers\Mypage\UserLoginController::index does not exist. in /Applications/MAMP/htdocs/lara9test/vendor/laravel/framework/src/Illuminate/Routing/Controller.php:68
  Stack trace:

  <!-- 〜省略〜 -->
  ----------------------------------------------------------------------------------
  
  Method App\Http\Controllers\Mypage\UserLoginController::index does not exist.

  at tests/Feature/Http/Controllers/Mypage/UserLoginControllerTest.php:17
     13▕      */
     14▕     function ログイン画面を開ける()
     15▕     {
     16▕         $this->get('mypage/login')
  ➜  17▕             ->assertOk();
     18▕     }
     19▕ }
     20▕ 


  Tests:  1 failed
  Time:   0.45s
```

`app/Http/Controllers/Mypage/UserLoginController.php`を編集  

```php:UserLoginController.php
<?php

namespace App\Http\Controllers\Mypage;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserLoginController extends Controller
{
    // 追加
    public function index()
    {
        
    }
}
```

- `$ php artisan test --filter ログイン画面を開ける`を実行  

```:terminal
 PASS  Tests\Feature\Http\Controllers\Mypage\UserLoginControllerTest
  ✓ ログイン画面を開ける

  Tests:  1 passed
  Time:   0.26s
```

`app/Http/Controllers/Mypage/UserLoginController.php`を編集  

```php:UserLoginController.php
<?php

namespace App\Http\Controllers\Mypage;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserLoginController extends Controller
{
    public function index()
    {
        return view('mypage.login'); // 追加
    }
}
```

- `$ mkdir resources/views/mypage && touch $_/login.blade.php`を実行  

`resources/views/mypage/login.blade.php`を編集  

```php:login.blade.php
@extends('layouts.index')

@section('content')
    <h1>ログイン画面</h1>

    <form method="POST">
        @csrf

        @include('inc.error')
        @include('inc.status')

        メールアドレス:<input type="text" name="email" value="{{ old('email') }}">
        <br>
        パスワード:<input type="password" name="password">
        <br><br>
        <input type="submit" value="送信する">
    </form>

    <p style="margin-top: 30px;">
        <a href="/signup">新規ユーザー登録</a>
    </p>
@endsection
```

- `$ touch resources/views/inc/status.blade.php`を実行  

`resources/views/inc/status.blade.php`を編集  

```php:status.blade.php
@if (session('status'))
    <p class="status">{{ session('status') }}</p>
@endif
```

- `$ php artisan test --filter ログイン画面を開ける`を実行  

```:terminal
   PASS  Tests\Feature\Http\Controllers\Mypage\UserLoginControllerTest
  ✓ ログイン画面を開ける

  Tests:  1 passed
  Time:   0.27s
```
