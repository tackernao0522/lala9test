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
