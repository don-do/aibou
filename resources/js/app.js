// Axiosライブラリの設定（Web API用のCSRFトークンチェック）をインポート
import './bootstrap'
import Vue from 'vue'
// ルーティングの定義をインポート
import router from './router'
// ストアをインポート
import store from './store'
// ルートコンポーネントをインポート
import App from './App.vue'


// リロード時、Vueアプリケーションのログイン状態を維持

// 起動処理をcreateApp関数にまとめ、最後に呼び出す。
// currentUserアクションの非同期処理終了後に、
// Vueインスタンスを生成し非同期処理をawaitするためには、
// async()内部にいる必要があるため
const createApp = async () => { // async()内部にて処理。最後にcreateAppに代入
  // Vueインスタンス生成前でも、
  // アクションをdispatch()で呼び出せる。ストアがインポートされているため
  await store.dispatch('auth/currentUser') // 非同期処理をawait

  // currentUserアクションの非同期処理終了後、Vueインスタンス生成
  new Vue({
    el: '#app',
    router, // ルーティングの定義を読み込む
    store, // ストアを読み込む
    components: { App }, // ルートコンポーネントの使用を宣言
    template: '<App />' // ルートコンポーネントを描画
  })
}

createApp()
