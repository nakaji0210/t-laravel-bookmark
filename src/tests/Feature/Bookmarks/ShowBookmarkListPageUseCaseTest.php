<?php

namespace Tests\Feature\Bookmarks;

use App\Bookmark\UseCase\ShowBookmarkListPageUseCase;
use Artesaos\SEOTools\Facades\SEOTools;
use Tests\TestCase;

class ShowBookmarkListPageUseCaseTest extends TestCase
{
    private ShowBookmarkListPageUseCase $useCase;

    // テストメソッドごとに毎回実行される
    protected function setUp(): void
    {
        parent::setUp();

        // ユースケースのインスタンスを作成
        $this->useCase = new ShowBookmarkListPageUseCase();
    }

    /**
     * @test
     */
    public function 正しいレスポンスが返る()
    {
        /**
         * SEOについてテストすること
         * ・<title>タグの中身が正しく設定されていること
         * ・<meta description>の中身が正しく設定されていること
         */
        SEOTools::shouldReceive('setTitle')->withArgs(['ブックマーク一覧'])->once();
        SEOTools::shouldReceive('setDescription')->withArgs([
            '技術分野に特化したブックマーク一覧です。みんなが投稿した技術分野のブックマークが投稿順に並んでいます。HTML、CSS、JavaScript、Rust、Goなど、気になる分野のブックマークに絞って調べることもできます'
        ])->once();

        /**
         * レスポンスについてテストすること
         * ・ブックマーク一覧について：10件取得できていること、最新順で10件になっていること
         * ・トップカテゴリーについて：10件取得できていること、内容が投稿数順になっていること
         * ・トップユーザーについて：10人取得できていること、内容が投稿数順になっていること
         */
        $response = $this->useCase->handle();
        self::assertCount(10, $response['bookmarks']);
        self::assertCount(10, $response['top_categories']);
        self::assertCount(10, $response['top_users']);

        // bookmarksのIDが100から91まで降順に格納されているか軽くチェック
        for ($i = 100; $i > 90; $i--) {
            self::assertSame($i, $response['bookmarks'][100 - $i]->id);
        }

        // top_categoriesのIDが昇順に格納されているか軽くチェック
        for ($i = 1; $i < 10; $i++) {
            self::assertSame($i, $response['top_categories'][$i - 1]->id);
        }

        // top_usersのIDが昇順に格納されているか軽くチェック
        for ($i = 1; $i < 10; $i++) {
            self::assertSame($i, $response['top_users'][$i - 1]->id);
        }
    }
}
