<?php

namespace Tests\Feature\Bookmarks;

use Tests\TestCase;

class ShowBookmarkCategoryListPageTest extends TestCase
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
        $response = $this->get('/bookmarks/category/1');

        $response->assertStatus(200);
    }

    /**
     * @test
     */
    public function パスパラメータのカテゴリーIDが文字列の場合にステータス404となる()
    {
        $response = $this->get('/bookmarks/category/xxx');

        $response->assertStatus(404);
    }

    /**
     * @test
     */
    public function 存在しないカテゴリーIDの場合にステータス404となる()
    {
        $response = $this->get('/bookmarks/category/0');

        $response->assertStatus(404);
    }
}
