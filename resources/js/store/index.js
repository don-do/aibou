import Vue from 'vue'
import Vuex from 'vuex'

import auth from './auth'
import error from './error' // errorモジュール読み込み
import message from './message' // サクセスメッセージ用のモジュール読み込み

// Vuex プラグインの使用を宣言
Vue.use(Vuex)

const store = new Vuex.Store({
  modules: { // ストア作成時、各ファイルをモジュールとして登録。可読性のため
    auth,
    error, // errorモジュール読み込み
    message
  }
})

export default store
