<?php

namespace App\Http\Controllers\Bookmarks;

use App\Bookmark\UseCase\CreateBookmarkUseCase;
use App\Bookmark\UseCase\DeleteBookmarkUseCase;
use App\Bookmark\UseCase\ShowBookmarkCategoryListPageUseCase;
use App\Bookmark\UseCase\ShowBookmarkCreateFormUseCase;
use App\Bookmark\UseCase\ShowBookmarkEditFormUseCase;
use App\Bookmark\UseCase\ShowBookmarkListPageUseCase;
use App\Bookmark\UseCase\UpdateBookmarkUseCase;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateBookmarkRequest;
use App\Http\Requests\UpdateBookmarkRequest;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class BookmarkController extends Controller
{
    /**
     * ブックマーク一覧画面
     *
     * @return Application|Factory|View
     */
    public function list(ShowBookmarkListPageUseCase $useCase)
    {
        return view('page.bookmark_list.index', [
            'h1' => 'ブックマーク一覧',
        ] + $useCase->handle());
    }

    /**
     * カテゴリ別ブックマーク一覧
     *
     * @param Request $request
     * @param ShowBookmarkCategoryListPageUseCase $useCase
     * @return Application|Factory|View
     */
    public function listCategory(Request $request, ShowBookmarkCategoryListPageUseCase $useCase)
    {
        $categoryId = $request->category_id;
        if (!is_numeric($categoryId)) {
            abort(404);
        }

        $useCaseResult = $useCase->handle($categoryId);

        return view('page.bookmark_list.index', [
            'h1' => "{$useCaseResult['categoryDisplayName']}のブックマーク一覧",
            'bookmarks' => $useCaseResult['bookmarks'],
            'top_categories' => $useCaseResult['topCategories'],
            'top_users' => $useCaseResult['topUsers']
        ]);
    }

    /**
     * ブックマーク作成フォームの表示
     * @param ShowBookmarkCreateFormUseCase $useCase
     * @return Application|Factory|View
     */
    public function showCreateForm(ShowBookmarkCreateFormUseCase $useCase)
    {
        $master_categories = $useCase->handle();

        return view('page.bookmark_create.index', [
            'master_categories' => $master_categories,
        ]);
    }

    /**
     * ブックマーク作成処理
     *
     * @param CreateBookmarkRequest $request
     * @return Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function create(CreateBookmarkRequest $request, CreateBookmarkUseCase $useCase)
    {
        $useCase->handle(
            $request->url,
            $request->category,
            $request->comment
        );

        return redirect('/bookmarks', 302);
    }

    /**
     * 編集画面の表示
     *
     * @param int $id
     * @param ShowBookmarkEditFormUseCase $useCase
     * @return Application|Factory|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|View
     */
    public function showEditForm(int $id, ShowBookmarkEditFormUseCase $useCase)
    {
        $authUserId = Auth::user()->id;
        $useCaseResult = $useCase->handle($id, $authUserId);

        return view('page.bookmark_edit.index', [
            'user' => $authUserId,
            'bookmark' => $useCaseResult['bookmark'],
            'master_categories' => $useCaseResult['master_categories'],
        ]);
    }

    /**
     * ブックマーク更新
     *
     * @param UpdateBookmarkRequest $request
     * @param int $id
     * @param UpdateBookmarkUseCase $useCase
     * @return Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws ValidationException
     */
    public function update(UpdateBookmarkRequest $request, int $id, UpdateBookmarkUseCase $useCase)
    {
        $useCase->handle(
            $id,
            $request->category,
            $request->comment
        );

        return redirect('/bookmarks', 302);
    }

    /**
     * ブックマーク削除
     *
     * @param int $id
     * @param DeleteBookmarkUseCase $useCase
     * @return Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws ValidationException
     */
    public function delete(int $id, DeleteBookmarkUseCase $useCase)
    {
        $useCase->handle($id);

        return redirect('/user/profile', 302);
    }
}
