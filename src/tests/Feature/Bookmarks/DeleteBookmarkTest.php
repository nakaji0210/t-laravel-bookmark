<?php

namespace Tests\Feature\Bookmarks;

use App\Models\Bookmark;
use App\Models\User;
use Tests\TestCase;

class DeleteBookmarkTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @test
     */
    public function 正常にブックマークの削除ができる()
    {
        $user = User::query()->find(1);
        $bookmark = Bookmark::query()->find(1);

        $response = $this->actingAs($user)->delete('/bookmarks/' . $bookmark->id);
        $response->assertRedirect('/user/profile');
        $this->assertDeleted($bookmark);
    }

    /**
     * @test
     */
    public function ユーザーが未認証の場合にログインページにリダイレクトされる()
    {
        $this->delete('/bookmarks/1')->assertRedirect('/login');
    }

    /**
     * @test
     */
    public function 他のユーザーのブックマークの場合に403で失敗する()
    {
        $user = User::query()->find(2);
        $this->actingAs($user)->delete('/bookmarks/1')->assertForbidden();
    }
}
