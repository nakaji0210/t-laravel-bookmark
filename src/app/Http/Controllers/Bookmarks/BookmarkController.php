<?php


namespace App\Http\Controllers\Bookmarks;

use App\Bookmark\UseCase\CreateBookmarkUseCase;
use App\Bookmark\UseCase\DeleteBookmarkUseCase;
use App\Bookmark\UseCase\ShowBookmarkListPageUseCase;
use App\Bookmark\UseCase\UpdateBookmarkUseCase;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateBookmarkRequest;
use App\Http\Requests\UpdateBookmarkRequest;
use App\Models\Bookmark;
use App\Models\BookmarkCategory;
use App\Models\User;
use Artesaos\SEOTools\Facades\SEOTools;
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
     * カテゴリが数字で無かった場合404
     * カテゴリが存在しないIDが指定された場合404
     *
     * title, descriptionにはカテゴリ名とカテゴリのブックマーク投稿数を含める
     *
     * 表示する内容は普通の一覧と同様
     * しかし、カテゴリに関しては現在のページのカテゴリを除いて表示する
     *
     * @param Request $request
     * @return Application|Factory|View
     */
    public function listCategory(Request $request)
    {
        $category_id = $request->category_id;
        if (!is_numeric($category_id)) {
            abort(404);
        }

        $category = BookmarkCategory::query()->findOrFail($category_id);

        SEOTools::setTitle("{$category->display_name}のブックマーク一覧");
        SEOTools::setDescription("{$category->display_name}に特化したブックマーク一覧です。みんなが投稿した{$category->display_name}のブックマークが投稿順に並んでいます。全部で{$category->bookmarks->count()}件のブックマークが投稿されています");

        $bookmarks = Bookmark::query()->with(['category', 'user'])->where('category_id', '=', $category_id)->latest('id')->paginate(10);

        // 自身のページのカテゴリを表示しても意味がないのでそれ以外のカテゴリで多い順に表示する
        $top_categories = BookmarkCategory::query()->withCount('bookmarks')->orderBy('bookmarks_count', 'desc')->orderBy('id')->where('id', '<>', $category_id)->take(10)->get();

        $top_users = User::query()->withCount('bookmarks')->orderBy('bookmarks_count', 'desc')->take(10)->get();

        return view('page.bookmark_list.index', [
            'h1' => "{$category->display_name}のブックマーク一覧",
            'bookmarks' => $bookmarks,
            'top_categories' => $top_categories,
            'top_users' => $top_users
        ]);
    }

    /**
     * ブックマーク作成フォームの表示
     * @return Application|Factory|View
     */
    public function showCreateForm()
    {
        if (Auth::id() === null) {
            return redirect('/login');
        }

        SEOTools::setTitle('ブックマーク作成');

        $master_categories = BookmarkCategory::query()->oldest('id')->get();

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
        // Memo: 引数が増える場合は配列にするか、引数の型を定義したクラスに詰めて渡すのが良い
        $useCase->handle(
            $request->url,
            $request->category,
            $request->comment
        );

        return redirect('/bookmarks', 302);
    }

    /**
     * 編集画面の表示
     * 未ログインであればログインページへ
     * 存在しないブックマークの編集画面なら表示しない
     * 他のユーザーのブックマークの場合は403エラーにする
     *
     * @param int $id
     * @return Application|Factory|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|View
     */
    public function showEditForm(int $id)
    {
        if (Auth::guest()) {
            // @note ここの処理はユーザープロフィールでも使われている
            return redirect('/login');
        }

        SEOTools::setTitle('ブックマーク編集');

        $bookmark = Bookmark::query()->findOrFail($id);
        if ($bookmark->user_id !== Auth::id()) {
            abort(403);
        }

        $master_categories = BookmarkCategory::query()->withCount('bookmarks')->orderBy('bookmarks_count', 'desc')->orderBy('id')->take(10)->get();

        return view('page.bookmark_edit.index', [
            'user' => Auth::user(),
            'bookmark' => $bookmark,
            'master_categories' => $master_categories,
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

        // 成功時は一覧ページへ
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
