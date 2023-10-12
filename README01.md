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

## 10. ExampleTestを通して学ぶ、その3

- 書き方の例1  

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
        $this->makeData();

        // 実行 / 検証
        $response = $this->get('/')
            ->assertStatus(200);
    }

    public function test_the_application_returns_a_successful_response2()
    {
        // 準備
        $this->makeData();

        // 実行 / 検証
        $response = $this->get('/')
            ->assertStatus(200);
    }

    protected function makeData()
    {
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

- 書き方の例2  

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
        $this->seed(XXXSeeder::class);

        // 実行 / 検証
        $response = $this->get('/')
            ->assertStatus(200);
    }

    public function test_the_application_returns_a_successful_response2()
    {
        // 準備
        $this->seed(XXXSeeder::class);

        // 実行 / 検証
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

- 書き方の例3  

`teste/Feature/ExampleTest.php`  

```php:ExampleTest.php
<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    public $user;

    protected function setUp(): void // 下記のメソッドたちが走る前に呼び出される
    {
        parent::setUp();

        $this->user = 'hoge';
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_the_application_returns_a_successful_response()
    {
        // 準備
        dump($this->user);

        // 実行 / 検証
        $response = $this->get('/')
            ->assertStatus(200);
    }

    public function test_the_application_returns_a_successful_response2()
    {
        // 準備
        dump($this->user);

        // 実行 / 検証
        $response = $this->get('/')
            ->assertStatus(200);
    }
}
```

`$ php artisan test`を実行してみる  

```:terminal
   PASS  Tests\Unit\ExampleTest
  ✓ that true is true

   PASS  Tests\Unit\FooTest
  ✓ example
"hoge" // tests/Feature/ExampleTest.php:27
"hoge" // tests/Feature/ExampleTest.php:37

   PASS  Tests\Feature\ExampleTest
  ✓ the application returns a successful response
  ✓ the application returns a successful response2

   PASS  Tests\Feature\FooTest
  ✓ example

  Tests:  5 passed
  Time:   0.30s
```

## 11. テストの実行方法

- `$ php artisan test`  (すべてのテストを実行)  
- `$ php artisan test tests/Feature/ExampleTest.php` (ファイル指定)  
- `$ php artisan test tests/Feature` (フォルダ指定)  
- `$ php artisan test --filter クラス名やメソッド名`  (部分一致)  
- `$ php artisan test --testsuite スイート名`  (Feature/Unit)  

## 12. テストを早く読み出す方法の検討、その1

VsCodeプラグイン `Better PHPUnit`をインストールしてみる  

## 15. テストで使うDBについて

- `テストで利用するDBは、ブラウザで確認する際のDBとは必ず分けます。`  
- `テストファイルでは、DBを利用する際は、トレイト RefreshDatabase; を読み込むこと`  

`RefreshDatabase を使用する場合、DBに応じて以下のように内部処理が分かれます。`  

**MySQLの場合**  

マイグレーションは、最初の一回のみ行われ、後は、テスト毎ごとに rollbackします(トランザクションを張る)。  

**SQLite インメモリの場合**  

テスト毎ごとにマイグレーションを実行します。  

#### 利点や欠点  

MySQLの場合・・・　　

[欠点]  

- 一般的に、SQLiteインメモリより遅いと言われる。  
- 固定の連番が使えない。(rollbackはするが、autoincrementはリセットされない為)  
- DBのtruncate() は、使えない。  

[利点]  

- 本番と同様のDBを使う為、DB間による差違が無い。  
- MySQL 特有の構文を使用しても問題ない。  

スピードに関しては、確かにテスト数やマイグレーションファイル数が多くない時は、SQLite インメモリの方が速いですが、  
共に多くなる時は、この限りではありません。  

`.envにDBの設定をする`  

### ハンズオン

`phpunit.xml`を編集する  

```xml:phpunit.xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="./vendor/phpunit/phpunit/phpunit.xsd"
         bootstrap="vendor/autoload.php"
         colors="true"
>
    <testsuites>
        <testsuite name="Unit">
            <directory suffix="Test.php">./tests/Unit</directory>
        </testsuite>
        <testsuite name="Feature">
            <directory suffix="Test.php">./tests/Feature</directory>
        </testsuite>
    </testsuites>
    <coverage processUncoveredFiles="true">
        <include>
            <directory suffix=".php">./app</directory>
        </include>
    </coverage>
    <php>
        <env name="APP_ENV" value="testing"/>
        <env name="BCRYPT_ROUNDS" value="4"/>
        <env name="CACHE_DRIVER" value="array"/>

        <!-- 追加 -->
        <env name="DB_CONNECTION" value="mysql"/>
        <env name="DB_DATABASE" value="testing"/>
        <!-- ここまで -->

        <!-- <env name="DB_CONNECTION" value="sqlite"/> -->
        <!-- <env name="DB_DATABASE" value=":memory:"/> -->
        <env name="MAIL_MAILER" value="array"/>
        <env name="QUEUE_CONNECTION" value="sync"/>
        <env name="SESSION_DRIVER" value="array"/>
        <env name="TELESCOPE_ENABLED" value="false"/>
    </php>
</phpunit>
```

`.env`を編集及びDATABASE作成 `.env.example`を参考  

`tests/Feature/ExampleTest.php`を編集  

```php:ExampleTest.php
<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Models\User;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase; // 必ず追加

    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_the_application_returns_a_successful_response()
    {
        User::factory()->create(); // 編集

        // 実行 / 検証
        $response = $this->get('/')
            ->assertStatus(200);
    }

    public function test_the_application_returns_a_successful_response2()
    {
        User::factory()->create(); // 編集

        // 実行 / 検証
        $response = $this->get('/')
            ->assertStatus(200);
    }
}
```

- `$ php artisan test --filter ExampleTest`を実行  


```:terminal
 PASS  Tests\Unit\ExampleTest
  ✓ that true is true

   PASS  Tests\Feature\ExampleTest
  ✓ the application returns a successful response
  ✓ the application returns a successful response2

  Tests:  3 passed
  Time:   0.45s


groovy@groovy-no-MBP lara9test % php artisan test --filter ExampleTest

   PASS  Tests\Unit\ExampleTest
  ✓ that true is true

   PASS  Tests\Feature\ExampleTest
  ✓ the application returns a successful response
  ✓ the application returns a successful response2

  Tests:  3 passed
  Time:   0.43s
```

`tests/Feature/ExampleTest.php`を編集  

```php:ExampleTest.php
<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_the_application_returns_a_successful_response()
    {
        // 編集
        $user = User::factory()->create();

        dump($user->id); // id が 1
        // ここまで

        // 実行 / 検証
        $response = $this->get('/')
            ->assertStatus(200);
    }

    public function test_the_application_returns_a_successful_response2()
    {
        // 編集
        $user = User::factory()->create();

        dump($user->id); // id が 2
        // ここまで

        // 実行 / 検証
        $response = $this->get('/')
            ->assertStatus(200);
    }
}
```

- `$ php artisan test --filter ExampleTest`を実行  

```:terminal
   PASS  Tests\Unit\ExampleTest
  ✓ that true is true
1 // tests/Feature/ExampleTest.php:24 (id　が 1)
2 // tests/Feature/ExampleTest.php:35 (id が 2)

   PASS  Tests\Feature\ExampleTest
  ✓ the application returns a successful response
  ✓ the application returns a successful response2

  Tests:  3 passed
  Time:   0.44s
```

**よって、MySQLでのテストの場合は id が元に戻っていないことがわかる(マイグレーションが最初のテストだけ走っていることになる)**  

#### SqLiteのインメモリでのテストの場合  

`phpunit.xml`を編集  

```xml:phpunit.xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="./vendor/phpunit/phpunit/phpunit.xsd"
         bootstrap="vendor/autoload.php"
         colors="true"
>
    <testsuites>
        <testsuite name="Unit">
            <directory suffix="Test.php">./tests/Unit</directory>
        </testsuite>
        <testsuite name="Feature">
            <directory suffix="Test.php">./tests/Feature</directory>
        </testsuite>
    </testsuites>
    <coverage processUncoveredFiles="true">
        <include>
            <directory suffix=".php">./app</directory>
        </include>
    </coverage>
    <php>
        <env name="APP_ENV" value="testing"/>
        <env name="BCRYPT_ROUNDS" value="4"/>
        <env name="CACHE_DRIVER" value="array"/>
        <!-- 編集 -->
        <!-- <env name="DB_CONNECTION" value="mysql"/>
        <env name="DB_DATABASE" value="testing"/> -->
        <env name="DB_CONNECTION" value="sqlite"/>
        <env name="DB_DATABASE" value=":memory:"/>
        <!-- ここまで -->
        <env name="MAIL_MAILER" value="array"/>
        <env name="QUEUE_CONNECTION" value="sync"/>
        <env name="SESSION_DRIVER" value="array"/>
        <env name="TELESCOPE_ENABLED" value="false"/>
    </php>
</phpunit>
```

- `$ php artisan test --filter ExampleTest`を実行  

```:terminal
   PASS  Tests\Unit\ExampleTest
  ✓ that true is true
1 // tests/Feature/ExampleTest.php:24 (id が 1)
1 // tests/Feature/ExampleTest.php:35 (id が 1)

   PASS  Tests\Feature\ExampleTest
  ✓ the application returns a successful response
  ✓ the application returns a successful response2

  Tests:  3 passed
  Time:   0.32s
  ```

**両方とも id が 1 となっており、テスト毎ごとにマイグレーションが走っていることがわかる**  (多少MySQLより処理が速い)  
