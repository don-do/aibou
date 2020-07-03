<template>
  <div id="container--small">
    <ul class="tab">
      <li
        class="tab__item"
        :class="{'tab__item--active': tab === 1 }"
        @click="tab = 1"
      >ログイン</li>
      <li
        class="tab__item"
        :class="{'tab__item--active': tab === 2 }"
        @click="tab = 2"
      >登録</li>
    </ul>
    <div class="panel panel-login" v-show="tab === 1">
      <form class="form" @submit.prevent="login"> <!-- デフォルトアクション（ブラウザ本来の挙動）のみ停止 -->
        <!-- バリデーションエラー -->
        <div v-if="loginErrors" class="errors">
          <ul v-if="loginErrors.email">
            <li v-for="msg in loginErrors.email" :key="msg">{{ msg }}</li>
          </ul>
          <ul v-if="loginErrors.password">
            <li v-for="msg in loginErrors.password" :key="msg">{{ msg }}</li>
          </ul>
        </div>
        <label for="login-email">Eメール</label>
        <input type="text" class="form__item" id="login-email" v-model="loginForm.email">
        <label for="login-password">パスワード</label>
        <input type="password" class="form__item" id="login-password" v-model="loginForm.password">
        <div class="form__button">
          <button type="submit" class="button button--inverse">ログイン</button>
        </div>
      </form>
    </div>
    <div class="panel" v-show="tab === 2">
      <form class="form" @submit.prevent="register"> <!-- デフォルトアクション（ブラウザ本来の挙動）のみ停止 -->
        <!-- バリデーションエラー -->
        <div v-if="registerErrors" class="errors">
          <ul v-if="registerErrors.name">
            <li v-for="msg in registerErrors.name" :key="msg">{{ msg }}</li>
          </ul>
          <ul v-if="registerErrors.email">
            <li v-for="msg in registerErrors.email" :key="msg">{{ msg }}</li>
          </ul>
          <ul v-if="registerErrors.password">
            <li v-for="msg in registerErrors.password" :key="msg">{{ msg }}</li>
          </ul>
        </div>
        <label for="username">氏名</label>
        <input type="text" class="form__item" id="username" v-model="registerForm.name">
        <label for="email">Eメール</label>
        <input type="text" class="form__item" id="email" v-model="registerForm.email">
        <label for="password">パスワード</label>
        <input type="password" class="form__item" id="password" v-model="registerForm.password">
        <label for="password-confirmation">パスワード (確認用)</label>
        <input type="password" class="form__item" id="password-confirmation" v-model="registerForm.password_confirmation">
        <div class="form__button">
          <button type="submit" class="button button--inverse">登録</button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import { mapState } from 'vuex' // VuexのmapState関数

export default {
  data () {
    return {
      tab: 1,
      loginForm: {
        email: '',
        password: ''
      },
      registerForm: {
        name: '',
        email: '',
        password: '',
        password_confirmation: ''
      }
    }
  },
  computed: mapState({ // VuexのmapState関数。当算出プロパティとstore/auth.jsのステートを、ひも付け
    apiStatus: state => state.auth.apiStatus, // authモジュールのapiStatusステートを参照。システムエラーへの対応
    loginErrors: state => state.auth.loginErrorMessages, // バリデーションエラーの場合のエラーメッセージを参照
    registerErrors: state => state.auth.registerErrorMessages // バリデーションエラーの場合のエラーメッセージを参照
  }),
  methods: {
    async login () {
      // authストアのloginアクションを呼び出す

      // await で非同期なアクションの処理の完了（Promiseの解決）を待つ
      // this.$store でストアを参照。stores/index.js の Vue.use(Vuex) によって使える
      // dispatchメソッドでアクションを呼び出す。auth/loginでアクションを指定。store/auth.jsにて、namespaced: trueにしてあるため
      // 第二引数は、フォームの入力値
      await this.$store.dispatch('auth/login', this.loginForm)
      if (this.apiStatus) { // apiStatusがfalseなら、トップページへ移動しない
        // トップページに移動。this にルーターオブジェクトを表す $router が追加されている。router.jsにて、 Vue.use(VueRouter) と記述したため
        this.$router.push('/')
      }
    },
    async register () {
      // authストアのresigterアクションを呼び出す

      // await で非同期なアクションの処理の完了（Promiseの解決）を待つ
      // this.$store でストアを参照。stores/index.js の Vue.use(Vuex) によって使える
      // dispatchメソッドでアクションを呼び出す。auth/registerでアクションを指定。store/auth.jsにて、namespaced: trueにしてあるため
      // 第二引数は、フォームの入力値
      await this.$store.dispatch('auth/register', this.registerForm)
      if (this.apiStatus) { // apiStatusがfalseなら、トップページへ移動しない
        // トップページに移動。this にルーターオブジェクトを表す $router が追加されている。router.jsにて、 Vue.use(VueRouter) と記述したため
        this.$router.push('/')
      }
    },
    clearError () {
      this.$store.commit('auth/setLoginErrorMessages', null)
      this.$store.commit('auth/setRegisterErrorMessages', null)
    }
  },
  // ログインページを表示するタイミングで、createdライフサイクルフックを使い、ログイン時のバリデーションエラーをクリア
  created () {
    this.clearError()
  }
}
</script>