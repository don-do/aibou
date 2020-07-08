import Vue from 'vue'
import VueRouter from 'vue-router'

// ページコンポーネントをインポートする
import PhotoList from './pages/PhotoList.vue'
import PhotoDetail from './pages/PhotoDetail.vue'
import Login from './pages/Login.vue'
import SystemError from './pages/errors/System.vue' // システムエラー
import NotFound from './pages/errors/NotFound.vue' // 404エラー
// ストアをインポート。authストアのcheckゲッターを使用するため
import store from './store'

// VueRouterプラグインを使用。<RouterView />コンポーネントなどを使えるようになる
Vue.use(VueRouter)

// パスとコンポーネントのマッピング
const routes = [
  {
    path: '/',
    component: PhotoList, // PhotoListコンポーネントに、クエリパラメータpageの値をpropsとして渡す
    props: route => { // 引数にルート情報routeが入る
      const page = route.query.page // ルート情報routeから、クエリパラメータのpageの値を取り出す
      return { page: /^[1-9][0-9]*$/.test(page) ? page * 1 : 1 } // 自然数ならtrue。falseの場合、「1」
    }
  },
  { // 写真詳細ページのルートを定義
    path: '/photos/:id', // :id にて、パラメータとして定義。写真IDが入る
    component: PhotoDetail, // :id の値を、<PhotoDetail> コンポーネントにpropsとして渡す
    props: true // URLの変数部分（写真IDの値）をpropsとして受け取る
  },
  {
    path: '/login',
    component: Login,

    // ログイン状態でログインページにアクセスした場合、トップページへ遷移
    beforeEnter (to, from, next) {
    // ページコンポーネント切り替え直前に呼び出される、beforeEnter()
    // 第一引数to：アクセスしようとしているルートのルートオブジェクト
    // 第二引数from：アクセス元のルート
    // 第三引数next：ページの切り替わり先を決めるための関数
      if (store.getters['auth/check']) { // ログイン状態をチェック
        next('/') // トップページへ
      } else {
        next() // ログインページを表示
      }
    }
  },
  {
    path: '/500', // システムエラー
    component: SystemError
  },
  {
    path: '*', // 404エラー。任意のパス（定義されていないルートのパスによるアクセス）
    component: NotFound
  }
]

// VueRouterインスタンスを作成
const router = new VueRouter({
  mode: 'history', // ハッシュ#を使わずに、URLの形を再現
  scrollBehavior () { // ページ遷移時、スクロール位置を先頭に制御
    return { x: 0, y: 0 }
  },
  routes
})

// VueRouterインスタンスをエクスポートする。app.jsでインポートへ
export default router
