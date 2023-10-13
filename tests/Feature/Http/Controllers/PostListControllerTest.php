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
        $this->get('/')
            ->assertOk();
    }
}
