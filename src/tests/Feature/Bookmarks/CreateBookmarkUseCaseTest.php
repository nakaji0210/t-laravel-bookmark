<?php

namespace Tests\Feature\Bookmarks;

use App\Bookmark\UseCase\CreateBookmarkUseCase;
use App\Lib\LinkPreview\LinkPreview;
use App\Lib\LinkPreview\LinkPreviewInterface;
use App\Lib\LinkPreview\MockLinkPreview;
use App\Models\BookmarkCategory;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CreateBookmarkUseCaseTest extends TestCase
{
    private CreateBookmarkUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->app->bind(LinkPreviewInterface::class, MockLinkPreview::class);
        $this->useCase = $this->app->make(CreateBookmarkUseCase::class);
    }

    /**
     * @test
     */
    public function 正常にブックマークデータが登録できる()
    {
        // URLは絶対存在しないexample.comを使う
        $url = 'https://notfound.example.com/';
        $categoryId = BookmarkCategory::query()->first()->id;
        $comment = 'テスト用のコメント';

        // 強制ログイン
        $testUser = User::query()->first();
        Auth::loginUsingId($testUser->id);

        // UseCaseを実行してログアウト
        $this->useCase->handle($url, $categoryId, $comment);
        Auth::logout();

        // データベースに正常に保存されているかチェック
        $this->assertDatabaseHas('bookmarks', [
            'url' => $url,
            'category_id' => $categoryId,
            'user_id' => $testUser->id,
            'comment' => $comment,
            'page_title' => 'モックのタイトル',
            'page_description' => 'モックのdescription',
            'page_thumbnail_url' => 'https://i.gyazo.com/634f77ea66b5e522e7afb9f1d1dd75cb.png',
        ]);
    }

    /**
     * @test
     */
    public function LinkPreview取得失敗時に例外がスローされる()
    {
        $url = 'https://notfound.example.com/';
        $category = BookmarkCategory::query()->first()->id;
        $comment = 'テスト用のコメント';

        // Mockeryライブラリでモックを用意する
        $mock = \Mockery::mock(LinkPreviewInterface::class);

        // 作ったモックがgetメソッドを実行したら必ず例外を投げるように仕込む
        $mock->shouldReceive('getLinkPreview')
            ->withArgs([$url])
            ->andThrow(new \Exception('URLからメタ情報の取得に失敗'))
            ->once();

        // サービスコンテナに$mockを使うように命令する
        $this->app->instance(
            LinkPreviewInterface::class,
            $mock
        );

        // 例外が投げられることを期待する記述
        $this->expectException(ValidationException::class);
        $this->expectExceptionObject(ValidationException::withMessages([
            'url' => 'URLが存在しない等の理由で読み込めませんでした。変更して再度投稿してください'
        ]));

        // 実際の処理を実行
        $this->useCase = $this->app->make(CreateBookmarkUseCase::class);
        $this->useCase->handle($url, $category, $comment);
    }
}
