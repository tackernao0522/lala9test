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

※ `SignupConroller.phpの下記の部分の例のように変えるとエラーになる`  

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

    public function store(Request $request)
    {
        User::create([
            'name' => '与太郎',
            'email' => $request->email,
            'password' => $request->password,
        ]);
    }
}
```

```:terminal
   FAIL  Tests\Feature\Http\Controllers\SignupControllerTest
  ⨯ ユーザー登録できる

  ---

  • Tests\Feature\Http\Controllers\SignupControllerTest > ユーザー登録できる
  Failed asserting that a row in the table [users] matches the attributes {
      "name": "太郎",
      "email": "aaa@bbb.net"
  }.
  
  Found: [
      {
          "name": "与太郎",
          "email": "aaa@bbb.net"
      }
  ].

  at tests/Feature/Http/Controllers/SignupControllerTest.php:41
     37▕             ->assertOk();
     38▕ 
     39▕         unset($validData['password']);
     40▕ 
  ➜  41▕         $this->assertDatabaseHas('users', $validData);
     42▕     }
     43▕ }
     44▕ 


  Tests:  1 failed
  Time:   0.29s
```

## 34. ユーザー登録、 その2、パスワード保存  

`tests/Feature/Http/Controllers/SignupControllerTest.php`を編集  

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

        $this->assertDatabaseHas('users', $validData); // なるべくこっちを使用した方が良い

        $user = User::firstWhere($validData); // 追加
        // $this->assertNotNull($user); // 追加 $this->assertDatabaseHas('users', $validData);を同じ意味になる
    }
}
```

- `$ php artisan test --filter ユーザー登録できる`を実行  

```:terminal
   PASS  Tests\Feature\Http\Controllers\SignupControllerTest
  ✓ ユーザー登録できる

  Tests:  1 passed
  Time:   0.29s
```

`tests/Feature/Http/Controllers/SignupControllerTest.php`を編集  

```php:SignupControllerTest.php
<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
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

        $user = User::firstWhere($validData);
        // $this->assertNotNull($user);

        $this->assertTrue(Hash::check('hogehoge', $user->password)); // 追加
    }
}
```

- `$ php artisan test --filter ユーザー登録できる`を実行  

```:terminal
 FAIL  Tests\Feature\Http\Controllers\SignupControllerTest
  ⨯ ユーザー登録できる

  ---

  • Tests\Feature\Http\Controllers\SignupControllerTest > ユーザー登録できる
  Failed asserting that false is true.

  at tests/Feature/Http/Controllers/SignupControllerTest.php:47
     43▕ 
     44▕         $user = User::firstWhere($validData);
     45▕         // $this->assertNotNull($user);
     46▕ 
  ➜  47▕         $this->assertTrue(Hash::check('hogehoge', $user->password));
     48▕     }
     49▕ }
     50▕ 


  Tests:  1 failed
  Time:   0.41s
```

`app/Http/Controllers/SignupController.php`を編集  

```php:SignupController.php
<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SignupController extends Controller
{
    public function index()
    {
        return view('signup');
    }

    public function store(Request $request)
    {
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password), // 編集
        ]);
    }
}
```

- `$ php artisan test --filter ユーザー登録できる`を実行  

```:terminal

   PASS  Tests\Feature\Http\Controllers\SignupControllerTest
  ✓ ユーザー登録できる

  Tests:  1 passed
  Time:   0.29s
```

## 35. ユーザー登録、 その3、 妥当なデータ

`tests/Feature/Http/Controllers/SignupControllerTest.php`を編集  

```php:SignupControllerTest.php
<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
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

        $validData = User::factory()->make(); // Userインスタンスが帰ってくる 追加
        dd($validData); // 追加

        $this->post('signup', $validData)
            ->assertOk();

        unset($validData['password']);

        $this->assertDatabaseHas('users', $validData);

        $user = User::firstWhere($validData);
        // $this->assertNotNull($user);

        $this->assertTrue(Hash::check('hogehoge', $user->password));
    }
}
```

- `$ php artisan test --filter ユーザー登録できる`を実行  

```:terminal
App\Models\User^ {#1379 // tests/Feature/Http/Controllers/SignupControllerTest.php:38
  #connection: null
  #table: null
  #primaryKey: "id"
  #keyType: "int"
  +incrementing: true
  #with: []
  #withCount: []
  +preventsLazyLoading: false
  #perPage: 15
  +exists: false
  +wasRecentlyCreated: false
  #escapeWhenCastingToString: false
  #attributes: array:5 [
    "name" => "近藤 七夏"
    "email" => "yosuke67@example.com"
    "email_verified_at" => "2023-10-28 12:22:58"
    "password" => "$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi"
    "remember_token" => "ZM6AtzV5lB"
  ]
  #original: []
  #changes: []
  #casts: array:1 [
    "email_verified_at" => "datetime"
  ]
  #classCastCache: []
  #attributeCastCache: []
  #dates: []
  #dateFormat: null
  #appends: []
  #dispatchesEvents: []
  #observables: []
  #relations: []
  #touches: []
  +timestamps: true
  #hidden: array:2 [
    0 => "password"
    1 => "remember_token"
  ]
  #visible: []
  #fillable: array:3 [
    0 => "name"
    1 => "email"
    2 => "password"
  ]
  #guarded: array:1 [
    0 => "*"
  ]
  #rememberTokenName: "remember_token"
  #accessToken: null
}
```

`tests/Feature/Http/Controllers/SignupControllerTest.php`を編集  

```php:SignupControllerTest.php
<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
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

        $validData = User::factory()->make()->toArray(); // 編集 UserFactory.phpでhiddenが設定されている部分が配列化する際に消える
        dd($validData);

        $this->post('signup', $validData)
            ->assertOk();

        unset($validData['password']);

        $this->assertDatabaseHas('users', $validData);

        $user = User::firstWhere($validData);
        // $this->assertNotNull($user);

        $this->assertTrue(Hash::check('hogehoge', $user->password));
    }
}
```

- `php artisan test --filter ユーザー登録できる`を実行  

```:terminal
array:3 [ // tests/Feature/Http/Controllers/SignupControllerTest.php:38
  "name" => "山本 香織"
  "email" => "ryosuke70@example.com"
  "email_verified_at" => "2023-10-28T03:26:27.000000Z"
]
```

`tests/Feature/Http/Controllers/SignupControllerTest.php`を編集  

```php:SignupControllerTest.php
<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
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

        $validData = User::factory()->raw(); // 編集 初めから配列化ができる
        dd($validData);

        $this->post('signup', $validData)
            ->assertOk();

        unset($validData['password']);

        $this->assertDatabaseHas('users', $validData);

        $user = User::firstWhere($validData);
        // $this->assertNotNull($user);

        $this->assertTrue(Hash::check('hogehoge', $user->password));
    }
}
```

- `$ php artisan test --filter ユーザー登録できる`を実行  

```:terminal
array:5 [ // tests/Feature/Http/Controllers/SignupControllerTest.php:38
  "name" => "渚 花子"
  "email" => "mwakamatsu@example.com"
  "email_verified_at" => Illuminate\Support\Carbon @1698463860^ {#1379
    #endOfTime: false
    #startOfTime: false
    #constructedObjectId: "00000000000005630000000000000000"
    #localMonthsOverflow: null
    #localYearsOverflow: null
    #localStrictModeEnabled: null
    #localHumanDiffOptions: null
    #localToStringFormat: null
    #localSerializer: null
    #localMacros: null
    #localGenericMacros: null
    #localFormatFunction: null
    #localTranslator: null
    #dumpProperties: array:3 [
      0 => "date"
      1 => "timezone_type"
      2 => "timezone"
    ]
    #dumpLocale: null
    #dumpDateProperties: null
    date: 2023-10-28 12:31:00.889611 Asia/Tokyo (+09:00)
  }
  "password" => "$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi"
  "remember_token" => "HxELhMPqkp"
]
```

・ __上記の通りhidden設定(Userモデルにてhidden設定されている)されている箇所も出力される__  

しかし、いらない項目もあるので一旦UserFactoryの方を一部コメントアウトしてみる  

`database/factories/UserFactory.php`を編集  

```php:UserFactory.php
<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            // 'email_verified_at' => now(), // コメントアウト
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            // 'remember_token' => Str::random(10), // コメントアウト
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return static
     */
    public function unverified()
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
```

- `$ php artisan test --filter ユーザー登録できる`を実行  

```:terminal
array:3 [ // tests/Feature/Http/Controllers/SignupControllerTest.php:38
  "name" => "宇野 陽一"
  "email" => "naoto.tanabe@example.com"
  "password" => "$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi"
]
```

上記結果はハッシュ化されたパスワードが出力されてしまっている。データ登録する際は素のパスワードが欲しい。  
ステートの機能を使って上書きしてあげる方法がある。

`database/factories/UserFactory.php`を編集  

```php:UserFactory.php
<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            // 'email_verified_at' => now(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            // 'remember_token' => Str::random(10),
        ];
    }

    // 追加
    public function validData()
    {
        return $this->state(function (array $attributes) {
            return [
                'password' => 'abcd1234',
            ];
        });
    }
    // ここまで

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return static
     */
    public function unverified()
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
```

`tests/Feature/Http/Controllers/SignupControllerTest.php`を編集  

```php:SignupControllerTest.php
<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
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

        // $validData = User::factory()->raw();
        $validData = User::factory()->validData()->raw(); // 編集
        dd($validData);

        $this->post('signup', $validData)
            ->assertOk();

        unset($validData['password']);

        $this->assertDatabaseHas('users', $validData);

        $user = User::firstWhere($validData);
        // $this->assertNotNull($user);

        $this->assertTrue(Hash::check('hogehoge', $user->password));
    }
}
```

- `$ php artisan test --filter ユーザー登録できる`を実行  

```:terminal
array:3 [ // tests/Feature/Http/Controllers/SignupControllerTest.php:39
  "name" => "田辺 修平"
  "email" => "shuhei83@example.org"
  "password" => "hogehoge"
]
```

しかし、実際はhiddenされているデータがあるので`UserFactory.php`のコメントアウトした部分を解除してみる  

`database/factories/UserFactory.php`を編集  

```php:UserFactory.php
<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(), // コメントアウト解除
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10), // コメントアウト解除
        ];
    }

    public function validData()
    {
        return $this->state(function (array $attributes) {
            return [
                'password' => 'hogehoge',
            ];
        });
    }

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return static
     */
    public function unverified()
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
```

- `$ php artisan test --filter ユーザー登録できる`を実行  

```:terminal
array:5 [ // tests/Feature/Http/Controllers/SignupControllerTest.php:39
  "name" => "杉山 裕美子"
  "email" => "oyoshimoto@example.com"
  "email_verified_at" => Illuminate\Support\Carbon @1698465152^ {#1382
    #endOfTime: false
    #startOfTime: false
    #constructedObjectId: "00000000000005660000000000000000"
    #localMonthsOverflow: null
    #localYearsOverflow: null
    #localStrictModeEnabled: null
    #localHumanDiffOptions: null
    #localToStringFormat: null
    #localSerializer: null
    #localMacros: null
    #localGenericMacros: null
    #localFormatFunction: null
    #localTranslator: null
    #dumpProperties: array:3 [
      0 => "date"
      1 => "timezone_type"
      2 => "timezone"
    ]
    #dumpLocale: null
    #dumpDateProperties: null
    date: 2023-10-28 12:52:32.578753 Asia/Tokyo (+09:00)
  }
  "password" => "hogehoge"
  "remember_token" => "Cmu3TfuGHS"
]
```

hiddenされているのが再度出力されてしまう。データ登録するにはテストにとって邪魔な項目になる  

`database/factories/UserFactory.php`を編集  

```php:UserFactory.php
<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
        ];
    }

    // 編集
    public function validData()
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => 'hogehoge', // password
        ];
    }
    // ここまで

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return static
     */
    public function unverified()
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
```

`tests/Feature/Http/Controllers/SignupControllerTest.php`を編集  

```php:SignupControllerTest.php
<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
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

        // $validData = User::factory()->raw();
        $validData = User::factory()->validData(); // 編集
        dd($validData);

        $this->post('signup', $validData)
            ->assertOk();

        unset($validData['password']);

        $this->assertDatabaseHas('users', $validData);

        $user = User::firstWhere($validData);
        // $this->assertNotNull($user);

        $this->assertTrue(Hash::check('hogehoge', $user->password));
    }
}
```

- `$ php artisan test --filter ユーザー登録できる`を実行  

```:terminal
array:3 [ // tests/Feature/Http/Controllers/SignupControllerTest.php:39
  "name" => "山田 裕美子"
  "email" => "mikako.ogaki@example.net"
  "password" => "hogehoge"
]
```

上記のようにうまく出力される  

`tests/Feature/Http/Controllers/SignupControllerTest.php`を編集  

```php:SignupControllerTest.php
<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
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

        // $validData = User::factory()->raw();
        $validData = User::factory()->validData();
        // dd($validData); // コメントアウト解除

        $this->post('signup', $validData)
            ->assertOk();

        unset($validData['password']);

        $this->assertDatabaseHas('users', $validData);

        $user = User::firstWhere($validData);
        // $this->assertNotNull($user);

        $this->assertTrue(Hash::check('hogehoge', $user->password));
    }
}
```

- `$ php artisan test --filter ユーザー登録できる`を実行  

```:terminal
  PASS  Tests\Feature\Http\Controllers\SignupControllerTest
  ✓ ユーザー登録できる

  Tests:  1 passed
  Time:   0.35s
```
