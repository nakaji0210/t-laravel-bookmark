<?php

namespace Tests\Feature\Bookmarks;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\User;
use Illuminate\Support\Str;
use Tests\TestCase;

class UpdateBookmarkTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // VerifyCsrfTokenがあると419で失敗する(今回テストしたいことではないので外す)
        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    /**
     * @test
     * @dataProvider updateBookmarkPutDataProvider
     */
    public function 正常にブックマークの更新ができる(?string $comment, ?int $categoryId, bool $isSuccess, array $sessionError)
    {
        $user = User::query()->find(1);

        // どのURLからリクエストされたかを仮想的に設定してputする
        $response = $this->actingAs($user)->from('/bookmarks/create')->put('/bookmarks/1', [
            'comment' => $comment,
            'category' => $categoryId,
        ]);

        /**
         * データプロバイダー側の結果に応じてアサートする内容を分岐
         */
        if ($isSuccess) {
            $response->assertRedirect('/bookmarks');
            $this->assertDatabaseHas('bookmarks', [
                'id' => 1,
                'comment' => $comment,
                'category_id' => $categoryId,
            ]);
        } else {
            $response->assertRedirect('/bookmarks/create');
            $response->assertSessionHasErrors($sessionError);
            $this->assertDatabaseMissing('bookmarks', [
                'id' => 1,
                'comment' => $comment,
                'category_id' => $categoryId,
            ]);
        }
    }

    /**
     * データプロバイダ
     * @see https://phpunit.readthedocs.io/ja/latest/writing-tests-for-phpunit.html#writing-tests-for-phpunit-data-providers
     * @return array
     */
    public function updateBookmarkPutDataProvider()
    {
        return [
            // $comment, $categoryId, $isSuccess, $sessionError
            [Str::random(10), 1, true, []],
            [Str::random(9), 1, false, ['comment']],
            [Str::random(1000), 1, true, []],
            [Str::random(1001), 1, false, ['comment']],
            [Str::random(10), 0, false, ['category']],
            [Str::random(9), 0, false, ['comment', 'category']],
            [null, 1, false, ['comment']],
            [Str::random(10), null, false, ['category']],
            [null, null, false, ['comment', 'category']],
        ];
    }

    /**
     * @test
     */
    public function ユーザーが未認証の場合にログインページにリダイレクトされる()
    {
        $this->put('/bookmarks/1', [
            'comment' => 'ブックマークのテスト用のコメントです',
            'category' => 1,
        ])->assertRedirect('/login');
    }

    /**
     * @test
     */
    public function 他のユーザーのブックマークの場合に403で失敗する()
    {
        $user = User::query()->find(2);
        $this->actingAs($user)->put('/bookmarks/1', [
            'comment' => 'ブックマークのテスト用のコメントです',
            'category' => 1,
        ])->assertForbidden();
    }
}
