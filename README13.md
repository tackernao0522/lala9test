# Section2

## 43. ログイン処理 (ログインできる)

`tests/Feauture/Http/Controllers/Mypage/UserLoginControllerTest.php`を編集  

```php:UserLoginControllerTest.php
<?php

namespace Tests\Feature\Http\Controllers\Mypage;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
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
            ->assertRedirect($url);

        app()->setlocale('testing');

        $this->post($url, ['email' => ''])->assertInvalid(['email' => 'required']);
        $this->post($url, ['email' => 'aa@bb@cc'])->assertInvalid(['email' => 'email']);
        $this->post($url, ['email' => 'aa@ああ.いい'])->assertInvalid(['email' => 'email']);
        $this->post($url, ['password' => ''])->assertInvalid(['password' => 'required']);
    }

    // 追加
    /**
     * @test
     */
    function ログインできる()
    {
        $user = User::factory()->create([
            'email' => 'aaa@bbb.net',
            'password' => Hash::make('abcd1234'),
        ]);

        $this->post('mypage/login', [
            'email' => 'aaa@bbb.net',
            'password' => 'abcd1234',
        ])->assertRedirect('mypage/posts');

        $this->assertAuthenticatedAs($user);
    }
    // ここまで
}
```

- `$ php artisan test --filter ログインできる`を実行  

```:terminal
 FAIL  Tests\Feature\Http\Controllers\Mypage\UserLoginControllerTest
  ⨯ ログインできる

  ---

  • Tests\Feature\Http\Controllers\Mypage\UserLoginControllerTest > ログインできる
  Expected response status code [201, 301, 302, 303, 307, 308] but received 200.
  Failed asserting that false is true.

  at tests/Feature/Http/Controllers/Mypage/UserLoginControllerTest.php:53
     49▕ 
     50▕         $this->post('mypage/login', [
     51▕             'email' => 'aaa@bbb.net',
     52▕             'password' => 'abcd1234',
  ➜  53▕         ])->assertRedirect('mypage/posts');
     54▕ 
     55▕         $this->assertAuthenticatedAs($user);
     56▕     }
     57▕ }


  Tests:  1 failed
  Time:   0.29s
```

`app/Http/Controllers/Mypage/UserLoginController.php`を編集  

```php:UserLoginController.php
<?php

namespace App\Http\Controllers\Mypage;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserLoginController extends Controller
{
    public function index()
    {
        return view('mypage.login');
    }

    public function login(Request $requst)
    {
        $credentials = $requst->validate([
            'email' => ['required', 'email:filter'],
            'password' => ['required'],
        ]);

        // 追加
        if (Auth::attempt($credentials)) {
            $requst->session()->regenerate();

            return redirect('mypage/posts');
        }
        // ここまで
    }
}
```

- `$ php artisan test --filter ログインできる`を実行  

```:terminal
  PASS  Tests\Feature\Http\Controllers\Mypage\UserLoginControllerTest
  ✓ ログインできる

  Tests:  1 passed
  Time:   0.29s
```

## 44. ログイン処理 (ログインできない)

`app/Http/Controllers/Mypage/UserLoginController.php`を編集  

```php:UserLoginController.php
<?php

namespace App\Http\Controllers\Mypage;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserLoginController extends Controller
{
    public function index()
    {
        return view('mypage.login');
    }

    public function login(Request $requst)
    {
        $credentials = $requst->validate([
            'email' => ['required', 'email:filter'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $requst->session()->regenerate();

            return redirect('mypage/posts');
        }

        // 追加してコメントアウトしておく
        // return back()->withErrors([
        //     'email' => 'メールアドレスかパスワードが間違っています。',
        // ])->withInput();
    }
}
```

`tests/Feature/Http/Controllers/Mypage/UserLoginControllerTest.php`を編集  

```php:UserLoginControllerTest.php
<?php

namespace Tests\Feature\Http\Controllers\Mypage;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
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
            ->assertRedirect($url);

        app()->setlocale('testing');

        $this->post($url, ['email' => ''])->assertInvalid(['email' => 'required']);
        $this->post($url, ['email' => 'aa@bb@cc'])->assertInvalid(['email' => 'email']);
        $this->post($url, ['email' => 'aa@ああ.いい'])->assertInvalid(['email' => 'email']);
        $this->post($url, ['password' => ''])->assertInvalid(['password' => 'required']);
    }

    /**
     * @test
     */
    function ログインできる()
    {
        $user = User::factory()->create([
            'email' => 'aaa@bbb.net',
            'password' => Hash::make('abcd1234'),
        ]);

        $this->post('mypage/login', [
            'email' => 'aaa@bbb.net',
            'password' => 'abcd1234',
        ])->assertRedirect('mypage/posts');

        $this->assertAuthenticatedAs($user);
    }

    // 追加
    /**
     * @test
     */
    function パスワードを間違えているのでログインできず、適切なエラ〜メッセージが表示される()
    {
        $url = 'mypage/login';

        $user = User::factory()->create([
            'email' => 'aaa@bbb.net',
            'password' => Hash::make('abcd1234'),
        ]);

        $this->from($url)->post('mypage/login', [
            'email' => 'aaa@bbb.net',
            'password' => '11112222',
        ])->assertRedirect($url);
    }
    // ここまで
}
```

- `$ php artisan test --filter パスワードを間違えているのでログインできず、適切なエラーメッセージが表示される`を実行  

```:terminal
   FAIL  Tests\Feature\Http\Controllers\Mypage\UserLoginControllerTest
  ⨯ パスワードを間違えているのでログインできず、適切なエラーメッセージが表示される

  ---

  • Tests\Feature\Http\Controllers\Mypage\UserLoginControllerTest > パスワードを間違えているのでログインできず、適切なエラーメッセージが表示される
  Expected response status code [201, 301, 302, 303, 307, 308] but received 200.
  Failed asserting that false is true.

  at tests/Feature/Http/Controllers/Mypage/UserLoginControllerTest.php:73
     69▕ 
     70▕         $this->from($url)->post('mypage/login', [
     71▕             'email' => 'aaa@bbb.net',
     72▕             'password' => '11112222',
  ➜  73▕         ])->assertRedirect($url);
     74▕     }
     75▕ }
     76▕ 


  Tests:  1 failed
  Time:   0.56s
```

`UserLoginController.php`で追加したコードのコメントアウトを解除する  

- `$ php artisan test --filter パスワードを間違えているのでログインできず、適切なエラーメッセージが表示される`を実行  

```:terminal
  PASS  Tests\Feature\Http\Controllers\Mypage\UserLoginControllerTest
  ✓ パスワードを間違えているのでログインできず、適切なエラーメッセージが表示される

  Tests:  1 passed
  Time:   0.52s
```

`tests/Feature/Http/Controllers/Mypage/UserLoginControllerTest.php`を編集  

```php:UserLoginControllerTest.php
<?php

namespace Tests\Feature\Http\Controllers\Mypage;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
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
            ->assertRedirect($url);

        app()->setlocale('testing');

        $this->post($url, ['email' => ''])->assertInvalid(['email' => 'required']);
        $this->post($url, ['email' => 'aa@bb@cc'])->assertInvalid(['email' => 'email']);
        $this->post($url, ['email' => 'aa@ああ.いい'])->assertInvalid(['email' => 'email']);
        $this->post($url, ['password' => ''])->assertInvalid(['password' => 'required']);
    }

    /**
     * @test
     */
    function ログインできる()
    {
        $user = User::factory()->create([
            'email' => 'aaa@bbb.net',
            'password' => Hash::make('abcd1234'),
        ]);

        $this->post('mypage/login', [
            'email' => 'aaa@bbb.net',
            'password' => 'abcd1234',
        ])->assertRedirect('mypage/posts');

        $this->assertAuthenticatedAs($user);
    }

    /**
     * @test
     */
    function パスワードを間違えているのでログインできず、適切なエラーメッセージが表示される()
    {
        $url = 'mypage/login';

        $user = User::factory()->create([
            'email' => 'aaa@bbb.net',
            'password' => Hash::make('abcd1234'),
        ]);

        $this->from($url)->post('mypage/login', [
            'email' => 'aaa@bbb.net',
            'password' => '11112222',
        ])->assertRedirect($url);

        // 追加
        $this->get($url)
            ->assertOk()
            ->assertSee('xxx');
        // ここまで
    }
}
```

- `$ php artisan test --filter パスワードを間違えているのでログインできず、適切なエラーメッセージが表示される`を実行  

```:terminal
   FAIL  Tests\Feature\Http\Controllers\Mypage\UserLoginControllerTest
  ⨯ パスワードを間違えているのでログインできず、適切なエラーメッセージが表示される

  ---

  • Tests\Feature\Http\Controllers\Mypage\UserLoginControllerTest > パスワードを間違えているのでログインできず、適切なエラーメッセージが表示される
  Failed asserting that '<!DOCTYPE html>\n
  <html lang="ja">\n
  \n
  <head>\n
      <meta charset="UTF-8">\n
      <title>ブログ</title>\n
      <link type="text/css" rel="stylesheet" href="/css/style.css">\n
  </head>\n
  \n
  <body>\n
          <h1>ログイン画面</h1>\n
  \n
      <form method="POST">\n
          <input type="hidden" name="_token" value="Snmy3OqhdrDYTTuC7mlLZ2qusjGdYY3WQk2EfmI1">\n
          <ul class="error">\n
                      <li>メールアドレスかパスワードが間違っています。</li>\n
              </ul>\n
          \n
          メールアドレス : <input type="text" name="email" value="aaa@bbb.net">\n
          <br>\n
          パスワード : <input type="password" name="password">\n
          <br><br>\n
          <input type="submit" value="送信する">\n
      </form>\n
  \n
      <p style="margin-top: 30px;">\n
          <a href="/signup">新規ユーザー登録</a>\n
      </p>\n
  </body>\n
  \n
  </html>\n
  ' contains "xxx".

  at tests/Feature/Http/Controllers/Mypage/UserLoginControllerTest.php:77
     73▕         ])->assertRedirect($url);
     74▕ 
     75▕         $this->get($url)
     76▕             ->assertOk()
  ➜  77▕             ->assertSee('xxx');
     78▕     }
     79▕ }
     80▕ 


  Tests:  1 failed
  Time:   0.55s
```

`tests/Feature/Http/Controllers/Mypage/UserLoginControllerTest.php`を編集  

```php:UserLoginControllerTest.php
<?php

namespace Tests\Feature\Http\Controllers\Mypage;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
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
            ->assertRedirect($url);

        app()->setlocale('testing');

        $this->post($url, ['email' => ''])->assertInvalid(['email' => 'required']);
        $this->post($url, ['email' => 'aa@bb@cc'])->assertInvalid(['email' => 'email']);
        $this->post($url, ['email' => 'aa@ああ.いい'])->assertInvalid(['email' => 'email']);
        $this->post($url, ['password' => ''])->assertInvalid(['password' => 'required']);
    }

    /**
     * @test
     */
    function ログインできる()
    {
        $user = User::factory()->create([
            'email' => 'aaa@bbb.net',
            'password' => Hash::make('abcd1234'),
        ]);

        $this->post('mypage/login', [
            'email' => 'aaa@bbb.net',
            'password' => 'abcd1234',
        ])->assertRedirect('mypage/posts');

        $this->assertAuthenticatedAs($user);
    }

    /**
     * @test
     */
    function パスワードを間違えているのでログインできず、適切なエラーメッセージが表示される()
    {
        $url = 'mypage/login';

        $user = User::factory()->create([
            'email' => 'aaa@bbb.net',
            'password' => Hash::make('abcd1234'),
        ]);

        $this->from($url)->post('mypage/login', [
            'email' => 'aaa@bbb.net',
            'password' => '11112222',
        ])->assertRedirect($url);

        $this->get($url)
            ->assertOk()
            ->assertSee('メールアドレスかパスワードが間違っています。'); // 編集
    }
}
```

- `$ php artisan test --filter パスワードを間違えているのでログインできず、適切なエラーメッセージが表示される`を実行  

```:terminal
   PASS  Tests\Feature\Http\Controllers\Mypage\UserLoginControllerTest
  ✓ パスワードを間違えているのでログインできず、適切なエラーメッセージが表示される

  Tests:  1 passed
  Time:   0.51s
```

`別の書き方`  

`UserLoginControllerTest.php`  

```php:UserLoginControllerTest.php
<?php

namespace Tests\Feature\Http\Controllers\Mypage;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
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
            ->assertRedirect($url);

        app()->setlocale('testing');

        $this->post($url, ['email' => ''])->assertInvalid(['email' => 'required']);
        $this->post($url, ['email' => 'aa@bb@cc'])->assertInvalid(['email' => 'email']);
        $this->post($url, ['email' => 'aa@ああ.いい'])->assertInvalid(['email' => 'email']);
        $this->post($url, ['password' => ''])->assertInvalid(['password' => 'required']);
    }

    /**
     * @test
     */
    function ログインできる()
    {
        $user = User::factory()->create([
            'email' => 'aaa@bbb.net',
            'password' => Hash::make('abcd1234'),
        ]);

        $this->post('mypage/login', [
            'email' => 'aaa@bbb.net',
            'password' => 'abcd1234',
        ])->assertRedirect('mypage/posts');

        $this->assertAuthenticatedAs($user);
    }

    /**
     * @test
     */
    function パスワードを間違えているのでログインできず、適切なエラーメッセージが表示される()
    {
        $url = 'mypage/login';

        $user = User::factory()->create([
            'email' => 'aaa@bbb.net',
            'password' => Hash::make('abcd1234'),
        ]);

        // $this->from($url)->post('mypage/login', [
        //     'email' => 'aaa@bbb.net',
        //     'password' => '11112222',
        // ])->assertRedirect($url);

        // $this->get($url)
        //     ->assertOk()
        //     ->assertSee('メールアドレスかパスワードが間違っています。');

        // ただこの書き方だとリダイレクト先のURLが'mypage/login'なのかわからない
        // $this->from($url)->followingRedirects()->post($url, [
        //     'email' => 'aaa@bbb.net',
        //     'password' => '11112222',
        // ])
        //     ->assertOk()
        //     ->assertSee('メールアドレスかパスワードが間違っています。');

        // この書き方だとリダイレクト先を明示できる
        $this->from($url)->followingRedirects()->post($url, [
            'email' => 'aaa@bbb.net',
            'password' => '11112222',
        ])
            ->assertOk()
            ->assertSee('メールアドレスかパスワードが間違っています。')
            ->assertSee('<h1>ログイン画面</h1>', false); // falseを第二引数に入れないと<h1></h1>がエスケープされてエラーになってしまう
    }
}
```

- `$ php artisan test --filter パスワードを間違えているのでログインできず、適切なエラーメッセージが表示される`を実行  

```:terminal
  PASS  Tests\Feature\Http\Controllers\Mypage\UserLoginControllerTest
  ✓ パスワードを間違えているのでログインできず、適切なエラーメッセージが表示される

  Tests:  1 passed
  Time:   0.50s
```
