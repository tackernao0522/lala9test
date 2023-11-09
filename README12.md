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

## 42. ログイン処理 (入力チェック)

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

    // 追加
    /**
     * @test
     */
    function ログイン時の入力チェック()
    {
        $url = 'mypage/login';

        $this->from($url)->post($url, [])
            ->assertRedirect($url);

        // app()->setlocale('testing');

        // $this->post($url, ['email' => ''])->assertInvalid(['email' => 'required']);
        // $this->post($url, ['email' => 'aa@bb@cc'])->assertInvalid(['email' => 'email']);
        // $this->post($url, ['email' => 'aa@ああ.いい'])->assertInvalid(['email' => 'email']);
        // $this->post($url, ['password' => ''])->assertInvalid(['password' => 'required']);
    }
    // ここまで
}
```

- `$ php artisan test --filter ログイン時の入力チェック`を実行  

```:terminal
   FAIL  Tests\Feature\Http\Controllers\Mypage\UserLoginControllerTest
  ⨯ ログイン時の入力チェック

  ---

  • Tests\Feature\Http\Controllers\Mypage\UserLoginControllerTest > ログイン時の入力チェック
  Expected response status code [201, 301, 302, 303, 307, 308] but received 405.
  Failed asserting that false is true.

  at tests/Feature/Http/Controllers/Mypage/UserLoginControllerTest.php:28
     24▕     {
     25▕         $url = 'mypage/login';
     26▕ 
     27▕         $this->from($url)->post($url, [])
  ➜  28▕             ->assertRedirect($url);
     29▕ 
     30▕         // app()->setlocale('testing');
     31▕ 
     32▕         // $this->post($url, ['email' => ''])->assertInvalid(['email' => 'required']);


  Tests:  1 failed
  Time:   0.79s
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

Route::get('mypage/login', [UserLoginController::class, 'index']);
Route::post('mypage/login', [UserLoginController::class, 'login']); // 追加
```

- `$ php artisan test --filter ログイン時の入力チェック`を実行  

```:terminal

   FAIL  Tests\Feature\Http\Controllers\Mypage\UserLoginControllerTest
  ⨯ ログイン時の入力チェック

  ---

  • Tests\Feature\Http\Controllers\Mypage\UserLoginControllerTest > ログイン時の入力チェック
  Expected response status code [201, 301, 302, 303, 307, 308] but received 500.
  Failed asserting that false is true.
  
  The following exception occurred during the last request:
  
  BadMethodCallException: Method App\Http\Controllers\Mypage\UserLoginController::login does not exist. in /Applications/MAMP/htdocs/lara9test/vendor/laravel/framework/src/Illuminate/Routing/Controller.php:68
  Stack trace:
 
  <!-- 〜省略〜 -->
  ----------------------------------------------------------------------------------
  
  Method App\Http\Controllers\Mypage\UserLoginController::login does not exist.

  at tests/Feature/Http/Controllers/Mypage/UserLoginControllerTest.php:28
     24▕     {
     25▕         $url = 'mypage/login';
     26▕ 
     27▕         $this->from($url)->post($url, [])
  ➜  28▕             ->assertRedirect($url);
     29▕ 
     30▕         // app()->setlocale('testing');
     31▕ 
     32▕         // $this->post($url, ['email' => ''])->assertInvalid(['email' => 'required']);


  Tests:  1 failed
  Time:   0.41s
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
        return view('mypage.login');
    }

    // 追加
    public function login(Request $requst)
    {
        $requst->validate([]);
    }
    // ここまで
}
```

- `$ php artisan test --filter ログイン時の入力チェック`を実行  

```:terminal
  FAIL  Tests\Feature\Http\Controllers\Mypage\UserLoginControllerTest
  ⨯ ログイン時の入力チェック

  ---

  • Tests\Feature\Http\Controllers\Mypage\UserLoginControllerTest > ログイン時の入力チェック
  Expected response status code [201, 301, 302, 303, 307, 308] but received 200.
  Failed asserting that false is true.

  at tests/Feature/Http/Controllers/Mypage/UserLoginControllerTest.php:28
     24▕     {
     25▕         $url = 'mypage/login';
     26▕ 
     27▕         $this->from($url)->post($url, [])
  ➜  28▕             ->assertRedirect($url);
     29▕ 
     30▕         // app()->setlocale('testing');
     31▕ 
     32▕         // $this->post($url, ['email' => ''])->assertInvalid(['email' => 'required']);


  Tests:  1 failed
  Time:   0.26s
```

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

    /**
     * @test
     */
    function ログイン時の入力チェック()
    {
        $url = 'mypage/login';

        // コメントアウトする
        // $this->from($url)->post($url, [])
        //     ->assertRedirect($url);

        app()->setlocale('testing'); // コメントアウト解除

        $this->post($url, ['email' => ''])->assertInvalid(['email' => 'required']); // コメントアウト解除
        // $this->post($url, ['email' => 'aa@bb@cc'])->assertInvalid(['email' => 'email']);
        // $this->post($url, ['email' => 'aa@ああ.いい'])->assertInvalid(['email' => 'email']);
        // $this->post($url, ['password' => ''])->assertInvalid(['password' => 'required']);
    }
}
```

- `$ php artisan test --filter ログイン時の入力チェック`を実行  

```:terminal
 FAIL  Tests\Feature\Http\Controllers\Mypage\UserLoginControllerTest
  ⨯ ログイン時の入力チェック

  ---

  • Tests\Feature\Http\Controllers\Mypage\UserLoginControllerTest > ログイン時の入力チェック
  Session is missing expected key [errors].
  Failed asserting that false is true.

  at tests/Feature/Http/Controllers/Mypage/UserLoginControllerTest.php:32
     28▕         //     ->assertRedirect($url);
     29▕ 
     30▕         app()->setlocale('testing');
     31▕ 
  ➜  32▕         $this->post($url, ['email' => ''])->assertInvalid(['email' => 'required']);
     33▕         // $this->post($url, ['email' => 'aa@bb@cc'])->assertInvalid(['email' => 'email']);
     34▕         // $this->post($url, ['email' => 'aa@ああ.いい'])->assertInvalid(['email' => 'email']);
     35▕         // $this->post($url, ['password' => ''])->assertInvalid(['password' => 'required']);
     36▕     }


  Tests:  1 failed
  Time:   0.35s
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
        return view('mypage.login');
    }

    public function login(Request $requst)
    {
        $requst->validate([
            'email' => ['required'], // 編集
        ]);
    }
}
```

- `$ php artisan test --filter ログイン時の入力チェック`を実行  

```:terminal
  PASS  Tests\Feature\Http\Controllers\Mypage\UserLoginControllerTest
  ✓ ログイン時の入力チェック

  Tests:  1 passed
  Time:   0.26s
```

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

    /**
     * @test
     */
    function ログイン時の入力チェック()
    {
        $url = 'mypage/login';

        // コメントアウトする
        // $this->from($url)->post($url, [])
        //     ->assertRedirect($url);

        app()->setlocale('testing'); // コメントアウト解除

        $this->post($url, ['email' => ''])->assertInvalid(['email' => 'required']); // コメントアウト解除
        $this->post($url, ['email' => 'aa@bb@cc'])->assertInvalid(['email' => 'email']); // コメントアウト解除
        // $this->post($url, ['email' => 'aa@ああ.いい'])->assertInvalid(['email' => 'email']);
        // $this->post($url, ['password' => ''])->assertInvalid(['password' => 'required']);
    }
}
```

- `$ php artisan test --filter ログイン時の入力チェック`を実行  

```:terminal
   FAIL  Tests\Feature\Http\Controllers\Mypage\UserLoginControllerTest
  ⨯ ログイン時の入力チェック

  ---

  • Tests\Feature\Http\Controllers\Mypage\UserLoginControllerTest > ログイン時の入力チェック
  Session is missing expected key [errors].
  Failed asserting that false is true.

  at tests/Feature/Http/Controllers/Mypage/UserLoginControllerTest.php:33
     29▕ 
     30▕         app()->setlocale('testing');
     31▕ 
     32▕         $this->post($url, ['email' => ''])->assertInvalid(['email' => 'required']);
  ➜  33▕         $this->post($url, ['email' => 'aa@bb@cc'])->assertInvalid(['email' => 'email']);
     34▕         // $this->post($url, ['email' => 'aa@ああ.いい'])->assertInvalid(['email' => 'email']);
     35▕         // $this->post($url, ['password' => ''])->assertInvalid(['password' => 'required']);
     36▕     }
     37▕ }


  Tests:  1 failed
  Time:   0.27s
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
        return view('mypage.login');
    }

    public function login(Request $requst)
    {
        $requst->validate([
            'email' => ['required', 'email'], // 編集
        ]);
    }
}
```

- `$ php artisan test --filter ログイン時の入力チェック`を実行  

```:terminal
 PASS  Tests\Feature\Http\Controllers\Mypage\UserLoginControllerTest
  ✓ ログイン時の入力チェック

  Tests:  1 passed
  Time:   0.27s
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
        return view('mypage.login');
    }

    public function login(Request $requst)
    {
        $requst->validate([
            'email' => ['required'], // 編集
        ]);
    }
}
```

- `$ php artisan test --filter ログイン時の入力チェック`を実行  

```:terminal
  PASS  Tests\Feature\Http\Controllers\Mypage\UserLoginControllerTest
  ✓ ログイン時の入力チェック

  Tests:  1 passed
  Time:   0.26s
```

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

    /**
     * @test
     */
    function ログイン時の入力チェック()
    {
        $url = 'mypage/login';

        // コメントアウトする
        // $this->from($url)->post($url, [])
        //     ->assertRedirect($url);

        app()->setlocale('testing'); // コメントアウト解除

        $this->post($url, ['email' => ''])->assertInvalid(['email' => 'required']); // コメントアウト解除
        $this->post($url, ['email' => 'aa@bb@cc'])->assertInvalid(['email' => 'email']); // コメントアウト解除
        $this->post($url, ['email' => 'aa@ああ.いい'])->assertInvalid(['email' => 'email']); // コメントアウト解除
        // $this->post($url, ['password' => ''])->assertInvalid(['password' => 'required']);
    }
}
```

- `$ php artisan test --filter ログイン時の入力チェック`を実行  

```:terminal
   FAIL  Tests\Feature\Http\Controllers\Mypage\UserLoginControllerTest
  ⨯ ログイン時の入力チェック

  ---

  • Tests\Feature\Http\Controllers\Mypage\UserLoginControllerTest > ログイン時の入力チェック
  Session is missing expected key [errors].
  Failed asserting that false is true.

  at tests/Feature/Http/Controllers/Mypage/UserLoginControllerTest.php:34
     30▕         app()->setlocale('testing');
     31▕ 
     32▕         $this->post($url, ['email' => ''])->assertInvalid(['email' => 'required']);
     33▕         $this->post($url, ['email' => 'aa@bb@cc'])->assertInvalid(['email' => 'email']);
  ➜  34▕         $this->post($url, ['email' => 'aa@ああ.いい'])->assertInvalid(['email' => 'email']);
     35▕         // $this->post($url, ['password' => ''])->assertInvalid(['password' => 'required']);
     36▕     }
     37▕ }
     38▕ 


  Tests:  1 failed
  Time:   0.28s
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
        return view('mypage.login');
    }

    public function login(Request $requst)
    {
        $requst->validate([
            'email' => ['required', 'email:filter'], // 編集
        ]);
    }
}
```

- `$ php artisan test --filter ログイン時の入力チェック`を実行  

```:terminal
   PASS  Tests\Feature\Http\Controllers\Mypage\UserLoginControllerTest
  ✓ ログイン時の入力チェック

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
        return view('mypage.login');
    }

    public function login(Request $requst)
    {
        $requst->validate([
            'email' => ['required'], // 編集
        ]);
    }
}
```

- `$ php artisan test --filter ログイン時の入力チェック`を実行  

```:terminal
  PASS  Tests\Feature\Http\Controllers\Mypage\UserLoginControllerTest
  ✓ ログイン時の入力チェック

  Tests:  1 passed
  Time:   0.26s
```

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

    /**
     * @test
     */
    function ログイン時の入力チェック()
    {
        $url = 'mypage/login';

        $this->from($url)->post($url, [])
            ->assertRedirect($url); // コメントアウト解除

        app()->setlocale('testing'); // コメントアウト解除

        $this->post($url, ['email' => ''])->assertInvalid(['email' => 'required']); // コメントアウト解除
        $this->post($url, ['email' => 'aa@bb@cc'])->assertInvalid(['email' => 'email']); // コメントアウト解除
        $this->post($url, ['email' => 'aa@ああ.いい'])->assertInvalid(['email' => 'email']); // コメントアウト解除
        $this->post($url, ['password' => ''])->assertInvalid(['password' => 'required']); // コメントアウト解除
    }
}
```

- `$ php artisan test --filter ログイン時の入力チェック`を実行  

```:terminal
   FAIL  Tests\Feature\Http\Controllers\Mypage\UserLoginControllerTest
  ⨯ ログイン時の入力チェック

  ---

  • Tests\Feature\Http\Controllers\Mypage\UserLoginControllerTest > ログイン時の入力チェック
  Failed to find a validation error in session for key: 'required'
  
  Response has the following validation errors in the session:
  
  {
      "email": [
          "required"
      ]
  }
  
  Failed asserting that an array has the key 'password'.

  at tests/Feature/Http/Controllers/Mypage/UserLoginControllerTest.php:35
     31▕ 
     32▕         $this->post($url, ['email' => ''])->assertInvalid(['email' => 'required']);
     33▕         $this->post($url, ['email' => 'aa@bb@cc'])->assertInvalid(['email' => 'email']);
     34▕         $this->post($url, ['email' => 'aa@ああ.いい'])->assertInvalid(['email' => 'email']);
  ➜  35▕         $this->post($url, ['password' => ''])->assertInvalid(['password' => 'required']);
     36▕     }
     37▕ }
     38▕ 


  Tests:  1 failed
  Time:   0.27s
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
        return view('mypage.login');
    }

    public function login(Request $requst)
    {
        $requst->validate([
            'email' => ['required', 'email:filter'],
            'password' => ['required'], // 追加
        ]);
    }
}
```

- `$ php artisan test --filter ログイン時の入力チェック`を実行  

```:terminal
  PASS  Tests\Feature\Http\Controllers\Mypage\UserLoginControllerTest
  ✓ ログイン時の入力チェック

  Tests:  1 passed
  Time:   0.28s
```
