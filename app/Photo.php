<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class Photo extends Model
{
    /** プライマリキーの型。初期設定のintから変更 */
    protected $keyType = 'string';

    /** JSONに含める属性 */
    protected $visible = [ // モデルのデータのうち、JSONに変換・表示するもの
        'id', 'owner', 'url', 'comments',
        'praises_count', 'praised_by_user',
    ];

    /** JSONに含めるアクセサ */
    protected $appends = [ // ユーザー定義のアクセサをJSON表現に含める
        'url', 'praises_count', 'praised_by_user',
    ];

    protected $perPage = 6; // 投稿一覧の、1ページあたりのアイテム数

    /** IDの桁数 */
    const ID_LENGTH = 12;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        if (! Arr::get($this->attributes, 'id')) {
            $this->setId();
        }
    }

    /**
     * ランダムなID値をid属性に代入する
     */
    private function setId() // デフォルトのルールと違い、Photo作成時にsetIdを使う必要がある
    {
        $this->attributes['id'] = $this->getRandomId();
    }

    /**
     * ランダムなID値を生成する
     * @return string
     */
    private function getRandomId()
    {
        $characters = array_merge(
            range(0, 9), range('a', 'z'),
            range('A', 'Z'), ['-', '_']
        );

        $length = count($characters);

        $id = "";

        for ($i = 0; $i < self::ID_LENGTH; $i++) {
            $id .= $characters[random_int(0, $length - 1)];
        }

        return $id;
    }

    /**
     * アクセサ - url
     * @return string
     */
    public function getUrlAttribute()
    {
        return Storage::disk('local')->url($this->attributes['filename']);
    }

    /**
     * アクセサ - praises_count
     * @return int
     */
    public function getPraisesCountAttribute()
    {
        return $this->praises->count();
    }

    /**
     * アクセサ - praised_by_user
     * @return boolean
     */
    public function getPraisedByUserAttribute()
    {
        if (Auth::guest()) {
            return false;
        }

        return $this->praises->contains(function ($user) {
            // グッジョブしたものが、ログインユーザーのIDと合致しているか判定
            // praisesリレーションからは、ユーザーモデルのコレクションが取得できる
            return $user->id === Auth::user()->id;
        });
    }

    /**
     * リレーションシップ - usersテーブル
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function owner() // JSON変換時、リレーション名ownerが反映される
    {   // owner()は自作メソッド名のため、belongsToの引数にてリレーション関係を明示
        return $this->belongsTo('App\User', 'user_id', 'id', 'users');
    }

    /**
     * リレーションシップ - commentsテーブル
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comments()
    {
        return $this->hasMany('App\Comment')->orderBy('id', 'desc');
    }

    /**
     * リレーションシップ - usersテーブル
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function praises()
    { // prasesテーブルは、外部キーしか中身のない中間テーブルのため、モデルクラス作成せず
        return $this->belongsToMany('App\User', 'praises')->withTimestamps(); // created_atとupdated_atカラムを更新
    }
}
