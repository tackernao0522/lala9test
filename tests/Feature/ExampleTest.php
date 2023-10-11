<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    public $user;

    protected function setUp(): void // 下記のメソッドたちが走る前に呼び出される
    {
        parent::setUp();

        $this->user = 'hoge';
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_the_application_returns_a_successful_response()
    {
        // 準備
        dump($this->user);

        // 実行 / 検証
        $response = $this->get('/')
            ->assertStatus(200);
    }

    public function test_the_application_returns_a_successful_response2()
    {
        // 準備
        dump($this->user);

        // 実行 / 検証
        $response = $this->get('/')
            ->assertStatus(200);
    }
}
