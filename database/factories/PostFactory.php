<?php

namespace Database\Factories;

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

            'title' => $this->faker->realText(20),
            'body' => $this->faker->realText(200),
        ];
    }
}
