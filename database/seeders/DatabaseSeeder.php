<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

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

        [$first] = User::factory(15)->create()->each(function ($user) {
            // ランダムで2〜5件のブログ投稿をする
            Post::factory(random_int(2, 5))->random()->create(['user_id' => $user])
                ->each(function ($post) {
                    Comment::factory(random_int(1, 5))->create(['post_id' => $post]);
                });
        });

        $first->update([
            'name' => 'takaki',
            'email' => 'takaki55730317@gmail.com',
            'password' => Hash::make('password123'),
        ]);
    }
}
