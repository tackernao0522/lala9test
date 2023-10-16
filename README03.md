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

## 19. ブログテーブルの用意  

参考: [Faker まとめ](https://github.com/nshiro/faker-summary)  

`$ php artisan make:model Post -mf`を実行  

`database/migrations/create_posts_table.php`を編集  

```php:create_posts_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->longText('body');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('posts');
    }
};
```

`database/factories/PostFactory.php`を編集  

```php:PostFactory.php
<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'title' => $this->faker->realText(20),
            'body' => $this->faker->realText(200),
        ];
    }
}
```


`database/seeders/DatabaseSeeder.php`を編集  

```php:DatabaseSeeder.php
<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Post;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        Post::factory(30)->create(); // 追加
    }
}
```

`$ php artisan migrate --seed`を実行  

## 20. ブログタイトルの一覧表示

`tests/Feature/Http/Controllers/PostListControllerTest.php`を編集  

```php:PostListControllerTest.php
<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Post;
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

        $post1 = Post::factory()->create(); // 追加
        $post2 = Post::factory()->create(); // 追加

        $this->get('/')
            ->assertOk()
            // 編集
            ->assertSee($post1->title)
            ->assertSee($post2->title);
            // ここまで
    }
}
```

`$ php artisan test --filter TOPページで、ブログ一覧が表示される` を実行  

`データベースエラーが出る`  

```:terminal
  FAIL  Tests\Feature\Http\Controllers\PostListControllerTest
  ⨯ t o pページで、ブログ一覧が表示される

  ---

  • Tests\Feature\Http\Controllers\PostListControllerTest > t o pページで、ブログ一覧が表示される
   Illuminate\Database\QueryException 

  SQLSTATE[HY000]: General error: 1 no such table: posts (SQL: insert into "posts" ("title", "body", "updated_at", "created_at") values (きて学校で見たことでもすぐみちを見てい。, もって、まもなく帰って一条じょうとうのさいね、トマトで見たあの苹果りんごうしまっすぐに歩いてくるみだれだけでした。カムパネルラが地図と首くびっくりした。五天気輪てんてあります」ジョバンニのうちあがら、いいままでもなくなりの中にただろう、なにかたって行けないんだんだんだ、やって叫さけびなが、そらをあげられ、木製もくさんだ。こんなさい」あのはこうとうを通るというこの頁ページいってそのときに本国へおり。, 2023-10-16 13:46:52, 2023-10-16 13:46:52))

  at vendor/laravel/framework/src/Illuminate/Database/Connection.php:760
    756▕         // If an exception occurs when attempting to run a query, we'll format the error
    757▕         // message to include the bindings with SQL, which will make this exception a
    758▕         // lot more helpful to the developer instead of just the database's errors.
    759▕         catch (Exception $e) {
  ➜ 760▕             throw new QueryException(
    761▕                 $query, $this->prepareBindings($bindings), $e
    762▕             );
    763▕         }
    764▕     }

      +16 vendor frames 
  17  tests/Feature/Http/Controllers/PostListControllerTest.php:25
      Illuminate\Database\Eloquent\Factories\Factory::create()


  Tests:  1 failed
  Time:   0.25s
```

`tests/Feature/Http/Controllers/PostListControllerTest.php`を編集  

```php:PostListControllerTest.php
<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PostListControllerTest extends TestCase
{
    use RefreshDatabase; // 追加

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

        $post1 = Post::factory()->create();
        $post2 = Post::factory()->create();

        $this->get('/')
            ->assertOk()
            ->assertSee($post1->title)
            ->assertSee($post2->title);
    }
}
```

`$ php artisan test --filter TOPページで、ブログ一覧が表示される` を実行  

```:terminal
 FAIL  Tests\Feature\Http\Controllers\PostListControllerTest
  ⨯ t o pページで、ブログ一覧が表示される

  ---

  • Tests\Feature\Http\Controllers\PostListControllerTest > t o pページで、ブログ一覧が表示される
  Failed asserting that '' contains "やジロフォンにまっすぐに立って、急いそ。". // まだ真っ白なページなのでまだタイトルは組んでませんという感じ

  at tests/Feature/Http/Controllers/PostListControllerTest.php:32
     28▕         $post2 = Post::factory()->create();
     29▕ 
     30▕         $this->get('/')
     31▕             ->assertOk()
  ➜  32▕             ->assertSee($post1->title)
     33▕             ->assertSee($post2->title);
     34▕     }
     35▕ }
     36▕ 


  Tests:  1 failed
  Time:   0.37s
```

`app/Http/Controllers/PostListController.php`を編集  

```php:PostListController.php
<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostListController extends Controller
{
    public function index()
    {
        $posts = Post::get();

        return view('index', compact('posts'));
    }
}
```

`$ touch resources/css/style.css`を実行  

`resources/css/style.css`を編集  

```css:style.css
.error-box {
    color: red;
}
.info-box {
    color: green;
}
nav {
    display: flex;
}
nav li {
    margin: 10px;
    list-style: none;
}
table {
    border-collapse: collapse;
    border-spacing: 0;
}
td {
    padding: 5px;
}
li > label:first-child {
    width: 170px;
    margin-right: 20px;
    float: left;
}
ul.error {
    color: red;
}
```

`$ mkdir resources/views/layouts && touch $_/index.blade.php`を実行  

```php:index.blade.php
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>ブログ</title>
    <link type="text/css" rel="stylesheet" href="/css/style.css">
</head>

<body>
    @yield('content')
</body>

</html>
```

`$ touch resources/views/index.blade.php`を実行  

`resources/views/index.blade.php`を編集  

```php:index.blade.php
@extends('layouts.index')

@section('content')
    <h1>ブログ一覧</h1>

    <ul>
        @foreach ($posts as $post)
            <li>{{ $post->title }}</li>
        @endforeach
    </ul>
@endsection
```

`$ php artisan test --filter TOPページで、ブログ一覧が表示される`を実行  

```:terminal
  PASS  Tests\Feature\Http\Controllers\PostListControllerTest
  ✓ t o pページで、ブログ一覧が表示される

  Tests:  1 passed
  Time:   0.51s
```

`tests/Feature/Http/controllers/PostListControllerTest.php`を編集(別の推奨する書き方)  

```php:PostListListControllerTest.php
<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PostListControllerTest extends TestCase
{
    use RefreshDatabase;

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

        // $post1 = Post::factory()->create();
        // $post2 = Post::factory()->create();

        // $this->get('/')
        //     ->assertOk()
        //     ->assertSee($post1->title)
        //     ->assertSee($post2->title);

        // 推奨の書き方
        $post1 = Post::factory()->create(['title' => 'ブログのタイトル1']);
        $post2 = Post::factory()->create(['title' => 'ブログのタイトル2']);

        $this->get('/')
            ->assertOk()
            ->assertSee('ブログのタイトル1')
            ->assertSee('ブログのタイトル2');
       //
    }
}
```

`$ php artisan test --filter TOPページで、ブログ一覧が表示される`を実行  (失敗時にわかりやすい)  

```:terminal
  PASS  Tests\Feature\Http\Controllers\PostListControllerTest
  ✓ t o pページで、ブログ一覧が表示される

  Tests:  1 passed
  Time:   0.31s
```

## 21. ブログタイトルの一覧表示(追加説明)  

`use RefreshDababase;`を`tests/TestCase.php`に書いておけばつけ忘れがなくなる  

- **注意** dusk や $seed ブロパティを使用する際は不都合が出るとのこと  

`tests/TestCase.php`を編集  

```php:TestCase.php
<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase; // 追加
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use RefreshDatabase; // 追加
}
```

`tests/Feature/Http/Controllers/PostControllerTest.php`を編集  

```php:PostControllerTest.php
<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PostListControllerTest extends TestCase
{
    // use RefreshDatabase; // 書き忘れていても親クラスに指定しているので大丈夫である

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

        // $post1 = Post::factory()->create();
        // $post2 = Post::factory()->create();

        // $this->get('/')
        //     ->assertOk()
        //     ->assertSee($post1->title)
        //     ->assertSee($post2->title);

        $post1 = Post::factory()->create(['title' => 'ブログのタイトル1']);
        $post2 = Post::factory()->create(['title' => 'ブログのタイトル2']);

        $this->get('/')
            ->assertOk() // 先頭に必ず書くこと エラー原因が分からなくなることがある
            ->assertSee('ブログのタイトル1')
            ->assertSee('ブログのタイトル2');
    }
}
```
