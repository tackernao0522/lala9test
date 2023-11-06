# Section2

## 39. ユーザー登録、 その7、 検証4

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

        $this->post($url, [])
            ->assertRedirect(); // コメントアウトを外す

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
        $this->post($url, ['password' => 'abc12345'])->assertValid(['password']);
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

        User::factory()->create(['email' => 'aaa@bbb.net']);

        $this->post($url, [])
            ->assertRedirect('signup'); // 編集 一つ手前にbackする設定になっている

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
        $this->post($url, ['password' => 'abc12345'])->assertValid(['password']);
    }
}
```

- `$ php artisan test --filter 不正なデータではユーザー登録できない`を実行  

```:terminal
   FAIL  Tests\Feature\Http\Controllers\SignupControllerTest
  ⨯ 不正なデータではユーザー登録できない

  ---

  • Tests\Feature\Http\Controllers\SignupControllerTest > 不正なデータではユーザー登録できない
  Failed asserting that two strings are equal.
  
  The following errors occurred during the last request:
  
  nameは必ず指定してください。
  emailは必ず指定してください。
  passwordは必ず指定してください。

  at tests/Feature/Http/Controllers/SignupControllerTest.php:66
     62▕ 
     63▕         User::factory()->create(['email' => 'aaa@bbb.net']);
     64▕ 
     65▕         $this->post($url, [])
  ➜  66▕             ->assertRedirect('signup');
     67▕ 
     68▕         // 注意点
     69▕         // (1) カスタムメッセージを設定している時は、そちらが優先されてしまう
     70▕         // (2) 入力エラーが出る前に言語ファイルを読もうとしている箇所がある時は、そちらもtestingに対応させる必要あり
  --- Expected
  +++ Actual
  @@ @@
  -'http://localhost/signup'
  +'http://localhost'

  Tests:  1 failed
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

        User::factory()->create(['email' => 'aaa@bbb.net']);

        $this->get('signup'); // 追加
        $this->post($url, [])
            ->assertRedirect('signup');

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
        $this->post($url, ['password' => 'abc12345'])->assertValid(['password']);
    }
}
```

- `$ php artisan test --filter 不正なデータではユーザー登録できない`を実行  

```:terminal
   PASS  Tests\Feature\Http\Controllers\SignupControllerTest
  ✓ 不正なデータではユーザー登録できない

  Tests:  1 passed
  Time:   0.36s
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

        // $this->get('signup');
        $this->from('signup')->post($url, [])
            ->assertRedirect('signup'); // fromを使うと$this->get('signup')と同じ意味になる

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
        $this->post($url, ['password' => 'abc12345'])->assertValid(['password']);
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

## 40. ユーザー登録、 その8、 後処理

- `__補足__`  

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

        // $this->get('signup');
        $this->from('signup')->post($url, [])
            ->assertRedirect('signup');

        // 注意点
        // (1) カスタムメッセージを設定している時は、そちらが優先されてしまう
        // (2) 入力エラーが出る前に言語ファイルを読もうとしている箇所がある時は、そちらもtestingに対応させる必要あり

        app()->setLocale('testing');

        dump(app()->getLocale()); // 追加 "testing"

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
        $this->post($url, ['password' => 'abc12345'])->assertValid(['password']);
    }

    // 追加
    /**
     * @test
     */
    function hogebar()
    {
        dump(app()->getLocale()); // "ja"

        $this->assertTrue(true);
    }
    // ここまで
}
```

- `$ php artisan test --filter SignupControllerTest`を実行  

```:terminal
"testing" // tests/Feature/Http/Controllers/SignupControllerTest.php:75
"ja" // tests/Feature/Http/Controllers/SignupControllerTest.php:97

   PASS  Tests\Feature\Http\Controllers\SignupControllerTest
  ✓ ユーザー登録画面が開ける
  ✓ ユーザー登録できる
  ✓ 不正なデータではユーザー登録できない
  ✓ hogebar

  Tests:  4 passed
  Time:   0.45s
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
            'password' => ['required', 'min:8'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // auth()->login($user); // 追加 コメントアウトしておく

        // return redirect('mypage/posts'); // 追加 コメントアウトしておく
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

        $this->assertAuthenticatedAs($user); // 追加
    }

    /**
     * @test
     */
    function 不正なデータではユーザー登録できない()
    {
        // $this->withoutExceptionHandling(); // 古いバージョンの時は記述すると良い

        $url = 'signup';

        User::factory()->create(['email' => 'aaa@bbb.net']);

        // $this->get('signup');
        $this->from('signup')->post($url, [])
            ->assertRedirect('signup');

        // 注意点
        // (1) カスタムメッセージを設定している時は、そちらが優先されてしまう
        // (2) 入力エラーが出る前に言語ファイルを読もうとしている箇所がある時は、そちらもtestingに対応させる必要あり

        app()->setLocale('testing');

        dump(app()->getLocale());

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
        $this->post($url, ['password' => 'abc12345'])->assertValid(['password']);
    }

    /**
     * @test
     */
    function hogebar()
    {
        dump(app()->getLocale());

        $this->assertTrue(true);
    }
}
```

- `$ php artisan test --filter ユーザー登録できる`を実行  

```:terminal
 FAIL  Tests\Feature\Http\Controllers\SignupControllerTest
  ⨯ ユーザー登録できる

  ---

  • Tests\Feature\Http\Controllers\SignupControllerTest > ユーザー登録できる
  The current user is not authenticated.
  Failed asserting that null is not null.

  at tests/Feature/Http/Controllers/SignupControllerTest.php:53
     49▕         // $this->assertNotNull($user);
     50▕ 
     51▕         $this->assertTrue(Hash::check('hogehoge', $user->password));
     52▕ 
  ➜  53▕         $this->assertAuthenticatedAs($user);
     54▕     }
     55▕ 
     56▕     /**
     57▕      * @test


  Tests:  1 failed
  Time:   0.31s
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
            'password' => ['required', 'min:8'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        auth()->login($user); // コメントアウトを外す

        // return redirect('mypage/posts');
    }
}
```

- `$ php artisan test --filter ユーザー登録できる`を実行  

```:terminal
   PASS  Tests\Feature\Http\Controllers\SignupControllerTest
  ✓ ユーザー登録できる

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
            ->assertRedirect('mypage/posts'); // 編集

        unset($validData['password']);

        $this->assertDatabaseHas('users', $validData);

        $user = User::firstWhere($validData);
        // $this->assertNotNull($user);

        $this->assertTrue(Hash::check('hogehoge', $user->password));

        $this->assertAuthenticatedAs($user);
    }

    /**
     * @test
     */
    function 不正なデータではユーザー登録できない()
    {
        // $this->withoutExceptionHandling(); // 古いバージョンの時は記述すると良い

        $url = 'signup';

        User::factory()->create(['email' => 'aaa@bbb.net']);

        // $this->get('signup');
        $this->from('signup')->post($url, [])
            ->assertRedirect('signup');

        // 注意点
        // (1) カスタムメッセージを設定している時は、そちらが優先されてしまう
        // (2) 入力エラーが出る前に言語ファイルを読もうとしている箇所がある時は、そちらもtestingに対応させる必要あり

        app()->setLocale('testing');

        dump(app()->getLocale());

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
        $this->post($url, ['password' => 'abc12345'])->assertValid(['password']);
    }

    /**
     * @test
     */
    function hogebar()
    {
        dump(app()->getLocale());

        $this->assertTrue(true);
    }
}
```

- `$ php artisan test --filter ユーザー登録できる`を実行  

```:terminal
  FAIL  Tests\Feature\Http\Controllers\SignupControllerTest
  ⨯ ユーザー登録できる

  ---

  • Tests\Feature\Http\Controllers\SignupControllerTest > ユーザー登録できる
  Expected response status code [201, 301, 302, 303, 307, 308] but received 200.
  Failed asserting that false is true.

  at tests/Feature/Http/Controllers/SignupControllerTest.php:42
     38▕         $validData = User::factory()->validData();
     39▕         // dd($validData);
     40▕ 
     41▕         $this->post('signup', $validData)
  ➜  42▕             ->assertRedirect('mypage/posts');
     43▕ 
     44▕         unset($validData['password']);
     45▕ 
     46▕         $this->assertDatabaseHas('users', $validData);


  Tests:  1 failed
  Time:   0.40s
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
            'password' => ['required', 'min:8'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        auth()->login($user);

        return redirect('mypage/posts'); // コメントアウトを外す
    }
}
```

- `$ php artisan test --filter ユーザー登録できる`を実行  

```:terminal
   PASS  Tests\Feature\Http\Controllers\SignupControllerTest
  ✓ ユーザー登録できる

  Tests:  1 passed
  Time:   0.39s
```

※ たまには全体のテスト  

- `$ vendor/bin/phpunit`を実行  

```:terminal
PHPUnit 9.6.13 by Sebastian Bergmann and contributors.

........"testing" // tests/Feature/Http/Controllers/SignupControllerTest.php:77
."ja" // tests/Feature/Http/Controllers/SignupControllerTest.php:99
.....                                                    14 / 14 (100%)

Time: 00:01.037, Memory: 46.50 MB

OK (14 tests, 55 assertions)
```
