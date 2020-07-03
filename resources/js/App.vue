<!-- ルートコンポーネント。index.blade.phpの <div id="app"></div> に描画 -->
<template>
  <div>
    <header>
      <Navbar />
    </header>
    <main>
      <div id="container">
        <Message /> <!-- 投稿サクセスメッセージ -->
        <RouterView /> <!-- Vue Router より提供 -->
      </div>
    </main>
    <Footer />
  </div>
</template>

<script>
import Message from './components/Message.vue' // 投稿サクセスメッセージ
import Navbar from './components/Navbar.vue'
import Footer from './components/Footer.vue'
// 定義したステータスコードを使用。NOT_FOUND = 404, UNAUTHORIZED = 419 認証切れ, INTERNAL_SERVER_ERROR = 500
import { NOT_FOUND, UNAUTHORIZED, INTERNAL_SERVER_ERROR } from './util'

export default {
  components: {
    Message, // 投稿サクセスメッセージ
    Navbar,
    Footer
  },
  computed: { // ストアのステートを参照
    errorCode () {
      return this.$store.state.error.code
    }
  },
  watch: {
    errorCode: { // 上記、算出プロパティのメソッドを監視
      async handler (val) {
        // サーバーエラーの場合
        if (val === INTERNAL_SERVER_ERROR) {
          this.$router.push('/500')
        // 認証エラーの場合
        } else if (val === UNAUTHORIZED) {
          // トークンをリフレッシュ
          await axios.get('/api/refresh-token')
          // ログインページ遷移のため、ストアのuserをクリア
          this.$store.commit('auth/setUser', null)
          // ログイン画面へ
          this.$router.push('/login')
        // 404エラーの場合
        } else if (val === NOT_FOUND) {
          this.$router.push('/not-found')
        }
      },
      immediate: true
    },
    $route () {
      this.$store.commit('error/setCode', null)
    }
  }
}
</script>