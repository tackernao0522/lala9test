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
