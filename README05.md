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
