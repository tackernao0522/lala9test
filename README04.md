# Section2

## 23. ブログの書き方も一覧表示

`app/Models/Post.php`を編集  

```php:Post.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    // 編集(コメントアウトしておく)
    // public function user()
    // {
    //     return $this->belongsTo(User::class);
    // }
    // ここまで
}
```

`$ php artisan make:test Models/PostTest`を実行  

`tests/Feature/Models/PostTest.php`を編集  

```php:PostTest.php
<?php

namespace Tests\Feature\Models;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PostTest extends TestCase
{
    // use RefreshDatabase;

    /**
     * @test
     */
    function userリレーションを返す()
    {
        $post = Post::factory()->create();

        $this->assertInstanceOf(User::class, $post->user); // ユーザークラスのインスタンになっていればOK
    }
}
```

`$ php artisan test --filter userリレーションを返す`を実行  

```:terminal
  FAIL  Tests\Feature\Models\PostTest
  ⨯ userリレーションを返す

  ---

  • Tests\Feature\Models\PostTest > userリレーションを返す
  Failed asserting that null is an instance of class "App\Models\User".

  at tests/Feature/Models/PostTest.php:22
     18▕     function userリレーションを返す()
     19▕     {
     20▕         $post = Post::factory()->create();
     21▕ 
  ➜  22▕         $this->assertInstanceOf(User::class, $post->user); // ユーザークラスのインスタンになっていればOK
     23▕     }
     24▕ }
     25▕ 


  Tests:  1 failed
  Time:   0.37s
```

`app/Models/Post.php`を編集  

```php:Post.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    // コメントアウトを解除
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
```

`$ php artisan test --filter userリレーションを返す`を実行  

```:terminal
  PASS  Tests\Feature\Models\PostTest
  ✓ userリレーションを返す

  Tests:  1 passed
  Time:   0.33s
```

`tests/Feature/Http/Controllers/PostListControllerTest.php`を編集  

```php:PostListControllerTest.php
<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PostListControllerTest extends TestCase
{
    // use RefreshDatabase;

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
            ->assertOk()
            ->assertSee('ブログのタイトル1')
            ->assertSee('ブログのタイトル2')
            ->assertSee($post1->user->name) // 追加
            ->assertSee($post2->user->name); // 追加
    }

    /**
     * @test
     */
    function factoryの観察()
    {
        // $post = Post::factory()->make(['user_id' => null]);
        // dump($post);
        // dump($post->toArray());

        // dump(User::get()->toArray());

        $this->assertTrue(true);
    }
}
```

`$ php artisan test --filter TOPページで、ブログ一覧が表示される`を実行  

```:terminal
   FAIL  Tests\Feature\Http\Controllers\PostListControllerTest
  ⨯ t o pページで、ブログ一覧が表示される

  ---

  • Tests\Feature\Http\Controllers\PostListControllerTest > t o pページで、ブログ一覧が表示される
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
          <h1>ブログ一覧</h1>\n
  \n
      <ul>\n
                      <li>ブログのタイトル1</li>\n
                      <li>ブログのタイトル2</li>\n
              </ul>\n
  </body>\n
  \n
  </html>\n
  ' contains "井高 加奈".

  at tests/Feature/Http/Controllers/PostListControllerTest.php:43
     39▕         $this->get('/')
     40▕             ->assertOk()
     41▕             ->assertSee('ブログのタイトル1')
     42▕             ->assertSee('ブログのタイトル2')
  ➜  43▕             ->assertSee($post1->user->name)
     44▕             ->assertSee($post2->user->name);
     45▕     }
     46▕ 
     47▕     /**


  Tests:  1 failed
  Time:   0.38s
```

`resources/views/index.blade.php`を編集  

```php:index.blade.php
@extends('layouts.index')

@section('content')
    <h1>ブログ一覧</h1>

    <ul>
        @foreach ($posts as $post)
            <li>{{ $post->title }} {{ $post->user->name }}</li> // 編集
        @endforeach
    </ul>
@endsection
```

`$ php artisan test --filter TOPページで、ブログ一覧が表示される`を実行  

- 開発用のデータは入っていないのでトップページにアクセスするとブラウザーはエラーになる。テストはpassする  

```:terminal
   PASS  Tests\Feature\Http\Controllers\PostListControllerTest
  ✓ t o pページで、ブログ一覧が表示される

  Tests:  1 passed
  Time:   0.64s
```

`$ php artisan migrate:refresh --seed`を実行  

TOPページを開くとエラーが出ないでアクセスできる  

## 24. Userが複数Postを所有する際のDatabaseSeeder  

※ 現在はUserが一人当たり一つのブログ投稿しか所有していない状態になっている  

よってUser一人当たりが複数のブログを所有するようにSeederを作る  

`/database/seeders/DatabaseSeeder.php`を編集  

```php:DatabaseSeeder.php
<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Post;
use App\Models\User;
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

        // Post::factory(30)->create();

        // 編集
        User::factory(15)->create()->each(function ($user) {
            // ランダムで2〜5件のブログ投稿をする
            Post::factory(random_int(2, 5))->create(['user_id' => $user]);
        });
        // ここまで
    }
}
```

`$ php artisan migrate:refresh --seed`を実行  

## 25. コメント数を表示する

`$ php artisan make:model Comment -mf`を実行  

`database/migrations/create_comments_table.php`を編集  

```php:create_comments_table.php
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
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id');
            $table->string('name');
            $table->text('body');
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
        Schema::dropIfExists('comments');
    }
};
```

`database/factories/CommentFactory.php`を編集  

```php:CommentFactory.php
<?php

namespace Database\Factories;

use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Comment>
 */
class CommentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'post_id' => Post::factory(),
            'name' => $this->faker->name(),
            'body' => $this->faker->realText(20),
        ];
    }
}
```

`app/Models/Post.php`を編集  

```php:Post.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // 追加
    // public function comments()
    // {
    //     return $this->hasMany(Comment::class);
    // }
}
```

`tests/Feature/Models/PostTest.php`を編集  

```php:PostTest.php
<?php

namespace Tests\Feature\Models;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PostTest extends TestCase
{
    // use RefreshDatabase;

    /**
     * @test
     */
    function userリレーションを返す()
    {
        $post = Post::factory()->create();

        $this->assertInstanceOf(User::class, $post->user); // ユーザークラスのインスタンになっていればOK
    }

    // 追加
    /**
     * @test
     */
    function commentsリレーションのテスト()
    {
        $post = Post::factory()->create();

        // $post->comments; // eroguentコレクションが返ってくる

        $this->assertInstanceOf(Collection::class, $post->comments);
    }
    // ここまで
}
```

`$ php artisan test --filter commentsリレーションのテスト`を実行  

```:terminal
   FAIL  Tests\Feature\Models\PostTest
  ⨯ commentsリレーションのテスト

  ---

  • Tests\Feature\Models\PostTest > commentsリレーションのテスト
  Failed asserting that null is an instance of class "Illuminate\Database\Eloquent\Collection".

  at tests/Feature/Models/PostTest.php:35
     31▕         $post = Post::factory()->create();
     32▕ 
     33▕         // $post->comments; // eroguentコレクションが返ってくる
     34▕ 
  ➜  35▕         $this->assertInstanceOf(Collection::class, $post->comments);
     36▕     }
     37▕ }
     38▕ 


  Tests:  1 failed
  Time:   0.31s
```

`app/Models/Post.php`を編集  

```php:Post.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // コメントアウトを解除
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}
```

`$ php artisan test --filter commentsリレーションのテスト`を実行  

```:terminal
   PASS  Tests\Feature\Models\PostTest
  ✓ commentsリレーションのテスト

  Tests:  1 passed
  Time:   0.30s
```

`tests/Feature/Http/Controllers/PostListControllerTest.php`を編集  

```php:PostListControllerTest.php
<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PostListControllerTest extends TestCase
{
    // use RefreshDatabase;

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

        $post1 = Post::factory()->hasComments(3)->create(['title' => 'ブログのタイトル1']); // 編集
        $post2 = Post::factory()->hasComments(5)->create(['title' => 'ブログのタイトル2']); // 編集

        $this->get('/')
            ->assertOk()
            ->assertSee('ブログのタイトル1')
            ->assertSee('ブログのタイトル2')
            ->assertSee($post1->user->name)
            ->assertSee($post2->user->name)
            ->assertSee('(3件のコメント)') // 追加
            ->assertSee('(5件のコメント)'); // 追加
    }

    /**
     * @test
     */
    function factoryの観察()
    {
        // $post = Post::factory()->make(['user_id' => null]);
        // dump($post);
        // dump($post->toArray());

        // dump(User::get()->toArray());

        $this->assertTrue(true);
    }
}
```

`$ php artisan test --filter TOPページで、ブログ一覧が表示される`を実行  

```:terminal
   FAIL  Tests\Feature\Http\Controllers\PostListControllerTest
  ⨯ t o pページで、ブログ一覧が表示される

  ---

  • Tests\Feature\Http\Controllers\PostListControllerTest > t o pページで、ブログ一覧が表示される
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
          <h1>ブログ一覧</h1>\n
  \n
      <ul>\n
                      <li>ブログのタイトル1 三宅 太郎</li>\n
                      <li>ブログのタイトル2 山岸 洋介</li>\n
              </ul>\n
  </body>\n
  \n
  </html>\n
  ' contains "(3件のコメント)".

  at tests/Feature/Http/Controllers/PostListControllerTest.php:45
     41▕             ->assertSee('ブログのタイトル1')
     42▕             ->assertSee('ブログのタイトル2')
     43▕             ->assertSee($post1->user->name)
     44▕             ->assertSee($post2->user->name)
  ➜  45▕             ->assertSee('(3件のコメント)')
     46▕             ->assertSee('(5件のコメント)');
     47▕     }
     48▕ 
     49▕     /**


  Tests:  1 failed
  Time:   0.39s
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
        $posts = Post::withCount('comments')->get(); // 編集

        return view('index', compact('posts'));
    }
}
```

`resources/views/index.blade.php`を編集  

```php:index.blade.php
@extends('layouts.index')

@section('content')
    <h1>ブログ一覧</h1>

    <ul>
        @foreach ($posts as $post)
            <li>{{ $post->title }} {{ $post->user->name }}
                ({{ $post->comments_count }}件のコメント)</li> // 編集
        @endforeach
    </ul>
@endsection
```

`$ php artisan test --filter TOPページで、ブログ一覧が表示される`を実行  

```:terminal
   PASS  Tests\Feature\Http\Controllers\PostListControllerTest
  ✓ t o pページで、ブログ一覧が表示される

  Tests:  1 passed
  Time:   0.34s
```

ブラウザにアクセスするとエラーになる  

`database/seeders/DatabaseSeeder.php`を編集  

```php:DatabaseSeeder.php
<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
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

        // Post::factory(30)->create();

        // 編集
        User::factory(15)->create()->each(function ($user) {
            // ランダムで2〜5件のブログ投稿をする
            Post::factory(random_int(2, 5))->create(['user_id' => $user])->each(function ($post) {
                Comment::factory(random_int(2, 3))->create(['post_id' => $post]);
            });
        });
    }
}
```

`$ php artisan migrate:refresh --seed`を実行  

トップページにアクセスできる  

## 26. コメント数の多い順に表示する

`database/seeders/DatabaseSeeder.php`を編集  

```php:DatabaseSeeder.php
<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
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

        // Post::factory(30)->create();

        User::factory(15)->create()->each(function ($user) {
            // ランダムで2〜5件のブログ投稿をする
            Post::factory(random_int(2, 5))->create(['user_id' => $user])->each(function ($post) {
                Comment::factory(random_int(1, 5))->create(['post_id' => $post]); // 編集
            });
        });
    }
}
```

- `$ php artisan migrate:refresh --seed`を実行  

`tests/Feature/Http/Controllers/PostListControllerTest.php`を編集  

```php:PostListControllerTest
<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PostListControllerTest extends TestCase
{
    // use RefreshDatabase;

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

        $post1 = Post::factory()->hasComments(3)->create(['title' => 'ブログのタイトル1']);
        $post2 = Post::factory()->hasComments(5)->create(['title' => 'ブログのタイトル2']);
        Post::factory()->hasComments(1)->create();

        $this->get('/')
            ->assertOk()
            ->assertSee('ブログのタイトル1')
            ->assertSee('ブログのタイトル2')
            ->assertSee($post1->user->name)
            ->assertSee($post2->user->name)
            ->assertSee('(3件のコメント)')
            ->assertSee('(5件のコメント)')
            ->assertSeeInOrder([
                '(5件のコメント)',
                '(3件のコメント)',
                '(1件のコメント)',
            ]);
    }

    /**
     * @test
     */
    function factoryの観察()
    {
        // $post = Post::factory()->make(['user_id' => null]);
        // dump($post);
        // dump($post->toArray());

        // dump(User::get()->toArray());

        $this->assertTrue(true);
    }
}
```

- `$ php artisan test --filter TOPページで、ブログ一覧が表示される`を実行  

- `順番通り出てこないのでテストは通らない`  

```:terminal
   FAIL  Tests\Feature\Http\Controllers\PostListControllerTest
  ⨯ t o pページで、ブログ一覧が表示される

  ---

  • Tests\Feature\Http\Controllers\PostListControllerTest > t o pページで、ブログ一覧が表示される
  Failed asserting that Failed asserting that '<!DOCTYPE html>
  <html lang="ja">
  
  <head>
      <meta charset="UTF-8">
      <title>ブログ</title>
      <link type="text/css" rel="stylesheet" href="/css/style.css">
  </head>
  
  <body>
          <h1>ブログ一覧</h1>
  
      <ul>
                      <li>ブログのタイトル1 江古田 陽子
                  (3件のコメント)</li>
                      <li>ブログのタイトル2 山岸 涼平
                  (5件のコメント)</li>
                      <li>へんじまいまは列れつをぬいでした。先生。 近藤 翼
                  (1件のコメント)</li>
              </ul>
  </body>
  
  </html>
  ' contains "(3件のコメント)" in specified order..

  at tests/Feature/Http/Controllers/PostListControllerTest.php:49
     45▕             ->assertSee($post2->user->name)
     46▕             ->assertSee('(3件のコメント)')
     47▕             ->assertSee('(5件のコメント)')
     48▕             ->assertSeeInOrder([
  ➜  49▕                 '(5件のコメント)',
     50▕                 '(3件のコメント)',
     51▕                 '(1件のコメント)',
     52▕             ]);
     53▕     }


  Tests:  1 failed
  Time:   0.41s
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
        // 編集
        $posts = Post::query()
            ->orderByDesc('comments_count')
            ->withCount('comments')
            ->get();
        // ここまで

        return view('index', compact('posts'));
    }
}
```

- `$ php artisan test --filter TOPページで、ブログ一覧が表示される`を実行  

```:terminal
  PASS  Tests\Feature\Http\Controllers\PostListControllerTest
  ✓ t o pページで、ブログ一覧が表示される

  Tests:  1 passed
```

- __N+1問題の解決__  

`app/Http/Controllers/PostListController.php`を編集  

```php:PostListController.php
<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PostListController extends Controller
{
    public function index()
    {
        // $posts = Post::withCount('comments')
        //     ->orderBy('comments_count', 'desc')
        //     ->get();

        $posts = Post::query()
            ->with('user') // 追加
            ->orderByDesc('comments_count')
            ->withCount('comments')
            ->get();

        // $posts = Post::select(['posts.*', DB::raw('count(comments.id) as comments_count')])
        //     ->leftJoin('comments', 'posts.id', '=', 'comments.post_id')
        //     ->groupBy('posts.id')
        //     ->orderBy('comments_count', 'desc')
        //     ->get();

        // dd($posts);

        return view('index', compact('posts'));
    }
}
```
