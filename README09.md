# Section2

## 36. ユーザー登録、 その4、 検証1

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
        // dd($validData);

        $this->post('signup', $validData)
            ->assertOk();

        unset($validData['password']);

        $this->assertDatabaseHas('users', $validData);

        $user = User::firstWhere($validData);
        // $this->assertNotNull($user);

        $this->assertTrue(Hash::check('hogehoge', $user->password));
    }

    // 追加
    /**
     * @test
     */
    function 不正なデータではユーザー登録できない()
    {
        $url = 'signup';

        $this->post($url, [])
            ->assertRedirect();
    }
    // ここまで
}
```

- `php artisan test --filter 不正なデータではユーザー登録できない`を実行  

```:terminal
  FAIL  Tests\Feature\Http\Controllers\SignupControllerTest
  ⨯ 不正なデータではユーザー登録できない

  ---

  • Tests\Feature\Http\Controllers\SignupControllerTest > 不正なデータではユーザー登録できない
  Expected response status code [201, 301, 302, 303, 307, 308] but received 500.
  Failed asserting that false is true.
  
  The following exception occurred during the last request:
  
  PDOException: SQLSTATE[23000]: Integrity constraint violation: 19 NOT NULL constraint failed: users.
  
  <!-- 〜省略〜 -->
  ----------------------------------------------------------------------------------
  
  SQLSTATE[23000]: Integrity constraint violation: 19 NOT NULL constraint failed: users.name (SQL: insert into "users" ("name", "email", "password", "updated_at", "created_at") values (?, ?, $2y$04$qUwj8hxWLU/kphzvm9yQh.n9Bd5LEnos2igHSpYW9sE9X05s1Gt7a, 2023-11-01 18:43:38, 2023-11-01 18:43:38))

  at tests/Feature/Http/Controllers/SignupControllerTest.php:62
     58▕     {
     59▕         $url = 'signup';
     60▕ 
     61▕         $this->post($url, [])
  ➜  62▕             ->assertRedirect();
     63▕     }
     64▕ }
     65▕ 


  Tests:  1 failed
  Time:   0.75s
```

`tests/Feature/Http/Controllers/SignupControllerTest.php`を編集  

```php:SignupContollerTest.php
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
        // dd($validData);

        $this->post('signup', $validData)
            ->assertOk();

        unset($validData['password']);

        $this->assertDatabaseHas('users', $validData);

        $user = User::firstWhere($validData);
        // $this->assertNotNull($user);

        $this->assertTrue(Hash::check('hogehoge', $user->password));
    }

    /**
     * @test
     */
    function 不正なデータではユーザー登録できない()
    {
        // $this->withoutExceptionHandling(); // 古いバージョンの時は記述すると良い

        $url = 'signup';

        // コメントアウト
        // $this->post($url, [])
        //     ->assertRedirect();

        $this->post($url, ['name' => ''])->assertInvalid(['name' => 'エラー']);
    }
}
```

- `$ php artisan test --filter 不正なデータではユーザー登録できない`を実行  

```:terminal
   FAIL  Tests\Feature\Http\Controllers\SignupControllerTest
  ⨯ 不正なデータではユーザー登録できない

  ---

  • Tests\Feature\Http\Controllers\SignupControllerTest > 不正なデータではユーザー登録できない
  Session is missing expected key [errors].
  Failed asserting that false is true.
  
  The following exception occurred during the last request:
  
  PDOException: SQLSTATE[23000]: Integrity constraint violation: 19 NOT NULL constraint failed: users.

  <!-- 〜省略〜 -->
  
  ----------------------------------------------------------------------------------
  
  SQLSTATE[23000]: Integrity constraint violation: 19 NOT NULL constraint failed: users.name (SQL: insert into "users" ("name", "email", "password", "updated_at", "created_at") values (?, ?, $2y$04$T8ZqevYpjglRpddIsUL.7.AVYfnJpqgdXm06QF9xl0IcpbUtnrdKW, 2023-11-01 18:51:19, 2023-11-01 18:51:19))

  at tests/Feature/Http/Controllers/SignupControllerTest.php:66
     62▕ 
     63▕         // $this->post($url, [])
     64▕         //     ->assertRedirect();
     65▕ 
  ➜  66▕         $this->post($url, ['name' => ''])->assertInvalid(['name' => 'エラー']);
     67▕     }
     68▕ }
     69▕ 


  Tests:  1 failed
  Time:   0.84s
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
        // 追加
        $request->validate([
            'name' => 'required',
        ]);
        // ここまで

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);
    }
}
```

- `$ php artisan test --filter 不正なデータではユーザー登録できない`を実行  

```:terminal
   FAIL  Tests\Feature\Http\Controllers\SignupControllerTest
  ⨯ 不正なデータではユーザー登録できない

  ---

  • Tests\Feature\Http\Controllers\SignupControllerTest > 不正なデータではユーザー登録できない
  Failed to find a validation error for key and message: 'name' => 'エラー'
  
  Response has the following validation errors in the session:
  
  {
      "name": [
          "nameは必ず指定してください。"
      ]
  }

  at tests/Feature/Http/Controllers/SignupControllerTest.php:66
     62▕ 
     63▕         // $this->post($url, [])
     64▕         //     ->assertRedirect();
     65▕ 
  ➜  66▕         $this->post($url, ['name' => ''])->assertInvalid(['name' => 'エラー']);
     67▕     }
     68▕ }
     69▕ 


  Tests:  1 failed
  Time:   0.48s
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
        $validData = User::factory()->validData();
        // dd($validData);

        $this->post('signup', $validData)
            ->assertOk();

        unset($validData['password']);

        $this->assertDatabaseHas('users', $validData);

        $user = User::firstWhere($validData);
        // $this->assertNotNull($user);

        $this->assertTrue(Hash::check('hogehoge', $user->password));
    }

    /**
     * @test
     */
    function 不正なデータではユーザー登録できない()
    {
        // $this->withoutExceptionHandling(); // 古いバージョンの時は記述すると良い

        $url = 'signup';

        // $this->post($url, [])
        //     ->assertRedirect();

        $this->post($url, ['name' => ''])->assertInvalid(['name' => '指定']); // 編集 部分一致で書けば良い
    }
}
```

- `$ php artisan test --filter 不正なデータではユーザー登録できない` を実行  

```:terminal
  PASS  Tests\Feature\Http\Controllers\SignupControllerTest
  ✓ 不正なデータではユーザー登録できない

  Tests:  1 passed
  Time:   0.47s
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
        $validData = User::factory()->validData();
        // dd($validData);

        $this->post('signup', $validData)
            ->assertOk();

        unset($validData['password']);

        $this->assertDatabaseHas('users', $validData);

        $user = User::firstWhere($validData);
        // $this->assertNotNull($user);

        $this->assertTrue(Hash::check('hogehoge', $user->password));
    }

    /**
     * @test
     */
    function 不正なデータではユーザー登録できない()
    {
        // $this->withoutExceptionHandling(); // 古いバージョンの時は記述すると良い

        $url = 'signup';

        // $this->post($url, [])
        //     ->assertRedirect();
        // assertSessionHasErrors は古いアサーションでありバリデーションコメントはフルで書かなければならない
        // dumpSession()を挟むとエラーコメントがわかる
        // $this->post($url, ['name' => ''])->dumpSession()->assertSessionHasErrors(['name' => 'nameは必ず指定してください。']);
        $this->post($url, ['name' => ''])->assertInvalid(['name' => '指定']);
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
        $validData = User::factory()->validData();
        // dd($validData);

        $this->post('signup', $validData)
            ->assertOk();

        unset($validData['password']);

        $this->assertDatabaseHas('users', $validData);

        $user = User::firstWhere($validData);
        // $this->assertNotNull($user);

        $this->assertTrue(Hash::check('hogehoge', $user->password));
    }

    /**
     * @test
     */
    function 不正なデータではユーザー登録できない()
    {
        // $this->withoutExceptionHandling(); // 古いバージョンの時は記述すると良い

        $url = 'signup';

        // $this->post($url, [])
        //     ->assertRedirect();

        // $this->post($url, ['name' => ''])->assertSessionHasErrors(['name' => 'nameは必ず指定してください。']);
        $this->post($url, ['name' => ''])->assertInvalid(['name' => '指定']);
        $this->post($url, ['name' => str_repeat('あ', 21)])->assertInvalid(['name' => 'max']); // 追加
    }
}
```

- `$ php artisan test --filter 不正なデータではユーザー登録できない`を実行  

```:terminal
   FAIL  Tests\Feature\Http\Controllers\SignupControllerTest
  ⨯ 不正なデータではユーザー登録できない

  ---

  • Tests\Feature\Http\Controllers\SignupControllerTest > 不正なデータではユーザー登録できない
  Session is missing expected key [errors].
  Failed asserting that false is true.
  
  The following exception occurred during the last request:
  
  PDOException: SQLSTATE[23000]: Integrity constraint violation: 19 NOT NULL constraint failed: users.email in /Applications/MAMP/htdocs/lara9test/vendor/laravel/framework/src/Illuminate/Database/Connection.php:545

  <!-- 省略 -->
  
  ----------------------------------------------------------------------------------
  
  SQLSTATE[23000]: Integrity constraint violation: 19 NOT NULL constraint failed: users.email (SQL: insert into "users" ("name", "email", "password", "updated_at", "created_at") values (あああああああああああああああああああああ, ?, $2y$04$Tv0XgU1QVjhZ4sxYVVX6lOCMD.QKdfNwmJoKNARFoYm2RXlvnbFsi, 2023-11-01 19:12:47, 2023-11-01 19:12:47))

  at tests/Feature/Http/Controllers/SignupControllerTest.php:68
     64▕         //     ->assertRedirect();
     65▕ 
     66▕         // $this->post($url, ['name' => ''])->assertSessionHasErrors(['name' => 'nameは必ず指定してください。']);
     67▕         $this->post($url, ['name' => ''])->assertInvalid(['name' => '指定']);
  ➜  68▕         $this->post($url, ['name' => str_repeat('あ', 21)])->assertInvalid(['name' => 'max']);
     69▕     }
     70▕ }
     71▕ 


  Tests:  1 failed
  Time:   0.62s
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
        $request->validate([
            'name' => 'required|max:20', // 編集
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);
    }
}
```

- `$ php artisan test --filter 不正なデータではユーザー登録できない`を実行  

```:terminal
   FAIL  Tests\Feature\Http\Controllers\SignupControllerTest
  ⨯ 不正なデータではユーザー登録できない

  ---

  • Tests\Feature\Http\Controllers\SignupControllerTest > 不正なデータではユーザー登録できない
  Failed to find a validation error for key and message: 'name' => 'max'
  
  Response has the following validation errors in the session:
  
  {
      "name": [
          "nameは、20文字以下で指定してください。"
      ]
  }

  at tests/Feature/Http/Controllers/SignupControllerTest.php:68
     64▕         //     ->assertRedirect();
     65▕ 
     66▕         // $this->post($url, ['name' => ''])->assertSessionHasErrors(['name' => 'nameは必ず指定してください。']);
     67▕         $this->post($url, ['name' => ''])->assertInvalid(['name' => '指定']);
  ➜  68▕         $this->post($url, ['name' => str_repeat('あ', 21)])->assertInvalid(['name' => 'max']);
     69▕     }
     70▕ }
     71▕ 


  Tests:  1 failed
  Time:   0.44s
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
        $validData = User::factory()->validData();
        // dd($validData);

        $this->post('signup', $validData)
            ->assertOk();

        unset($validData['password']);

        $this->assertDatabaseHas('users', $validData);

        $user = User::firstWhere($validData);
        // $this->assertNotNull($user);

        $this->assertTrue(Hash::check('hogehoge', $user->password));
    }

    /**
     * @test
     */
    function 不正なデータではユーザー登録できない()
    {
        // $this->withoutExceptionHandling(); // 古いバージョンの時は記述すると良い

        $url = 'signup';

        // $this->post($url, [])
        //     ->assertRedirect();

        // $this->post($url, ['name' => ''])->assertSessionHasErrors(['name' => 'nameは必ず指定してください。']);
        $this->post($url, ['name' => ''])->assertInvalid(['name' => '指定']);
        $this->post($url, ['name' => str_repeat('あ', 21)])->assertInvalid(['name' => '20文字以下']); // 編集
    }
}
```

- `$ php artisan test --filter 不正なデータではユーザー登録できない`を実行  

```:terminal
  PASS  Tests\Feature\Http\Controllers\SignupControllerTest
  ✓ 不正なデータではユーザー登録できない

  Tests:  1 passed
  Time:   0.27s
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
        $validData = User::factory()->validData();
        // dd($validData);

        $this->post('signup', $validData)
            ->assertOk();

        unset($validData['password']);

        $this->assertDatabaseHas('users', $validData);

        $user = User::firstWhere($validData);
        // $this->assertNotNull($user);

        $this->assertTrue(Hash::check('hogehoge', $user->password));
    }

    /**
     * @test
     */
    function 不正なデータではユーザー登録できない()
    {
        // $this->withoutExceptionHandling(); // 古いバージョンの時は記述すると良い

        $url = 'signup';

        // $this->post($url, [])
        //     ->assertRedirect();

        // $this->post($url, ['name' => ''])->assertSessionHasErrors(['name' => 'nameは必ず指定してください。']);
        $this->post($url, ['name' => ''])->assertInvalid(['name' => '指定']);
        $this->post($url, ['name' => str_repeat('あ', 21)])->assertInvalid(['name' => '20文字以下']);
        $this->post($url, ['name' => str_repeat('あ', 20)])->assertValid('name'); // 20文字の場合
    }
}
```

- `$ php artisan test --filter 不正なデータではユーザー登録できない`を実行  

```:terminal
   PASS  Tests\Feature\Http\Controllers\SignupControllerTest
  ✓ 不正なデータではユーザー登録できない

  Tests:  1 passed
  Time:   0.82s
```

## 37. ユーザー登録、 その5、 検証2

- `$ mkdir lang/testing && cp lang/en/validation.php lang/testing/validation.php`を実行  

`lang/testing/validation.php`を編集  

```php:validation.php
<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => 'accepted',
    'accepted_if' => 'accepted_if',
    'active_url' => 'active_url',
    'after' => 'after',
    'after_or_equal' => 'after_or_equal',
    'alpha' => 'alpha',
    'alpha_dash' => 'alpha_dash',
    'alpha_num' => 'alpha_num',
    'array' => 'array',
    'before' => 'before',
    'before_or_equal' => 'before_or_equal',
    'between' => [
        'array' => 'between',
        'file' => 'between',
        'numeric' => 'between',
        'string' => 'between',
    ],
    'boolean' => 'boolean',
    'confirmed' => 'confirmed',
    'current_password' => 'current_password',
    'date' => 'date',
    'date_equals' => 'date_equals',
    'date_format' => 'date_format',
    'declined' => 'declined',
    'declined_if' => 'declined_if',
    'different' => 'different',
    'digits' => 'digits',
    'digits_between' => 'digits_between',
    'dimensions' => 'dimensions',
    'distinct' => 'distinct',
    'email' => 'email',
    'ends_with' => 'ends_with',
    'enum' => 'enum',
    'exists' => 'exists',
    'file' => 'file',
    'filled' => 'filled',
    'gt' => [
        'array' => 'gt',
        'file' => 'gt',
        'numeric' => 'gt',
        'string' => 'gt',
    ],
    'gte' => [
        'array' => 'gte',
        'file' => 'gte',
        'numeric' => 'gte',
        'string' => 'gte',
    ],
    'image' => 'image',
    'in' => 'in',
    'in_array' => 'in_array',
    'integer' => 'integer',
    'ip' => 'ip',
    'ipv4' => 'ipv4',
    'ipv6' => 'ipv6',
    'json' => 'json',
    'lt' => [
        'array' => 'lt',
        'file' => 'lt',
        'numeric' => 'lt',
        'string' => 'lt',
    ],
    'lte' => [
        'array' => 'lte',
        'file' => 'lte',
        'numeric' => 'lte',
        'string' => 'lte',
    ],
    'mac_address' => 'mac_address',
    'max' => [
        'array' => 'max',
        'file' => 'max',
        'numeric' => 'max',
        'string' => 'max',
    ],
    'mimes' => 'mimes',
    'mimetypes' => 'mimetypes',
    'min' => [
        'array' => 'min',
        'file' => 'min',
        'numeric' => 'min',
        'string' => 'min',
    ],
    'multiple_of' => 'multiple_of',
    'not_in' => 'not_in',
    'not_regex' => 'not_regex',
    'numeric' => 'numeric',
    'password' => 'password',
    'present' => 'present',
    'prohibited' => 'prohibited',
    'prohibited_if' => 'prohibited_if',
    'prohibited_unless' => 'prohibited_unless',
    'prohibits' => 'prohibits',
    'regex' => 'regex',
    'required' => 'required',
    'required_array_keys' => 'required_array_keys',
    'required_if' => 'required_if',
    'required_unless' => 'required_unless',
    'required_with' => 'required_with',
    'required_with_all' => 'required_with_all',
    'required_without' => 'required_without',
    'required_without_all' => 'required_without_all',
    'same' => 'same',
    'size' => [
        'array' => 'size',
        'file' => 'size',
        'numeric' => 'size',
        'string' => 'size',
    ],
    'starts_with' => 'starts_with',
    'string' => 'string',
    'timezone' => 'timezone',
    'unique' => 'unique',
    'uploaded' => 'uploaded',
    'url' => 'url',
    'uuid' => 'uuid',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [],

];
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
        $validData = User::factory()->validData();
        // dd($validData);

        $this->post('signup', $validData)
            ->assertOk();

        unset($validData['password']);

        $this->assertDatabaseHas('users', $validData);

        $user = User::firstWhere($validData);
        // $this->assertNotNull($user);

        $this->assertTrue(Hash::check('hogehoge', $user->password));
    }

    /**
     * @test
     */
    function 不正なデータではユーザー登録できない()
    {
        // $this->withoutExceptionHandling(); // 古いバージョンの時は記述すると良い

        $url = 'signup';

        // $this->post($url, [])
        //     ->assertRedirect();

        // 注意点
        // (1) カスタムメッセージを設定している時は、そちらが優先されてしまう
        // (2) 入力エラーが出る前に言語ファイルを読もうとしている箇所がある時は、そちらもtestingに対応させる必要あり

        app()->setLocale('testing'); // 追加

        // $this->post($url, ['name' => ''])->assertSessionHasErrors(['name' => 'nameは必ず指定してください。']);
        $this->post($url, ['name' => ''])->assertInvalid(['name' => 'required']); // 編集
        $this->post($url, ['name' => str_repeat('あ', 21)])->assertInvalid(['name' => 'max']); // 編集
        $this->post($url, ['name' => str_repeat('あ', 20)])->assertValid('name');
    }
}
```

- `$ php artisan test --filter 不正なデータではユーザー登録できない` を実行  

```:terminal
   PASS  Tests\Feature\Http\Controllers\SignupControllerTest
  ✓ 不正なデータではユーザー登録できない

  Tests:  1 passed
  Time:   0.53s
```
