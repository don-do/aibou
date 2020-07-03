/**
 * クッキーの値を取得する
 * @param {String} searchKey 検索するキー
 * @returns {String} キーに対応する値
 */
export function getCookieValue (searchKey) {
  if (typeof searchKey === 'undefined') {
    return ''
  }

  let val = ''

  // document.cookieで、クッキーをname=12345;token=67890;key=abcdeの形式で参照
  document.cookie.split(';').forEach(cookie => { // splitで、["name=12345", "token=67890", "key=abcde"]となる
    const [key, value] = cookie.split('=') // keyとvalueに切り分け
    if (key === searchKey) {
      return val = value
    }
  })

  return val
}

// 使用するステータスコードを定義
export const OK = 200
export const CREATED = 201
export const NO_CONTENT = 204 // delComment（コメント削除）にて使用
export const INTERNAL_SERVER_ERROR = 500
export const UNPROCESSABLE_ENTITY = 422 // laravelのバリデーションエラーは422
export const UNAUTHORIZED = 419 // 認証切れ
export const NOT_FOUND = 404
