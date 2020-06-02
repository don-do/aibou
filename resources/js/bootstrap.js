// クッキーの値をインポート
import { getCookieValue } from './util'

window.axios = require('axios')

// Axiosライブラリの設定を記述

// Ajaxリクエストであることを示すヘッダー（X-Requested-With）を付与
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'

// フォームではなく、ヘッダーを見てCSRFトークンチェックを行うようにする
window.axios.interceptors.request.use(config => {
  // クッキーからトークンを取り出してヘッダーに添付。トークンをX-XSRF-TOKENヘッダーに含める
  config.headers['X-XSRF-TOKEN'] = getCookieValue('XSRF-TOKEN')

  return config
})

// axios.interceptors.responseにて、レスポンス後の処理を上書き
window.axios.interceptors.response.use(
  response => response, // 成功時の処理
  // store/auth.jsのエラー時のAPI呼び出しをインターセプターにまとめた
  error => error.response || error // 失敗時の処理
)
