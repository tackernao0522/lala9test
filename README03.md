# Section02

## 18. withoutExceptionHandiling

`(例)`  
`$ php artisan make:controller BarController --test` を実行するとテストコントローラも同時に作成される  (その際にテストコントローラはFeature/Httpの階層に作成される ver8.51未満の場合)  

`テスト失敗時に古いLaravelだとエラー原因が特定されにくかった`  

- その時の対策  

`tests/Feature/Http/Controllers/PostListControllerTest.php`を下記のようにする  

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
        // Ver.8.51未満の場合で、500エラーが出た場合のエラー確認方法
        //
        // $this->withoutExceptionHandling();
        // ブラウザで確認できる場合は、ブラウザで確認する方法もある
        // エラーログを確認する

        // $this->withoutExceptionHandling();

        $this->get('/')
            ->assertOk();
    }
}
```
