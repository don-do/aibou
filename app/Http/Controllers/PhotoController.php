<?php

namespace App\Http\Controllers;

use App\Photo;
use App\Comment;
use App\Http\Requests\StorePhoto;
use App\Http\Requests\StoreComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PhotoController extends Controller
{
    public function __construct()
    {
        // 認証が必要
        $this->middleware('auth')->except(['index', 'download', 'show']); // 認証の適応対象から除外
    }

    /**
     * 写真一覧
     */
    public function index()
    {
        $photos = Photo::with(['owner', 'praises']) // with()にてリレーションを事前にロード。SQL発行回数を抑える
            ->orderBy(Photo::CREATED_AT, 'desc')->paginate(); // ページング自動生成（JSONレスポンスのページ情報追加）
        // コントローラーからモデルクラスのインスタンスなどをreturnすると、自動でJSONに変換・レスポンス生成
        return $photos; // アクセサは含まれないため、JSONとして返したいアクセサは、モデルの$appendsに登録
    }

    /**
     * 写真詳細
     * @param string $id
     * @return Photo
     */
    public function show(string $id) // 引数でパスのパラメータidを受け取る
    {
        // comments.authorでは、PhotoモデルのcommentsリレーションからCommentを取得し、authorリレーションをたどって、Userを取得
        $photo = Photo::where('id', $id)->with(['owner', 'comments.author', 'praises'])->first();

        return $photo ?? abort(404); // 写真データが見つからなかった場合（NULLの場合）、404を返却
    }

    /**
     * 写真投稿
     * @param StorePhoto $request
     * @return \Illuminate\Http\Response
     */
    public function create(StorePhoto $request)
    {
        // 投稿写真の拡張子を取得
        $extension = $request->photo->extension();

        $photo = new Photo();

        // インスタンス生成時に割り振られたランダムなID値と
        // 拡張子を組み合わせてファイル名とする
        $photo->filename = $photo->id . '.' . $extension;

        // laravelのローカルストレージ（storage/app/public）にファイルを保存
        Storage::disk('local')->putFileAs('public', $request->photo, $photo->filename);

        // トランザクションを利用。データベースエラー時にファイル削除を行う
        DB::beginTransaction();

        try {
            Auth::user()->photos()->save($photo); // save直後、なぜか$photo内のidが消える
            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            // アップロードしたファイルを削除。DBとの不整合を避ける
            Storage::disk('local')->delete('public/' . $photo->filename);
            throw $exception;
        }

        // レスポンスコードは201(CREATED)を返却。リソース新規作成のため
        return response($photo, 201);
    }

    /**
     * 写真ダウンロード
     * @param Photo $photo
     * @return \Illuminate\Http\Response
     */
    public function download(Photo $photo)
    {
        // 写真の存在チェック
        if (! Storage::disk('local')->exists('public/' . $photo->filename)) {
            abort(404);
        }

        $disposition = 'attachment; filename="' . $photo->filename . '"';
        $headers = [
            'Content-Type' => 'application/octet-stream',
            // attachmentおよびfilenameを指定することで、レスポンスの内容（ストレージから取得した画像ファイル）を
            // DLするために、保存ダイアログを開くようにブラウザに指示
            'Content-Disposition' => $disposition,
        ];

        return response(Storage::disk('local')->get('public/' . $photo->filename), 200, $headers);
    }

    /**
     * コメント投稿
     * @param Photo $photo
     * @param StoreComment $request
     * @return \Illuminate\Http\Response
     */
    public function addComment(Photo $photo, StoreComment $request)
    {
        $comment = new Comment();
        $comment->content = $request->get('content');
        $comment->user_id = Auth::user()->id;
        $photo->comments()->save($comment);

        // コメントを取得しなおす。authorリレーションをロードするため
        $new_comment = Comment::where('id', $comment->id)->with('author')->first();

        return response($new_comment, 201);
    }

    /**
     * グッジョブ
     * @param string $id
     * @return array
     */
    public function praise(string $id)
    {
        $photo = Photo::where('id', $id)->with('praises')->first();

        if (! $photo) {
            abort(404);
        }

        $photo->praises()->detach(Auth::user()->id);
        $photo->praises()->attach(Auth::user()->id);

        return ["photo_id" => $id];
    }

    /**
     * グッジョブ解除
     * @param string $id
     * @return array
     */
    public function praiseless(string $id)
    {
        $photo = Photo::where('id', $id)->with('praises')->first();

        if (! $photo) {
            abort(404);
        }

        $photo->praises()->detach(Auth::user()->id);

        return ["photo_id" => $id];
    }
}
