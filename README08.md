# Section2

## 33. ユーザー登録、 その1

- `$ php artisan make:controller SignupController`を実行  

- `$ php artisan make:test Http/Controllers/SignupControllerTest`を実行  

`tests/Feature/Http/Controllers/SignupControllerTest.php`を編集  

```php:SignupControllerTest.php
<?php

namespace Tests\Feature\Http\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SignupControllerTest extends TestCase
{
    // 編集
    /**
     * @test
     */
    function ユーザー登録画面が開ける()
    {
        $this->get('signup')
            ->assertOk();
    }
    // ここまで
}
```

- `$ php artisan test --filter ユーザー登録画面が開ける`を実行  

```:terminal
   FAIL  Tests\Feature\Http\Controllers\SignupControllerTest
  ⨯ ユーザー登録画面が開ける

  ---

  • Tests\Feature\Http\Controllers\SignupControllerTest > ユーザー登録画面が開ける
  Expected response status code [200] but received 404.
  Failed asserting that 200 is identical to 404.

    <!-- 省略 -->
  
  ----------------------------------------------------------------------------------
  
  Method App\Http\Controllers\SignupController::index does not exist.

  at tests/Feature/Http/Controllers/SignupControllerTest.php:17
     13▕      */
     14▕     function ユーザー登録画面が開ける()
     15▕     {
     16▕         $this->get('signup')
  ➜  17▕             ->assertOk();
     18▕     }
     19▕ }
     20▕ 


  Tests:  1 failed
  Time:   0.45s
```

`app/Http/Controllers/SignupController.php`を編集  

```php:SignupController.php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SignupController extends Controller
{
    public function index()
    {

    }
}
```

- `$ php artisan test --filter ユーザー登録画面が開ける`を実行  

```:terminal
   PASS  Tests\Feature\Http\Controllers\SignupControllerTest
  ✓ ユーザー登録画面が開ける

  Tests:  1 passed
  Time:   0.26s
```

`app/Http/Controllers/SignupController.php`を編集  

```php:SignupController.php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SignupController extends Controller
{
    public function index()
    {
        return view('signup'); // 追加
    }
}
```

- `$ touch resources/views/signup.blade.php`を実行  

`resources/views/signup.blade.php`を編集  

```php:signup.blade.php
@extends('layouts.index')

@section('content')
    <h1>ユーザー登録</h1>
    <form method="POST">
        @csrf

        @include('inc.error')

        名前 : <input type="text" name="name" value="{{ old('name') }}">
        <br>
        メルアド : <input type="text" name="email" value="{{ old('email') }}">
        <br>
        パスワード : <input type="password" name="password">
        <br>
        <br>
        <input type="submit" value="送信する">
    </form>
@endsection
```

- `$ mkdir resources/views/inc && touch $_/error.blade.php`を実行  

`resources/views/inc/error.blade.php`を編集  

```php:error.blade.php
@if ($errors->any())
    <ul class="error">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
@endif
```

`tests/Feature/Http/Controllers/SignupControllers/SignupControllerTest.php`を編集  

```php:SignupControllerTest.php
<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SignupControllerTest extends TestCase
{
    /**
     * @test
     */
    function ユーザー登録画面が開ける()
    {
        $this->get('signup')
            ->assertOk();
    }

    // 追加
    /**
     * @test
     */
    function ユーザー登録できる()
    {
        // データ検証
        // DBに保存
        // ログインされてからマイページにリダイレクト

        $validData = [
            'name' => '太郎',
            'email' => 'aaa@bbb.net',
            'password' => 'hogehoge',
        ];

        $this->post('signup', $validData)
            ->assertOk();

        unset($validData['password']);

        $this->assertDatabaseHas('users', $validData);
    }
    // ここまで
}
```

- `$ php artisan test --filter ユーザー登録できる`を実行  

```:terminal
 FAIL  Tests\Feature\Http\Controllers\SignupControllerTest
  ⨯ ユーザー登録できる

  ---

  • Tests\Feature\Http\Controllers\SignupControllerTest > ユーザー登録できる
  Expected response status code [200] but received 405.
  Failed asserting that 200 is identical to 405.

  at tests/Feature/Http/Controllers/SignupControllerTest.php:37
     33▕             'password' => 'hogehoge',
     34▕         ];
     35▕ 
     36▕         $this->post('signup', $validData)
  ➜  37▕             ->assertOk();
     38▕ 
     39▕         unset($validData['password']);
     40▕ 
     41▕         $this->assertDatabaseHas('users', $validData);


  Tests:  1 failed
  Time:   0.39s
```

`routes/web.php`を編集  

```php:web.php
<?php

use App\Http\Controllers\PostController;
use App\Http\Controllers\SignupController;
use Illuminate\Support\Facades\Route;

Route::get('', [PostController::class, 'index']);
Route::get('posts/{post}', [PostController::class, 'show'])
->name('posts.show')
->whereNumber('post'); // 'post'は数値のみに限定という意味

Route::get('signup', [SignupController::class, 'index']);
Route::post('signup', [SignupController::class, 'store']); // 追加
```

- `$ php artisan test --filter ユーザー登録できる`を実行  

```:terminal
 FAIL  Tests\Feature\Http\Controllers\SignupControllerTest
  ⨯ ユーザー登録できる

  ---

    <!-- 〜省略〜 -->
  
  ----------------------------------------------------------------------------------
  
  Method App\Http\Controllers\SignupController::store does not exist.

  at tests/Feature/Http/Controllers/SignupControllerTest.php:37
     33▕             'password' => 'hogehoge',
     34▕         ];
     35▕ 
     36▕         $this->post('signup', $validData)
  ➜  37▕             ->assertOk();
     38▕ 
     39▕         unset($validData['password']);
     40▕ 
     41▕         $this->assertDatabaseHas('users', $validData);


  Tests:  1 failed
  Time:   0.43s
```

`app/Http/Controllers/SignupController.php`を編集  

```php:SignupController.php
<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class SignupController extends Controller
{
    public function index()
    {
        return view('signup');
    }

    // 追加
    public function store(Request $request)
    {
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
        ]);
    }
    // ここまで
}
```

- `$ php artisan test --filter ユーザー登録できる`を実行  

```:terminal
   PASS  Tests\Feature\Http\Controllers\SignupControllerTest
  ✓ ユーザー登録できる

  Tests:  1 passed
  Time:   0.32s
```
