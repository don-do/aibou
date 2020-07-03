<?php

use Illuminate\Http\Request;

// 会員登録
Route::post('/register', 'Auth\RegisterController@register')->name('register');

// ログイン
Route::post('/login', 'Auth\LoginController@login')->name('login');

// ログアウト
Route::post('/logout', 'Auth\LoginController@logout')->name('logout');

// ログインユーザー
// （ログインユーザーを返すだけ。コントローラー作成せず。
// ログインしていない場合、nullを返却。HTTPレスポンスに変換される時、nullは空文字に変わる）
Route::get('/user', function(){ return Auth::user(); })->name('user');

// 写真投稿
Route::post('/photos', 'PhotoController@create')->name('photo.create');

// 写真一覧
Route::get('/photos', 'PhotoController@index')->name('photo.index');

// 写真詳細（パラメータ{id}を定義）
Route::get('/photos/{id}', 'PhotoController@show')->name('photo.show');

// コメント
Route::post('/photos/{photo}/comments', 'PhotoController@addComment')->name('photo.comment');

  // コメント削除
  Route::delete('/comments/{id}', 'PhotoController@delComment');

// グッジョブ
Route::put('/photos/{id}/praise', 'PhotoController@praise')->name('photo.praise');

// グッジョブ解除
Route::delete('/photos/{id}/praise', 'PhotoController@praiseless');

// トークンリフレッシュ用のAPI
// 認証エラーの場合、ログインページへ移動。再ログインへ。
// その際、CSRFトークンをリフレッシュ
Route::get('/reflesh-token', function (Illuminate\Http\Request $request) {
    $request->session()->regenerateToken();

    return response()->json();
});
