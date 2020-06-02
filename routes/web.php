<?php

// 写真ダウンロード
Route::get('/photos/{photo}/download', 'PhotoController@download');

// APIのURL以外のリクエストに対してはindexテンプレートを返す（最終的な受け皿として機能するので、最下部に記述）
// {any?}にて（あっても無くてもいい）任意パラメータ受け入れ。正規表現'.+'にて、どんな文字列も受け入れる
Route::get('/{any?}', function () { return view('index'); })->where('any', '.+');
