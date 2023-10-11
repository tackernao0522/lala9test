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
