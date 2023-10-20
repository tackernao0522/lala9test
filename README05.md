# Section2

## 27. ブログ投稿の公開フラグの作成

`database/migrations/crate_posts_table.php`を編集  

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
            $table->foreignId('user_id');
            $table->boolean('status'); // 追加
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

`app/Models/Post.php`を編集  

```php:Post.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    // 追加
    const OPEN = 1;
    const CLOSED = 0;
    // ここまで

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}
```

`database/factories/PostFactory.php`を編集  

```php:PostFactory.php
<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
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
            'user_id' => User::factory(), // 下記の書き方と同じになる

            // 'user_id' => User::factory()->create()->id, // この書き方はやめた方が良い

            // 'user_id' => function () {
            //     return User::factory()->create()->id;
            // },

            'status' => Post::OPEN, // テストの場合はこの固定でOK
            'title' => $this->faker->realText(20),
            'body' => $this->faker->realText(200),
        ];
    }

    // 追加
    // ブラウザ表示用のデータ テストとは別に実行される
    public function random()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => $this->faker->randomElement([1, 1, 1, 1, 0]),
            ];
        });
    }
    // ここまで
}
```

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
            Post::factory(random_int(2, 5))->random()->create(['user_id' => $user]) // 編集
                ->each(function ($post) {
                    Comment::factory(random_int(1, 5))->create(['post_id' => $post]);
                });
        });
    }
}
```

- `$ php artisan migrate:fresh --seed`を実行  

## 28. ブログの一覧で公開したものだけを表示

`tests/Features/Http/Controllers/PostListControllerTest.php`を編集  

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

    // 追加
    /**
     * @test
     */
    function ブログの一覧で、非公開のブログは表示されない()
    {
        Post::factory()->create([
            'status' => Post::CLOSED,
            'title' => 'これは非公開のブログです',
        ]);
    }
    // ここまで

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

`database/factories/PostFactory.php`を編集  

```php:PostFactory.php
<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
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
            'user_id' => User::factory(), // 下記の書き方と同じになる

            // 'user_id' => User::factory()->create()->id, // この書き方はやめた方が良い

            // 'user_id' => function () {
            //     return User::factory()->create()->id;
            // },

            'status' => Post::OPEN, // テストの場合はこの固定でOK
            'title' => $this->faker->realText(20),
            'body' => $this->faker->realText(200),
        ];
    }

    // ブラウザ表示用のデータ
    public function random()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => $this->faker->randomElement([1, 1, 1, 1, 0]),
            ];
        });
    }

    // 追加
    public function closed()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => Post::CLOSED,
            ];
        });
    }
    // ここまで
}
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

    // 編集
    /**
     * @test
     */
    function ブログの一覧で、非公開のブログは表示されない()
    {
        $post1 = Post::factory()->closed()->create([
            'title' => 'これは非公開のブログです',
        ]);

        $post2 = Post::factory()->create([
            'title' => 'これは公開済みのブログです',
        ]); // 公開されているデータ

        $this->get('/')
            ->assertDontSee('これは非公開のブログです')
            ->assertSee('これは公開済みのブログです');
    }
    // ここまで

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

- `$ php artisan test --filter ブログの一覧で、非公開のブログは表示されない`を実行  

```:terminal
   FAIL  Tests\Feature\Http\Controllers\PostListControllerTest
  ⨯ ブログの一覧で、非公開のブログは表示されない

  ---

  • Tests\Feature\Http\Controllers\PostListControllerTest > ブログの一覧で、非公開のブログは表示されない
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
                      <li>これは非公開のブログです 江古田 浩\n
                  (0件のコメント)</li>\n
                      <li>これは公開済みのブログです 小泉 春香\n
                  (0件のコメント)</li>\n
              </ul>\n
  </body>\n
  \n
  </html>\n
  ' does not contain "これは非公開のブログです".

  at tests/Feature/Http/Controllers/PostListControllerTest.php:69
     65▕             'title' => 'これは公開済みのブログです',
     66▕         ]); // 公開されているデータ
     67▕ 
     68▕         $this->get('/')
  ➜  69▕             ->assertDontSee('これは非公開のブログです')
     70▕             ->assertSee('これは公開済みのブログです');
     71▕     }
     72▕ 
     73▕     /**


  Tests:  1 failed
  Time:   0.68s
```

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
            ->with('user')
            ->where('status', Post::OPEN) // 追加
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

- `$  php artisan test --filter ブログの一覧で、非公開のブログは表示されない`を実行  

```:terminal
 PASS  Tests\Feature\Http\Controllers\PostListControllerTest
  ✓ ブログの一覧で、非公開のブログは表示されない

  Tests:  1 passed
  Time:   0.40s
```

- __リファクタリングする__  

`app/Models/Post.php`を編集  

```php:Post.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    const OPEN = 1;
    const CLOSED = 0;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    // 追加
    public function scopeOnlyOpen($query)
    {
        $query->where('status', self::OPEN);
    }
}
```

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

        // $posts = Post::query()
        //     ->with('user')
        //     ->where('status', Post::OPEN)
        //     ->orderByDesc('comments_count')
        //     ->withCount('comments')
        //     ->get();

        $posts = Post::query()
            ->onlyOpen() // 編集
            ->with('user')
            ->withCount('comments')
            ->orderByDesc('comments_count')
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

- `$ php artisan test --filter ブログの一覧で、非公開のブログは表示されない`を実行  

```:terminal
  PASS  Tests\Feature\Http\Controllers\PostListControllerTest
  ✓ ブログの一覧で、非公開のブログは表示されない

  Tests:  1 passed
  Time:   0.38s
```

## スコープのテスト

`app/Models/Post.php`を編集  

```php:Post.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    const OPEN = 1;
    const CLOSED = 0;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function scopeOnlyOpen($query)
    {
        return $query; // 追加してみる
        $query->where('status', self::OPEN);
    }
}
```

- `$ php artisan test --filter ブログの一覧で、非公開のブログは表示されない`を実行  

```:terminal
 FAIL  Tests\Feature\Http\Controllers\PostListControllerTest
  ⨯ ブログの一覧で、非公開のブログは表示されない

  ---

  • Tests\Feature\Http\Controllers\PostListControllerTest > ブログの一覧で、非公開のブログは表示されない
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
                      <li>これは非公開のブログです 笹田 香織\n
                  (0件のコメント)</li>\n
                      <li>これは公開済みのブログです 藤本 健一\n
                  (0件のコメント)</li>\n
              </ul>\n
  </body>\n
  \n
  </html>\n
  ' does not contain "これは非公開のブログです".

  at tests/Feature/Http/Controllers/PostListControllerTest.php:69
     65▕             'title' => 'これは公開済みのブログです',
     66▕         ]); // 公開されているデータ
     67▕ 
     68▕         $this->get('/')
  ➜  69▕             ->assertDontSee('これは非公開のブログです')
     70▕             ->assertSee('これは公開済みのブログです');
     71▕     }
     72▕ 
     73▕     /**


  Tests:  1 failed
  Time:   0.42s
```

`tests/Features/Models/PostTest.php`を編集  

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

    /**
     * @test
     */
    function commentsリレーションのテスト()
    {
        $post = Post::factory()->create();

        // $post->comments; // eroguentコレクションが返ってくる

        $this->assertInstanceOf(Collection::class, $post->comments);
    }

    // 追加
    /**
     * @test
     */
    function ブログの公開・非公開のscope()
    {
        $post1 = Post::factory()->closed()->create();
        $post2 = Post::factory()->create(); // 公開されているデータ

        $posts = Post::onlyOpen()->get();

        $this->assertFalse($posts->contains($post1));
        $this->assertTrue($posts->contains($post2));
    }
    // ここまで
}
```

- `$ php artisan test --filter ブログの公開・非公開のscope`を実行  

```:terminal
   FAIL  Tests\Feature\Models\PostTest
  ⨯ ブログの公開・非公開のscope

  ---

  • Tests\Feature\Models\PostTest > ブログの公開・非公開のscope
  Failed asserting that true is false.

  at tests/Feature/Models/PostTest.php:48
     44▕         $post2 = Post::factory()->create(); // 公開されているデータ
     45▕ 
     46▕         $posts = Post::onlyOpen()->get();
     47▕ 
  ➜  48▕         $this->assertFalse($posts->contains($post1));
     49▕         $this->assertTrue($posts->contains($post2));
     50▕     }
     51▕ }
     52▕ 


  Tests:  1 failed
  Time:   0.38s
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

    const OPEN = 1;
    const CLOSED = 0;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function scopeOnlyOpen($query)
    {
        // return $query; 削除
        $query->where('status', self::OPEN);
    }
}
```

- `$ php artisan test --filter ブログの公開・非公開のscope`を実行  

```:terminal
  PASS  Tests\Feature\Models\PostTest
  ✓ ブログの公開・非公開のscope

  Tests:  1 passed
  Time:   0.31s
```

※ たまに全体のテストをしてみること その際のコマンドは下記のコマンドでやることを推奨する  

- `$ vendor/bin/phpunit`を実行  

```:terminal
PHPUnit 9.6.13 by Sebastian Bergmann and contributors.

......                                                              6 / 6 (100%)

Time: 00:00.578, Memory: 42.00 MB

OK (6 tests, 15 assertions)
```
