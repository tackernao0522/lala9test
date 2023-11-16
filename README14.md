# Section2

## 47. 認証していれば、マイページを開ける

- `$ php artisan make:controller Mypage/PostManageController`を実行  

- `$ php artisan make:test Http/Controllers/Mypage/PostMenageControllerTest`を実行  

`test/Feature/Http/Controllers/Mypage/PostManageControllerTest.php`を編集  

```php:PostManageControllerTest.php
<?php

namespace Tests\Feature\Http\Controllers\Mypage;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PostManageControllerTest extends TestCase
{
    // 編集
    /**
     * @test
     */
    function 認証している場合に限りマイページを開ける()
    {
        // 認証していないばあい
        $this->get('mypage/posts')
            ->assertRedirect('mypage/login');
    }
    // ここまで
}
```

- `$ php artisan test --filter 認証している場合に限りマイページを開ける`を実行  

```:terminal
   FAIL  Tests\Feature\Http\Controllers\Mypage\PostManageControllerTest
  ⨯ 認証している場合に限りマイページを開ける

  ---

  • Tests\Feature\Http\Controllers\Mypage\PostManageControllerTest > 認証している場合に限りマイページを開ける
  Expected response status code [201, 301, 302, 303, 307, 308] but received 404.
  Failed asserting that false is true.

  at tests/Feature/Http/Controllers/Mypage/PostManageControllerTest.php:17
     13▕      */
     14▕     function 認証している場合に限りマイページを開ける()
     15▕     {
     16▕         $this->get('mypage/posts')
  ➜  17▕             ->assertRedirect('mypage/login');
     18▕     }
     19▕ }
     20▕ 


  Tests:  1 failed
  Time:   0.30s
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

Route::get('mypage/login', [UserLoginController::class, 'index']);
Route::post('mypage/login', [UserLoginController::class, 'login']);


Route::get('mypage/posts', [PostManageController::class, 'index']); // 追加
```

- `$ php artisan test --filter 認証している場合に限りマイページを開ける`を実行  

```:terminal
   FAIL  Tests\Feature\Http\Controllers\Mypage\PostManageControllerTest
  ⨯ 認証している場合に限りマイページを開ける

  ---

  • Tests\Feature\Http\Controllers\Mypage\PostManageControllerTest > 認証している場合に限りマイページを開ける
  Expected response status code [201, 301, 302, 303, 307, 308] but received 500.
  Failed asserting that false is true.
  
  The following exception occurred during the last request:
  
    <!-- 〜省略〜 -->
  
  ----------------------------------------------------------------------------------
  
  Method App\Http\Controllers\Mypage\PostManageController::index does not exist.

  at tests/Feature/Http/Controllers/Mypage/PostManageControllerTest.php:17
     13▕      */
     14▕     function 認証している場合に限りマイページを開ける()
     15▕     {
     16▕         $this->get('mypage/posts')
  ➜  17▕             ->assertRedirect('mypage/login');
     18▕     }
     19▕ }
     20▕ 


  Tests:  1 failed
  Time:   0.43s
```

`app/Http/Controllers/Mypage/PostManageController.php`を編集  

```php:PostManageController.php
<?php

namespace App\Http\Controllers\Mypage;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PostManageController extends Controller
{
    // 追加
    public function index()
    {
    }
    // ここまで
}
```

- `$ php artisan test --filter 認証している場合に限りマイページを開ける`を実行  

```:terminal
   FAIL  Tests\Feature\Http\Controllers\Mypage\PostManageControllerTest
  ⨯ 認証している場合に限りマイページを開ける

  ---

  • Tests\Feature\Http\Controllers\Mypage\PostManageControllerTest > 認証している場合に限りマイページを開ける
  Expected response status code [201, 301, 302, 303, 307, 308] but received 200.
  Failed asserting that false is true.

  at tests/Feature/Http/Controllers/Mypage/PostManageControllerTest.php:17
     13▕      */
     14▕     function 認証している場合に限りマイページを開ける()
     15▕     {
     16▕         $this->get('mypage/posts')
  ➜  17▕             ->assertRedirect('mypage/login');
     18▕     }
     19▕ }
     20▕ 


  Tests:  1 failed
  Time:   0.27s
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

Route::get('mypage/login', [UserLoginController::class, 'index']);
Route::post('mypage/login', [UserLoginController::class, 'login']);

// 編集
Route::middleware('auth')->group(function () {
    Route::get('mypage/posts', [PostManageController::class, 'index']);
});
// ここまで
```

- `$ php artisan test --filter 認証している場合に限りマイページを開ける`を実行  

```:terminal
  FAIL  Tests\Feature\Http\Controllers\Mypage\PostManageControllerTest
  ⨯ 認証している場合に限りマイページを開ける

  ---

  • Tests\Feature\Http\Controllers\Mypage\PostManageControllerTest > 認証している場合に限りマイページを開ける
  Expected response status code [201, 301, 302, 303, 307, 308] but received 500.
  Failed asserting that false is true.
  
  The following exception occurred during the last request:
  
    <!-- 〜省略〜 -->
  
  ----------------------------------------------------------------------------------
  
  Route [login] not defined.

  at tests/Feature/Http/Controllers/Mypage/PostManageControllerTest.php:17
     13▕      */
     14▕     function 認証している場合に限りマイページを開ける()
     15▕     {
     16▕         $this->get('mypage/posts')
  ➜  17▕             ->assertRedirect('mypage/login');
     18▕     }
     19▕ }
     20▕ 


  Tests:  1 failed
  Time:   0.43s
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

Route::get('mypage/login', [UserLoginController::class, 'index'])->name('login'); // 編集
Route::post('mypage/login', [UserLoginController::class, 'login']);

Route::middleware('auth')->group(function () {
    Route::get('mypage/posts', [PostManageController::class, 'index']);
});
```

- `$ php artisan test --filter 認証している場合に限りマイページを開ける`を実行  

```:terminal
   PASS  Tests\Feature\Http\Controllers\Mypage\PostManageControllerTest
  ✓ 認証している場合に限りマイページを開ける

  Tests:  1 passed
  Time:   0.27s
```

`tests/Feature/Http/Controllers/Mypage/PostManageControllerTest.php`を編集  

```php:PostManageControllerTest.php
<?php

namespace Tests\Feature\Http\Controllers\Mypage;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PostManageControllerTest extends TestCase
{
    /**
     * @test
     */
    function 認証している場合に限りマイページを開ける()
    {
        // 認証していない場合
        $this->get('mypage/posts')
            ->assertRedirect('mypage/login');

        // 追記
        // 認証済みの場合
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('mypage/posts')
            ->assertOk();
        // ここまで
    }
}
```

- `$ php artisan test --filter 認証している場合に限りマイページを開ける`を実行  

```:terminal
   PASS  Tests\Feature\Http\Controllers\Mypage\PostManageControllerTest
  ✓ 認証している場合に限りマイページを開ける

  Tests:  1 passed
  Time:   0.39s
```

`tests/TestCase.php`を編集  

```php:TestCase.php
<?php

namespace Tests;

use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable; // 追加
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use RefreshDatabase;

    // 追加
    public function login(Authenticatable $user = null)
    {
        $user ??= User::factory()->create();

        $this->actingAs($user);

        return $user;
    }
    // ここまで
}
```

`tests/Features/Http/Controllers/Mypage/PostManageControllerTest.php`を編集  

```php:PostManagerControllerTest.php
<?php

namespace Tests\Feature\Http\Controllers\Mypage;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PostManageControllerTest extends TestCase
{
    /**
     * @test
     */
    function 認証している場合に限りマイページを開ける()
    {
        // 認証していない場合
        $this->get('mypage/posts')
            ->assertRedirect('mypage/login');

        // 認証済みの場合
        // $user = User::factory()->create();

        // $this->actingAs($user)
        //     ->get('mypage/posts')
        //     ->assertOk();

        // 編集
        $this->login();

        $this->get('mypage/posts')
            ->assertOk();
        // ここまで
    }
}
```

- `$ php artisan test --filter 認証している場合に限りマイページを開ける`を実行  

```:terminal
   PASS  Tests\Feature\Http\Controllers\Mypage\PostManageControllerTest
  ✓ 認証している場合に限りマイページを開ける

  Tests:  1 passed
  Time:   0.39s
```

`tests/Features/Http/Controllers/Mypage/PostManageControllerTest.php`を編集  

```php:PostManagerControllerTest.php
<?php

namespace Tests\Feature\Http\Controllers\Mypage;

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
        // 追加
        $loginUrl = 'mypage/login';

        // 認証していない場合
        $this->get('mypage/posts')
            ->assertRedirect($loginUrl);
        // ここまで
    }

    /**
     * @test
     */
    // 編集
    function マイページをTOP開ける()
    {
        // 認証済みの場合
        // $user = User::factory()->create();

        // $this->actingAs($user)
        //     ->get('mypage/posts')
        //     ->assertOk();

        $this->login();

        $this->get('mypage/posts')
            ->assertOk();
        // ここまで
    }
}
```

- `$ php artisan test --filter ゲストはブログを管理できない`を実行  

```:terminal
   PASS  Tests\Feature\Http\Controllers\Mypage\PostManageControllerTest
  ✓ ゲストはブログを管理できない

  Tests:  1 passed
  Time:   0.28s
```

`app/Http/Mypage/PostManageController.php`を編集  

```php:PostManageController.php
<?php

namespace App\Http\Controllers\Mypage;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PostManageController extends Controller
{
    public function index()
    {
        return view('mypage/posts/index');
    }
}
```

- `$ mkdir resources/views/mypage/posts && touch $_/index.blade.php`を実行  

`resources/views/mypage/posts/index.blade.php`を編集  

```php:index.blade.php
@extends('layoutes.index')
@section('content')
    <h1>マイブログ一覧</h1>

    <a href="/mypage/posts/create">ブログ新規登録</a>

    <hr>
@endsection
```

- `$ php artisan test --filter マイページをTOP開ける`を実行  

```:terminal
   PASS  Tests\Feature\Http\Controllers\Mypage\PostManageControllerTest
  ✓ マイページを t o p開ける

  Tests:  1 passed
  Time:   0.29s
```

- `$ vendor/bin/phpunit`を実行  

```:terminal
PHPUnit 9.6.13 by Sebastian Bergmann and contributors.

................"testing" // tests/Feature/Http/Controllers/SignupControllerTest.php:77
."ja" // tests/Feature/Http/Controllers/SignupControllerTest.php:99
.....                                            22 / 22 (100%)

Time: 00:02.021, Memory: 48.50 MB
```
