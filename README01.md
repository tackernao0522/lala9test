# Section1: テストの基礎

`マニュアル`: <https://phpunit-unofficial.readthedocs.io/ja/latest/>  

## 7. ExampleTestを通して学ぶ、その1

`tests/Feature/ExampleTest.php`  

```php:ExampleTest.php
<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_the_application_returns_a_successful_response()
    {
        $response = $this->get('/'); // トップページにgetでアクセスして、レスポンスを得る

        $response->assertStatus(200); // レスポンスに対してステータスが200であるかを調べる
    }
}
```

`tests/Unit/ExampleTest.php`  

```php:ExampleTest.php
<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_that_true_is_true()
    {
        $this->assertTrue(true); // trueがtrueか？をアサートしてくださいという意味。
    }
}
```

- `$ php artisan test`を実行  

```:terminal
  PASS  Tests\Unit\ExampleTest
  ✓ that true is true

   PASS  Tests\Feature\ExampleTest
  ✓ the application returns a successful response

  Tests:  2 passed
```

### テストと認識させるには？

クラス名 ... XxxTest  

ファイル名 ... XxxTestphp(コマンドがある)  

メソッド名は、以下の2通りあります。  

1. メソッド名をtestから書く。(例 : test_hoge_bar)  
2. コメントブロックで、@testと書く。  

```php:ExampleTest.php
// 例
function test_テストです()
{
    
}
/** @test */
function テストです()
{
    
}
/**
* @test
*/
function テストです
{

}
```

メソッド名は、日本語も使用できます。  

また、テストメソッドは、publicである必要があります。(PHPでは、publicを省略すれば、public扱いになります。)  

`参考`: VsCode用 testスニペット  

```:json
 "test case": {
    "prefix": "test",
    "body": [
    "/** @test */",
    "function ${1:name}()",
    "{",
    "   $2",
    "}"
 ],
    "description": "test case"
 },
```

### ハンズオン

`tests/Feature/ExampleTest.php`  

```php:ExampleTest.php
<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_the_application_returns_a_successful_response()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    // 追加
    /**
     * @test
     */
    function これはテストです。()
    {
        $this->assertTrue(true);
    }
    // ここまで
}
```

- `$ php artisan test`を実行  

```:termainal
 PASS  Tests\Unit\ExampleTest
  ✓ that true is true

   PASS  Tests\Feature\ExampleTest
  ✓ the application returns a successful response
  ✓ これはテストです。

  Tests:  3 passed
  Time:   0.21s
```

## 8. ExampleTestを通して学ぶ、 その2

- 古いLaravelの場合のテストコマンド  

- `$ vendor/bin/phpinit`を実行する  

```:terminal
PHPUnit 9.6.13 by Sebastian Bergmann and contributors.

...

Time: 00:00.235, Memory: 22.00 MB

OK (3 tests, 3 assertions)
```

### ハンズオン

`tests/Feature/ExampleTest.php`  

```php:ExampleTest.php
<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_the_application_returns_a_successful_response()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    /**
     * @test
     */
    function これはテストです。()
    {
        $this->assertTrue(true);
        $this->assertTrue(true);
        $this->assertTrue(true);
    }
}
```

- `$ vendor/bin/phpunit`を実行  

```:terminal
PHPUnit 9.6.13 by Sebastian Bergmann and contributors.

...                                                                 3 / 3 (100%)

Time: 00:00.180, Memory: 22.00 MB

OK (3 tests, 5 assertions)
```

`tests/Feature/ExampleTest.php`  

```php:ExampleTest.php
<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_the_application_returns_a_successful_response()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    /**
     * @test
     */
    function これはテストです。()
    {
        $this->assertTrue(false); // 編集 ここで失敗するのでここで止まる
        $this->assertTrue(true);
        $this->assertTrue(true);
    }
}
```

- `$ vendor/bin/phpunit`を実行  

```:terminal
PHPUnit 9.6.13 by Sebastian Bergmann and contributors.

..F                                                                 3 / 3 (100%)

Time: 00:00.362, Memory: 22.00 MB

There was 1 failure:

1) Tests\Feature\ExampleTest::これはテストです。
Failed asserting that false is true.

/Applications/MAMP/htdocs/lara9test/tests/Feature/ExampleTest.php:27

FAILURES!
Tests: 3, Assertions: 3, Failures: 1.
```

(例) `$ php artisan make:test FootTest`とするとデフォルトでは`Feature`の方で作成される(試してみる)  
(例) `$ php artisan make:test FooTest --unit`をすると`Unit`の方に作成される(試してみる)  

## 9. ExampleTestを通して学ぶ、 その3

- <https://developer.mozilla.org/ja/docs/Web/HTTP/Status>  

### ステータスコード

200番台 : 成功  
300番台 : リダイレクト  
400番台 : クライアントエラー  
500番台 : サーバーエラー  

### 覚えておきたいステータスコード

200 OK (ページが問題なく表示された)  
302 Found (昔は、Moved Temporaily。レイダイレクト)  
403 Forbidden  
404 Not Found  
405 Method Not Allowed (そのURLがPOSTを受け付けていない -> web.phpに問題あり)  
422 Unprocessable Entity (validationエラー)  
500 Internal Server Error  

`tests/Feature/ExampleTest.php`  

```php:ExampleTest.php
<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_the_application_returns_a_successful_response()
    {
        // 準備 (今はまだない)

        // 実行
        $response = $this->get('/');

        // 検証
        $response->assertStatus(200);
    }

    /**
     * @test
     */
    function これはテストです。()
    {
        $this->assertTrue(false);
        $this->assertTrue(true);
        $this->assertTrue(true);
    }
}
```

`or`  

`tests/Feature/ExampleTest.php`  

```php:ExampleTest.php
<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_the_application_returns_a_successful_response()
    {
        // 準備

        // 実行 / 検証 (一行でまとめて書ける)
        $response = $this->get('/')
            ->assertStatus(200);
    }

    /**
     * @test
     */
    function これはテストです。()
    {
        $this->assertTrue(false);
        $this->assertTrue(true);
        $this->assertTrue(true);
    }
}
```
