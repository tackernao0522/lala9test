# Section2

## 38, ユーザー登録、 その6、 検証3

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

        app()->setLocale('testing');

        // $this->post($url, ['name' => ''])->assertSessionHasErrors(['name' => 'nameは必ず指定してください。']);
        $this->post($url, ['name' => ''])->assertInvalid(['name' => 'required']);
        $this->post($url, ['name' => str_repeat('あ', 21)])->assertInvalid(['name' => 'max']);
        $this->post($url, ['name' => str_repeat('あ', 20)])->assertValid('name');

        $this->post($url, ['email' => ''])->assertInvalid(['email' => 'required']); // 追加
    }
}
```

- `$ php artisan test --filter 不正なデータではユーザー登録できない`を実行  

```:terminal
   FAIL  Tests\Feature\Http\Controllers\SignupControllerTest
  ⨯ 不正なデータではユーザー登録できない

  ---

  • Tests\Feature\Http\Controllers\SignupControllerTest > 不正なデータではユーザー登録できない
  Failed to find a validation error in session for key: 'required'
  
  Response has the following validation errors in the session:
  
  {
      "name": [
          "required"
      ]
  }
  
  Failed asserting that an array has the key 'email'.
  
  The following exception occurred during the last request:
  
  PDOException: SQLSTATE[23000]: Integrity constraint violation: 19 NOT NULL constraint failed: users.email in /Applications/MAMP/htdocs/lara9test/vendor/laravel/framework/src/Illuminate/Database/Connection.php:545
  Stack trace:

    <!-- 〜省略〜 -->
  
  ----------------------------------------------------------------------------------
  
  SQLSTATE[23000]: Integrity constraint violation: 19 NOT NULL constraint failed: users.email (SQL: insert into "users" ("name", "email", "password", "updated_at", "created_at") values (ああああああああああああああああああああ, ?, $2y$04$8x9Juj4c9f8N2/PLQSeVUe0k0qblvYRDtEzz4YvYFTYRlK70J/Y6., 2023-11-03 14:59:10, 2023-11-03 14:59:10))

  at tests/Feature/Http/Controllers/SignupControllerTest.php:77
     73▕         $this->post($url, ['name' => ''])->assertInvalid(['name' => 'required']);
     74▕         $this->post($url, ['name' => str_repeat('あ', 21)])->assertInvalid(['name' => 'max']);
     75▕         $this->post($url, ['name' => str_repeat('あ', 20)])->assertValid('name');
     76▕ 
  ➜  77▕         $this->post($url, ['email' => ''])->assertInvalid(['email' => 'required']);
     78▕     }
     79▕ }
     80▕ 


  Tests:  1 failed
  Time:   0.86s
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
            'name' => 'required|max:20',
            'email' => 'required', // 追加
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
   PASS  Tests\Feature\Http\Controllers\SignupControllerTest
  ✓ 不正なデータではユーザー登録できない

  Tests:  1 passed
  Time:   0.32s
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

        app()->setLocale('testing');

        // $this->post($url, ['name' => ''])->assertSessionHasErrors(['name' => 'nameは必ず指定してください。']);
        $this->post($url, ['name' => ''])->assertInvalid(['name' => 'required']);
        $this->post($url, ['name' => str_repeat('あ', 21)])->assertInvalid(['name' => 'max']);
        $this->post($url, ['name' => str_repeat('あ', 20)])->assertValid('name');

        $this->post($url, ['email' => ''])->assertInvalid(['email' => 'required']);
        $this->post($url, ['email' => 'aa@bb@cc'])->assertInvalid(['email' => 'email']); // 追加
    }
}
```

- `$ php artisan test --filter 不正なデータではユーザー登録できない`を実行  

```:terminal
 FAIL  Tests\Feature\Http\Controllers\SignupControllerTest
  ⨯ 不正なデータではユーザー登録できない

  ---

  • Tests\Feature\Http\Controllers\SignupControllerTest > 不正なデータではユーザー登録できない
  Failed to find a validation error in session for key: 'email'
  
  Response has the following validation errors in the session:
  
  {
      "name": [
          "required"
      ]
  }
  
  Failed asserting that an array has the key 'email'.

  at tests/Feature/Http/Controllers/SignupControllerTest.php:78
     74▕         $this->post($url, ['name' => str_repeat('あ', 21)])->assertInvalid(['name' => 'max']);
     75▕         $this->post($url, ['name' => str_repeat('あ', 20)])->assertValid('name');
     76▕ 
     77▕         $this->post($url, ['email' => ''])->assertInvalid(['email' => 'required']);
  ➜  78▕         $this->post($url, ['email' => 'aa@bb@cc'])->assertInvalid(['email' => 'email']);
     79▕     }
     80▕ }
     81▕ 


  Tests:  1 failed
  Time:   0.28s
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
            'name' => 'required|max:20',
            'email' => 'required|email', // 編集
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
  PASS  Tests\Feature\Http\Controllers\SignupControllerTest
  ✓ 不正なデータではユーザー登録できない

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

        app()->setLocale('testing');

        // $this->post($url, ['name' => ''])->assertSessionHasErrors(['name' => 'nameは必ず指定してください。']);
        $this->post($url, ['name' => ''])->assertInvalid(['name' => 'required']);
        $this->post($url, ['name' => str_repeat('あ', 21)])->assertInvalid(['name' => 'max']);
        $this->post($url, ['name' => str_repeat('あ', 20)])->assertValid('name');

        $this->post($url, ['email' => ''])->assertInvalid(['email' => 'required']);
        $this->post($url, ['email' => 'aa@bb@cc'])->assertInvalid(['email' => 'email']);
        $this->post($url, ['email' => 'aa@ああ.net'])->assertInvalid(['email' => 'email']);
    }
}
```

- `$ php artisan test --filter 不正なデータではユーザー登録できない`を実行  

```:terminal
  FAIL  Tests\Feature\Http\Controllers\SignupControllerTest
  ⨯ 不正なデータではユーザー登録できない

  ---

  • Tests\Feature\Http\Controllers\SignupControllerTest > 不正なデータではユーザー登録できない
  Failed to find a validation error in session for key: 'email'
  
  Response has the following validation errors in the session:
  
  {
      "name": [
          "required"
      ]
  }
  
  Failed asserting that an array has the key 'email'.

  at tests/Feature/Http/Controllers/SignupControllerTest.php:79
     75▕         $this->post($url, ['name' => str_repeat('あ', 20)])->assertValid('name');
     76▕ 
     77▕         $this->post($url, ['email' => ''])->assertInvalid(['email' => 'required']);
     78▕         $this->post($url, ['email' => 'aa@bb@cc'])->assertInvalid(['email' => 'email']);
  ➜  79▕         $this->post($url, ['email' => 'aa@ああ.net'])->assertInvalid(['email' => 'email']);
     80▕     }
     81▕ }
     82▕ 


  Tests:  1 failed
  Time:   0.29s
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
            'name' => 'required|max:20',
            'email' => 'required|email:filter', // 編集
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
   PASS  Tests\Feature\Http\Controllers\SignupControllerTest
  ✓ 不正なデータではユーザー登録できない

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

        User::factory()->create(['email' => 'aaa@bbb.net']); // 追加

        // $this->post($url, [])
        //     ->assertRedirect();

        // 注意点
        // (1) カスタムメッセージを設定している時は、そちらが優先されてしまう
        // (2) 入力エラーが出る前に言語ファイルを読もうとしている箇所がある時は、そちらもtestingに対応させる必要あり

        app()->setLocale('testing');

        // $this->post($url, ['name' => ''])->assertSessionHasErrors(['name' => 'nameは必ず指定してください。']);
        $this->post($url, ['name' => ''])->assertInvalid(['name' => 'required']);
        $this->post($url, ['name' => str_repeat('あ', 21)])->assertInvalid(['name' => 'max']);
        $this->post($url, ['name' => str_repeat('あ', 20)])->assertValid('name');

        $this->post($url, ['email' => ''])->assertInvalid(['email' => 'required']);
        $this->post($url, ['email' => 'aa@bb@cc'])->assertInvalid(['email' => 'email']);
        $this->post($url, ['email' => 'aa@ああ.net'])->assertInvalid(['email' => 'email']);
        $this->post($url, ['email' => 'aaa@bbb.net'])->assertInvalid(['email' => 'unique']); // 追加
    }
}
```

- `$ php artisan test --filter 不正なデータではユーザー登録できない`を実行  

```:terminal
 FAIL  Tests\Feature\Http\Controllers\SignupControllerTest
  ⨯ 不正なデータではユーザー登録できない

  ---

  • Tests\Feature\Http\Controllers\SignupControllerTest > 不正なデータではユーザー登録できない
  Failed to find a validation error in session for key: 'unique'
  
  Response has the following validation errors in the session:
  
  {
      "name": [
          "required"
      ]
  }
  
  Failed asserting that an array has the key 'email'.

  at tests/Feature/Http/Controllers/SignupControllerTest.php:82
     78▕ 
     79▕         $this->post($url, ['email' => ''])->assertInvalid(['email' => 'required']);
     80▕         $this->post($url, ['email' => 'aa@bb@cc'])->assertInvalid(['email' => 'email']);
     81▕         $this->post($url, ['email' => 'aa@ああ.net'])->assertInvalid(['email' => 'email']);
  ➜  82▕         $this->post($url, ['email' => 'aaa@bbb.net'])->assertInvalid(['email' => 'unique']);
     83▕     }
     84▕ }
     85▕ 


  Tests:  1 failed
  Time:   0.36s
```

`app/Http/Controllers/SignupController.php`を編集  

```php:SignupController.php
<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Validation\Rule; // 追加
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
            'name' => ['required', 'max:20'], // 編集
            'email' => ['required', 'email:filter', Rule::unique('users')], // 編集
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
   PASS  Tests\Feature\Http\Controllers\SignupControllerTest
  ✓ 不正なデータではユーザー登録できない

  Tests:  1 passed
  Time:   0.31s
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

        User::factory()->create(['email' => 'aaa@bbb.net']);

        // $this->post($url, [])
        //     ->assertRedirect();

        // 注意点
        // (1) カスタムメッセージを設定している時は、そちらが優先されてしまう
        // (2) 入力エラーが出る前に言語ファイルを読もうとしている箇所がある時は、そちらもtestingに対応させる必要あり

        app()->setLocale('testing');

        // $this->post($url, ['name' => ''])->assertSessionHasErrors(['name' => 'nameは必ず指定してください。']);
        $this->post($url, ['name' => ''])->assertInvalid(['name' => 'required']);
        $this->post($url, ['name' => str_repeat('あ', 21)])->assertInvalid(['name' => 'max']);
        $this->post($url, ['name' => str_repeat('あ', 20)])->assertValid('name');

        $this->post($url, ['email' => ''])->assertInvalid(['email' => 'required']);
        $this->post($url, ['email' => 'aa@bb@cc'])->assertInvalid(['email' => 'email']);
        $this->post($url, ['email' => 'aa@ああ.net'])->assertInvalid(['email' => 'email']);
        $this->post($url, ['email' => 'aaa@bbb.net'])->assertInvalid(['email' => 'unique']);

        $this->post($url, ['password' => ''])->assertInvalid(['password' => 'required']);
    }
}
```

- `$ php artisan test --filter 不正なデータではユーザー登録できない`を実行  

```:terminal
   FAIL  Tests\Feature\Http\Controllers\SignupControllerTest
  ⨯ 不正なデータではユーザー登録できない

  ---

  • Tests\Feature\Http\Controllers\SignupControllerTest > 不正なデータではユーザー登録できない
  Failed to find a validation error in session for key: 'required'
  
  Response has the following validation errors in the session:
  
  {
      "name": [
          "required"
      ],
      "email": [
          "required"
      ]
  }
  
  Failed asserting that an array has the key 'password'.
  
  The following errors occurred during the last request:
  
  required
  required

  at tests/Feature/Http/Controllers/SignupControllerTest.php:84
     80▕         $this->post($url, ['email' => 'aa@bb@cc'])->assertInvalid(['email' => 'email']);
     81▕         $this->post($url, ['email' => 'aa@ああ.net'])->assertInvalid(['email' => 'email']);
     82▕         $this->post($url, ['email' => 'aaa@bbb.net'])->assertInvalid(['email' => 'unique']);
     83▕ 
  ➜  84▕         $this->post($url, ['password' => ''])->assertInvalid(['password' => 'required']);
     85▕     }
     86▕ }
     87▕ 


  Tests:  1 failed
  Time:   0.46s
```

`app/Http/Controllers/SignupController.php`を編集  

```php:SignupController.php
<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class SignupController extends Controller
{
    public function index()
    {
        return view('signup');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'max:20'],
            'email' => ['required', 'email:filter', Rule::unique('users')],
            'password' => ['required'], // 追加
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
   PASS  Tests\Feature\Http\Controllers\SignupControllerTest
  ✓ 不正なデータではユーザー登録できない

  Tests:  1 passed
  Time:   0.38s
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

        User::factory()->create(['email' => 'aaa@bbb.net']);

        // $this->post($url, [])
        //     ->assertRedirect();

        // 注意点
        // (1) カスタムメッセージを設定している時は、そちらが優先されてしまう
        // (2) 入力エラーが出る前に言語ファイルを読もうとしている箇所がある時は、そちらもtestingに対応させる必要あり

        app()->setLocale('testing');

        // $this->post($url, ['name' => ''])->assertSessionHasErrors(['name' => 'nameは必ず指定してください。']);
        $this->post($url, ['name' => ''])->assertInvalid(['name' => 'required']);
        $this->post($url, ['name' => str_repeat('あ', 21)])->assertInvalid(['name' => 'max']);
        $this->post($url, ['name' => str_repeat('あ', 20)])->assertValid('name');

        $this->post($url, ['email' => ''])->assertInvalid(['email' => 'required']);
        $this->post($url, ['email' => 'aa@bb@cc'])->assertInvalid(['email' => 'email']);
        $this->post($url, ['email' => 'aa@ああ.net'])->assertInvalid(['email' => 'email']);
        $this->post($url, ['email' => 'aaa@bbb.net'])->assertInvalid(['email' => 'unique']);

        $this->post($url, ['password' => ''])->assertInvalid(['password' => 'required']);
        $this->post($url, ['password' => 'abc1234'])->assertInvalid(['password' => 'min']); // 追加
    }
}
```

- `$ php artisan test --filter 不正なデータではユーザー登録できない`を実行  

```:terminal
 FAIL  Tests\Feature\Http\Controllers\SignupControllerTest
  ⨯ 不正なデータではユーザー登録できない

  ---

  • Tests\Feature\Http\Controllers\SignupControllerTest > 不正なデータではユーザー登録できない
  Failed to find a validation error in session for key: 'min'
  
  Response has the following validation errors in the session:
  
  {
      "name": [
          "required"
      ],
      "email": [
          "required"
      ]
  }
  
  Failed asserting that an array has the key 'password'.
  
  The following errors occurred during the last request:
  
  required
  required

  at tests/Feature/Http/Controllers/SignupControllerTest.php:85
     81▕         $this->post($url, ['email' => 'aa@ああ.net'])->assertInvalid(['email' => 'email']);
     82▕         $this->post($url, ['email' => 'aaa@bbb.net'])->assertInvalid(['email' => 'unique']);
     83▕ 
     84▕         $this->post($url, ['password' => ''])->assertInvalid(['password' => 'required']);
  ➜  85▕         $this->post($url, ['password' => 'abc1234'])->assertInvalid(['password' => 'min']);
     86▕     }
     87▕ }
     88▕ 


  Tests:  1 failed
  Time:   0.33s
```

`app/Http/Controllers/SignupController.php`を編集  

```php:SignupController.php
<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class SignupController extends Controller
{
    public function index()
    {
        return view('signup');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'max:20'],
            'email' => ['required', 'email:filter', Rule::unique('users')],
            'password' => ['required', 'min:8'], // 編集
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
  PASS  Tests\Feature\Http\Controllers\SignupControllerTest
  ✓ 不正なデータではユーザー登録できない

  Tests:  1 passed
  Time:   0.40s
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

        User::factory()->create(['email' => 'aaa@bbb.net']);

        // $this->post($url, [])
        //     ->assertRedirect();

        // 注意点
        // (1) カスタムメッセージを設定している時は、そちらが優先されてしまう
        // (2) 入力エラーが出る前に言語ファイルを読もうとしている箇所がある時は、そちらもtestingに対応させる必要あり

        app()->setLocale('testing');

        // $this->post($url, ['name' => ''])->assertSessionHasErrors(['name' => 'nameは必ず指定してください。']);
        $this->post($url, ['name' => ''])->assertInvalid(['name' => 'required']);
        $this->post($url, ['name' => str_repeat('あ', 21)])->assertInvalid(['name' => 'max']);
        $this->post($url, ['name' => str_repeat('あ', 20)])->assertValid('name');

        $this->post($url, ['email' => ''])->assertInvalid(['email' => 'required']);
        $this->post($url, ['email' => 'aa@bb@cc'])->assertInvalid(['email' => 'email']);
        $this->post($url, ['email' => 'aa@ああ.net'])->assertInvalid(['email' => 'email']);
        $this->post($url, ['email' => 'aaa@bbb.net'])->assertInvalid(['email' => 'unique']);

        $this->post($url, ['password' => ''])->assertInvalid(['password' => 'required']);
        $this->post($url, ['password' => 'abc1234'])->assertInvalid(['password' => 'min']);
        $this->post($url, ['password' => 'abc12345'])->assertValid(['password']); // 追加
    }
}
```

- `$ php artisan test --filter 不正なデータではユーザー登録できない`を実行  

```:terminal
   PASS  Tests\Feature\Http\Controllers\SignupControllerTest
  ✓ 不正なデータではユーザー登録できない

  Tests:  1 passed
  Time:   0.32s
```
