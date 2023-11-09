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
