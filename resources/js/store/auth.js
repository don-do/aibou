// ステータスコードをインポート。UNPROCESSABLE_ENTITY = 422 laravelのバリデーションエラー
import { OK, CREATED, UNPROCESSABLE_ENTITY } from '../util'

const state = {
  user: null, // ログイン済ユーザーを保持
  apiStatus: null, // API呼び出しの成否を表示
  loginErrorMessages: null, // ログイン時、バリデーションエラーメッセージを入れる
  registerErrorMessages: null // 登録時、バリデーションエラーメッセージを入れる
}

const getters = {
  // ログインチェック。undefinedでエラーが出る古いブラウザに対応するため、確実に真偽値を返す二重否定
  check: state => !! state.user,
  // ログインユーザーのname。userがnullだったとしてもエラーとならないよう、空文字を返却
  username: state => state.user ? state.user.name : ''
}

const mutations = {
  setUser (state, user) { // userステートの値を更新。第一引数state、第二引数に実引数が渡される
    state.user = user
  },
  setApiStatus (state, status) {
    state.apiStatus = status // statusステートの値を更新
  },
  setLoginErrorMessages (state, messages) {
    state.loginErrorMessages = messages // バリデーションエラーメッセージのステートの値を更新
  },
  setRegisterErrorMessages (state, messages) {
    state.registerErrorMessages = messages // バリデーションエラーメッセージのステートの値を更新
  }
}

// アクション
const actions = {

  // 会員登録
  // contextオブジェクト（ミューテーションを呼び出すcommitメソッドが入っている）を渡す
  async register (context, data) {
    // apiStatusを通信結果によって更新。最初はunllを入れておく
    context.commit('setApiStatus', null)
    // 会員登録API呼び出し
    const response = await axios.post('/api/register', data)

    if (response.status === CREATED) { // 成功した場合
      context.commit('setApiStatus', true)
      // setUserミューテーションを実行し、userステートを更新
      context.commit('setUser', response.data)
      return false
    }

    context.commit('setApiStatus', false) // 失敗した場合
    if (response.status === UNPROCESSABLE_ENTITY) { // 構文が正しいものの、中に含まれている指示が処理できなかった
      // バリデーションエラーの場合、そのページコンポーネント内でエラーを表示
      context.commit('setRegisterErrorMessages', response.data.errors)
    } else {
      // error.jsモジュールのsetCodeミューテーションをcommit（別モジュールをcommitするには、第三引数 {root: true} とする）
      context.commit('error/setCode', response.status, { root: true })
    }
  },

  // ログイン
  // contextオブジェクト（ミューテーションを呼び出すcommitメソッドが入っている）を渡す
  async login (context, data) {
    // apiStatusを通信結果によって更新。最初はunllを入れておく
    context.commit('setApiStatus', null)
    // ログインAPI呼び出し
    const response = await axios.post('/api/login', data)

    if (response.status === OK) { // 成功した場合
      context.commit('setApiStatus', true)
      // setUserミューテーションを実行し、userステートを更新
      context.commit('setUser', response.data)
      return false
    }

    context.commit('setApiStatus', false) // 失敗した場合
    if (response.status === UNPROCESSABLE_ENTITY) { // 構文が正しいものの、中に含まれている指示が処理できなかった
      // バリデーションエラーの場合、そのページコンポーネント内でエラーを表示
      context.commit('setLoginErrorMessages', response.data.errors)
    } else {
      // error.jsモジュールのsetCodeミューテーションをcommit（別モジュールをcommitするには、第三引数 {root: true} とする）
      context.commit('error/setCode', response.status, { root: true })
    }
  },

  // ログアウト
  // contextオブジェクト（ミューテーションを呼び出すcommitメソッドが入っている）を渡す
  async logout (context) {
    // apiStatusを通信結果によって更新。最初はunllを入れておく
    context.commit('setApiStatus', null)
    // ログアウトAPI呼び出し
    const response = await axios.post('/api/logout')

    if (response.status === OK) { // 成功した場合
      context.commit('setApiStatus', true)
      // setUserミューテーションを実行し、userステートを更新
      context.commit('setUser', null)
      return false
    }

    context.commit('setApiStatus', false) // 失敗した場合
    // error.jsモジュールのsetCodeミューテーションをcommit（別モジュールをcommitするには、第三引数 {root: true} とする）
    context.commit('error/setCode', response.status, { root: true })
  },

  // ログインユーザーチェック
  // contextオブジェクト（ミューテーションを呼び出すcommitメソッドが入っている）を渡す
  async currentUser (context) {
    // apiStatusを通信結果によって更新。最初はunllを入れておく
    context.commit('setApiStatus', null)
    // ユーザーAPI呼び出し
    const response = await axios.get('/api/user')
    // ログインしていない場合、response.dataは空文字。その場合nullを入れ、初期値のnullに揃えておく
    const user = response.data || null

    if (response.status === OK) { // 成功した場合
      context.commit('setApiStatus', true)
      // setUserミューテーションを実行し、userステートを更新
      context.commit('setUser', user)
      return false
    }

    context.commit('setApiStatus', false) // 失敗した場合
    // error.jsモジュールのsetCodeミューテーションをcommit（別モジュールをcommitするには、第三引数 {root: true} とする）
    context.commit('error/setCode', response.status, { root: true })
  }
}

export default {
  namespaced: true, // ステート名やミューテーション名が同じでも、モジュール名で区別するため
  state,
  getters,
  mutations,
  actions
}
