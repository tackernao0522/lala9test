# Section2: テストをしながらブログ作成

- `tests/Feature/ExampleTest.php`と`tests/Unit/ExampleTest.php`を削除する  
- `FooTest.php`も削除する  

- `phpunit.xml`は`sqlite`の`:memory:`を使用する  

`config/app.php`を編集  

```php:app.php
<?php

use Illuminate\Support\Facades\Facade;

return [

    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application. This value is used when the
    | framework needs to place the application's name in a notification or
    | any other location as required by the application or its packages.
    |
    */

    'name' => env('APP_NAME', 'Laravel'),

    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    |
    | This value determines the "environment" your application is currently
    | running in. This may determine how you prefer to configure various
    | services the application utilizes. Set this in your ".env" file.
    |
    */

    'env' => env('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    |
    | When your application is in debug mode, detailed error messages with
    | stack traces will be shown on every error that occurs within your
    | application. If disabled, a simple generic error page is shown.
    |
    */

    'debug' => (bool) env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | This URL is used by the console to properly generate URLs when using
    | the Artisan command line tool. You should set this to the root of
    | your application so that it is used when running Artisan tasks.
    |
    */

    'url' => env('APP_URL', 'http://localhost'),

    'asset_url' => env('ASSET_URL'),

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which
    | will be used by the PHP date and date-time functions. We have gone
    | ahead and set this to a sensible default for you out of the box.
    |
    */

    'timezone' => 'Asia/Tokyo', // 編集

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by the translation service provider. You are free to set this value
    | to any of the locales which will be supported by the application.
    |
    */

    'locale' => 'ja', // 編集

    /*
    |--------------------------------------------------------------------------
    | Application Fallback Locale
    |--------------------------------------------------------------------------
    |
    | The fallback locale determines the locale to use when the current one
    | is not available. You may change the value to correspond to any of
    | the language folders that are provided through your application.
    |
    */

    'fallback_locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Faker Locale
    |--------------------------------------------------------------------------
    |
    | This locale will be used by the Faker PHP library when generating fake
    | data for your database seeds. For example, this will be used to get
    | localized telephone numbers, street address information and more.
    |
    */

    'faker_locale' => 'ja_JP', // 編集

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | This key is used by the Illuminate encrypter service and should be set
    | to a random, 32 character string, otherwise these encrypted strings
    | will not be safe. Please do this before deploying an application!
    |
    */

    'key' => env('APP_KEY'),

    'cipher' => 'AES-256-CBC',

    /*
    |--------------------------------------------------------------------------
    | Maintenance Mode Driver
    |--------------------------------------------------------------------------
    |
    | These configuration options determine the driver used to determine and
    | manage Laravel's "maintenance mode" status. The "cache" driver will
    | allow maintenance mode to be controlled across multiple machines.
    |
    | Supported drivers: "file", "cache"
    |
    */

    'maintenance' => [
        'driver' => 'file',
        // 'store'  => 'redis',
    ],

    /*
    |--------------------------------------------------------------------------
    | Autoloaded Service Providers
    |--------------------------------------------------------------------------
    |
    | The service providers listed here will be automatically loaded on the
    | request to your application. Feel free to add your own services to
    | this array to grant expanded functionality to your applications.
    |
    */

    'providers' => [

        /*
         * Laravel Framework Service Providers...
         */
        Illuminate\Auth\AuthServiceProvider::class,
        Illuminate\Broadcasting\BroadcastServiceProvider::class,
        Illuminate\Bus\BusServiceProvider::class,
        Illuminate\Cache\CacheServiceProvider::class,
        Illuminate\Foundation\Providers\ConsoleSupportServiceProvider::class,
        Illuminate\Cookie\CookieServiceProvider::class,
        Illuminate\Database\DatabaseServiceProvider::class,
        Illuminate\Encryption\EncryptionServiceProvider::class,
        Illuminate\Filesystem\FilesystemServiceProvider::class,
        Illuminate\Foundation\Providers\FoundationServiceProvider::class,
        Illuminate\Hashing\HashServiceProvider::class,
        Illuminate\Mail\MailServiceProvider::class,
        Illuminate\Notifications\NotificationServiceProvider::class,
        Illuminate\Pagination\PaginationServiceProvider::class,
        Illuminate\Pipeline\PipelineServiceProvider::class,
        Illuminate\Queue\QueueServiceProvider::class,
        Illuminate\Redis\RedisServiceProvider::class,
        Illuminate\Auth\Passwords\PasswordResetServiceProvider::class,
        Illuminate\Session\SessionServiceProvider::class,
        Illuminate\Translation\TranslationServiceProvider::class,
        Illuminate\Validation\ValidationServiceProvider::class,
        Illuminate\View\ViewServiceProvider::class,

        /*
         * Package Service Providers...
         */

        /*
         * Application Service Providers...
         */
        App\Providers\AppServiceProvider::class,
        App\Providers\AuthServiceProvider::class,
        // App\Providers\BroadcastServiceProvider::class,
        App\Providers\EventServiceProvider::class,
        App\Providers\RouteServiceProvider::class,

    ],

    /*
    |--------------------------------------------------------------------------
    | Class Aliases
    |--------------------------------------------------------------------------
    |
    | This array of class aliases will be registered when this application
    | is started. However, feel free to register as many as you wish as
    | the aliases are "lazy" loaded so they don't hinder performance.
    |
    */

    'aliases' => Facade::defaultAliases()->merge([
        // 'ExampleClass' => App\Example\ExampleClass::class,
    ])->toArray(),

];
```

- `$ mkdir lang/ja && touch $_/{auth.php,pagination.php,passwords.php,validation.php}`を実行  

`lang/ja/auth.php`を編集  

```php:auth.php
<?php

return [

    /*
    |--------------------------------------------------------------------------
    | 認証言語行
    |--------------------------------------------------------------------------
    |
    | 以下の言語行は認証時にユーザーに対し表示する必要のある
    | 様々なメッセージです。アプリケーションの必要に合わせ
    | 自由にこれらの言語行を変更してください。
    |
    */

    'failed' => 'ログイン情報が登録されていません。',
    'throttle' => 'ログインに続けて失敗しています。:seconds秒後に再度お試しください。',

];
```

- `lang/ja/pagination.php`を編集  

```php:pagination.php
<?php

return [

    /*
    |--------------------------------------------------------------------------
    | ペジネーション言語行
    |--------------------------------------------------------------------------
    |
    | 以下の言語行はペジネーターライブラリーによりシンプルなペジネーション
    | リンクを生成するために使用されます。アプリケーションに合うように、
    | 自由に変更してください。
    |
    */

    'previous' => '&laquo; 前',
    'next' => '次 &raquo;',

];
```

- `lang/ja/passwords.php`を編集  

```php:passwords.php
<?php

return [

    /*
    |--------------------------------------------------------------------------
    | パスワードリセット言語行
    |--------------------------------------------------------------------------
    |
    | 以下の言語行は既存のパスワードを無効にしたい場合に、無効なトークンや
    | 新しいパスワードが入力された場合のように、パスワードの更新に失敗した
    | 理由を示すデフォルトの文言です。
    |
    */

    'reset' => 'パスワードをリセットしました。',
    'sent' => 'パスワードリセットメールを送信しました。',
    'throttled' => 'しばらく再試行はお待ちください。',
    'token' => 'このパスワードリセットトークンは無効です。',
    'user' => "メールアドレスに一致するユーザーは存在していません。",

];
```

- `lang/ja/validation.php`を編集  

```php:validation.php
<?php

return [

    /*
    |--------------------------------------------------------------------------
    | バリデーション言語行
    |--------------------------------------------------------------------------
    |
    | 以下の言語行はバリデタークラスにより使用されるデフォルトのエラー
    | メッセージです。サイズルールのようにいくつかのバリデーションを
    | 持っているものもあります。メッセージはご自由に調整してください。
    |
    */

    'accepted'             => ':attributeを承認してください。',
    'accepted_if' => ':otherが:valueの場合、:attributeを承認してください。',
    'active_url'           => ':attributeが有効なURLではありません。',
    'after'                => ':attributeには、:dateより後の日付を指定してください。',
    'after_or_equal'       => ':attributeには、:date以降の日付を指定してください。',
    'alpha'                => ':attributeはアルファベットのみがご利用できます。',
    'alpha_dash'           => ':attributeはアルファベットとダッシュ(-)及び下線(_)がご利用できます。',
    'alpha_num'            => ':attributeはアルファベット数字がご利用できます。',
    'array'                => ':attributeは配列でなくてはなりません。',
    'before'               => ':attributeには、:dateより前の日付をご利用ください。',
    'before_or_equal'      => ':attributeには、:date以前の日付をご利用ください。',
    'between'              => [
        'numeric' => ':attributeは、:minから:maxの間で指定してください。',
        'file'    => ':attributeは、:min kBから、:max kBの間で指定してください。',
        'string'  => ':attributeは、:min文字から、:max文字の間で指定してください。',
        'array'   => ':attributeは、:min個から:max個の間で指定してください。',
    ],
    'boolean'              => ':attributeは、trueかfalseを指定してください。',
    'confirmed'            => ':attributeと、確認フィールドとが、一致していません。',
    'current_password'     => 'パスワードが正しくありません。',
    'date'                 => ':attributeには有効な日付を指定してください。',
    'date_equals'          => ':attributeには、:dateと同じ日付けを指定してください。',
    'date_format'          => ':attributeは:format形式で指定してください。',
    'different'            => ':attributeと:otherには、異なった内容を指定してください。',
    'digits'               => ':attributeは:digits桁で指定してください。',
    'digits_between'       => ':attributeは:min桁から:max桁の間で指定してください。',
    'dimensions'           => ':attributeの図形サイズが正しくありません。',
    'distinct'             => ':attributeには異なった値を指定してください。',
    'email'                => ':attributeには、有効なメールアドレスを指定してください。',
    'ends_with'            => ':attributeには、:valuesのどれかで終わる値を指定してください。',
    'exists'               => '選択された:attributeは正しくありません。',
    'file'                 => ':attributeにはファイルを指定してください。',
    'filled'               => ':attributeに値を指定してください。',
    'gt'                   => [
        'numeric' => ':attributeには、:valueより大きな値を指定してください。',
        'file'    => ':attributeには、:value kBより大きなファイルを指定してください。',
        'string'  => ':attributeは、:value文字より長く指定してください。',
        'array'   => ':attributeには、:value個より多くのアイテムを指定してください。',
    ],
    'gte'                  => [
        'numeric' => ':attributeには、:value以上の値を指定してください。',
        'file'    => ':attributeには、:value kB以上のファイルを指定してください。',
        'string'  => ':attributeは、:value文字以上で指定してください。',
        'array'   => ':attributeには、:value個以上のアイテムを指定してください。',
    ],
    'image'                => ':attributeには画像ファイルを指定してください。',
    'in'                   => '選択された:attributeは正しくありません。',
    'in_array'             => ':attributeには:otherの値を指定してください。',
    'integer'              => ':attributeは整数で指定してください。',
    'ip'                   => ':attributeには、有効なIPアドレスを指定してください。',
    'ipv4'                 => ':attributeには、有効なIPv4アドレスを指定してください。',
    'ipv6'                 => ':attributeには、有効なIPv6アドレスを指定してください。',
    'json'                 => ':attributeには、有効なJSON文字列を指定してください。',
    'lt'                   => [
        'numeric' => ':attributeには、:valueより小さな値を指定してください。',
        'file'    => ':attributeには、:value kBより小さなファイルを指定してください。',
        'string'  => ':attributeは、:value文字より短く指定してください。',
        'array'   => ':attributeには、:value個より少ないアイテムを指定してください。',
    ],
    'lte'                  => [
        'numeric' => ':attributeには、:value以下の値を指定してください。',
        'file'    => ':attributeには、:value kB以下のファイルを指定してください。',
        'string'  => ':attributeは、:value文字以下で指定してください。',
        'array'   => ':attributeには、:value個以下のアイテムを指定してください。',
    ],
    'max'                  => [
        'numeric' => ':attributeには、:max以下の数字を指定してください。',
        'file'    => ':attributeには、:max kB以下のファイルを指定してください。',
        'string'  => ':attributeは、:max文字以下で指定してください。',
        'array'   => ':attributeは:max個以下指定してください。',
    ],
    'mimes'                => ':attributeには:valuesタイプのファイルを指定してください。',
    'mimetypes'            => ':attributeには:valuesタイプのファイルを指定してください。',
    'min'                  => [
        'numeric' => ':attributeには、:min以上の数字を指定してください。',
        'file'    => ':attributeには、:min kB以上のファイルを指定してください。',
        'string'  => ':attributeは、:min文字以上で指定してください。',
        'array'   => ':attributeは:min個以上指定してください。',
    ],
    'multiple_of' => ':attributeには、:valueの倍数を指定してください。',
    'not_in'               => '選択された:attributeは正しくありません。',
    'not_regex'            => ':attributeの形式が正しくありません。',
    'numeric'              => ':attributeには、数字を指定してください。',
    'password'             => '正しいパスワードを指定してください。',
    'present'              => ':attributeが存在していません。',
    'regex'                => ':attributeに正しい形式を指定してください。',
    'required'             => ':attributeは必ず指定してください。',
    'required_if'          => ':otherが:valueの場合、:attributeも指定してください。',
    'required_unless'      => ':otherが:valuesでない場合、:attributeを指定してください。',
    'required_with'        => ':valuesを指定する場合は、:attributeも指定してください。',
    'required_with_all'    => ':valuesを指定する場合は、:attributeも指定してください。',
    'required_without'     => ':valuesを指定しない場合は、:attributeを指定してください。',
    'required_without_all' => ':valuesのどれも指定しない場合は、:attributeを指定してください。',
    'prohibited'           => ':attributeは入力禁止です。',
    'prohibited_if' => ':otherが:valueの場合、:attributeは入力禁止です。',
    'prohibited_unless'    => ':otherが:valueでない場合、:attributeは入力禁止です。',
    'prohibits'            => 'attributeは:otherの入力を禁じています。',
    'same'                 => ':attributeと:otherには同じ値を指定してください。',
    'size'                 => [
        'numeric' => ':attributeは:sizeを指定してください。',
        'file'    => ':attributeのファイルは、:sizeキロバイトでなくてはなりません。',
        'string'  => ':attributeは:size文字で指定してください。',
        'array'   => ':attributeは:size個指定してください。',
    ],
    'starts_with'          => ':attributeには、:valuesのどれかで始まる値を指定してください。',
    'string'               => ':attributeは文字列を指定してください。',
    'timezone'             => ':attributeには、有効なゾーンを指定してください。',
    'unique'               => ':attributeの値は既に存在しています。',
    'uploaded'             => ':attributeのアップロードに失敗しました。',
    'url'                  => ':attributeに正しい形式を指定してください。',
    'uuid'                 => ':attributeに有効なUUIDを指定してください。',

    /*
    |--------------------------------------------------------------------------
    | Custom バリデーション言語行
    |--------------------------------------------------------------------------
    |
    | "属性.ルール"の規約でキーを指定することでカスタムバリデーション
    | メッセージを定義できます。指定した属性ルールに対する特定の
    | カスタム言語行を手早く指定できます。
    |
    */

    'custom' => [
        '属性名' => [
            'ルール名' => 'カスタムメッセージ',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | カスタムバリデーション属性名
    |--------------------------------------------------------------------------
    |
    | 以下の言語行は、例えば"email"の代わりに「メールアドレス」のように、
    | 読み手にフレンドリーな表現でプレースホルダーを置き換えるために指定する
    | 言語行です。これはメッセージをよりきれいに表示するために役に立ちます。
    |
    */

    'attributes' => [],

];
```

## 17. TOPページの表示

- `$ php artisan make:test Http/Controllers/PostListControllerTest`を実行  

- 最初は失敗するテストを書く  

`routes/web.php`を編集  

```php:web.php
<?php

use Illuminate\Support\Facades\Route;
```

`tests/Feature/Http/Controllers/PostListControllerTest.php`を編集  

```php:PostListControllerTest.php
<?php

namespace Tests\Feature\Http\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PostListControllerTest extends TestCase
{
    /**
     * @test
     */
    function TOPページで、ブログ一覧が表示される()
    {
        $this->get('/')
            ->assertOk();
    }
}
```

`$ php artisan test --filter TOPページで、ブログ一覧が表示される`を実行  

```:terminal
 FAIL  Tests\Feature\Http\Controllers\PostListControllerTest
  ⨯ t o pページで、ブログ一覧が表示される

  ---

  • Tests\Feature\Http\Controllers\PostListControllerTest > t o pページで、ブログ一覧が表示される
  Expected response status code [200] but received 404.
  Failed asserting that 200 is identical to 404.

  at tests/Feature/Http/Controllers/PostListControllerTest.php:17
     13▕      */
     14▕     function TOPページで、ブログ一覧が表示される()
     15▕     {
     16▕         $this->get('/')
  ➜  17▕             ->assertOk();
     18▕     }
     19▕ }
     20▕ 


  Tests:  1 failed
  Time:   0.18s
```

`routes/web.php`を編集  

```php:web.php
<?php

use Illuminate\Support\Facades\Route;

// この状態でstausコード200は返すようになるのでテストはpassする
Route::get('', function () {

});
```

`$ php artisan test --filter TOPページで、ブログ一覧が表示される`を実行  

```:terminal
   PASS  Tests\Feature\Http\Controllers\PostListControllerTest
  ✓ t o pページで、ブログ一覧が表示される

  Tests:  1 passed
  Time:   0.15s
```

`$ php artisan make:controller PostListController`を実行  

`routes/web.php`を編集  

```php:web.php
<?php

use App\Http\Controllers\PostListController;
use Illuminate\Support\Facades\Route;

Route::get('', [PostListController::class, 'index']);
```

`$ php artisan test --filter TOPページで、ブログ一覧が表示される`を実行  

```:terminal
 FAIL  Tests\Feature\Http\Controllers\PostListControllerTest
  ⨯ t o pページで、ブログ一覧が表示される

  ---

  • Tests\Feature\Http\Controllers\PostListControllerTest > t o pページで、ブログ一覧が表示される
  Expected response status code [200] but received 500.
  Failed asserting that 200 is identical to 500.
  
  The following exception occurred during the last request:
  
  <!-- 〜省略〜 -->
  
  ----------------------------------------------------------------------------------
  
  Method App\Http\Controllers\PostListController::index does not exist.

  at tests/Feature/Http/Controllers/PostListControllerTest.php:17
     13▕      */
     14▕     function TOPページで、ブログ一覧が表示される()
     15▕     {
     16▕         $this->get('/')
  ➜  17▕             ->assertOk();
     18▕     }
     19▕ }
     20▕ 


  Tests:  1 failed
  Time:   0.43s
```

`Http/Controllers/PostListController.php`を編集  

```php:PostListController.php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PostListController extends Controller
{
    // この状態で200を返すのでテストはpassする
    public function index()
    {
        
    }
}
```

`$ php artisan test --filter TOPページで、ブログ一覧が表示される`  を実行  

```:terminal
 PASS  Tests\Feature\Http\Controllers\PostListControllerTest
  ✓ t o pページで、ブログ一覧が表示される

  Tests:  1 passed
  Time:   0.17s
```
