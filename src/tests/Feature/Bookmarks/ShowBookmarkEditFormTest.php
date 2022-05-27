<?php

namespace Tests\Feature\Bookmarks;

use App\Models\User;
use Tests\TestCase;

class ShowBookmarkEditFormTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @test
     */
    public function ステータス200が返る()
    {
        $user = User::query()->find(1);
        $response = $this->actingAs($user)->get('/bookmark-edit/1');

        $response->assertStatus(200);
    }

    /**
     * @test
     */
    public function ユーザーが未認証の場合にログインページにリダイレクトされる()
    {
        $this->get('/bookmark-edit/1')->assertRedirect('/login');
    }
}
