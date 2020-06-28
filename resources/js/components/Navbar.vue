<template>
  <nav class="navbar">
    <!-- Vue Router の管理下にあるページへ遷移。<a>要素を描画。サーバサイドへのGETリクエストを発生させない -->
    <RouterLink class="navbar__brand" to="/">
    <img :src="'/images/Aibou-logo.png'">
    　~ 同じ目線で成長する。現場共有アプリ ~
    </RouterLink>
    <!-- フォームの外枠をクリックすると、フォームが閉じる -->
    <div class="root" ref="elRoot">
      <div class="navbar__menu">
        <div v-if="isLogin" class="navbar__item">
          <button class="button button__report" @click="showForm = ! showForm">
            <i class="icon ion-md-add"></i>
            報告へ / 中止する
          </button>
        </div>
        <span v-if="isLogin" class="navbar__item">
          {{ username }}
        </span>
        <div v-else class="navbar__item">
          <RouterLink class="button button--link button__LoginRegister--color" to="/login">
            ログイン / 登録
          </RouterLink>
        </div>
      <PhotoForm v-model="showForm" />
      </div>
    </div>
  </nav>
</template>

<script>
import PhotoForm from './PhotoForm.vue'
export default {
  components: {
    PhotoForm
  },
  data () {
    return {
      showForm: false
    }
  },
  computed: {
    // 冗長な記述を避けるため、直接テンプレートで$storeを参照するのではなく、computedからストアのゲッターを参照
    isLogin () {
      return this.$store.getters['auth/check']
    },
    username () {
      return this.$store.getters['auth/username']
    }
  },
  mounted() { // フォームの外枠をクリックすると、フォームを閉じる
    // windowにイベントリスナーをセット
    window.addEventListener('click', this._onBlurHandler = (event) => {
      // フォームをクリックしても、何も操作しない。targetがコンポーネントの中に含まれているものならreturn
      console.log(this.$refs.elRoot.contains(event.target));
      if (this.$refs.elRoot.contains(event.target)) {
        return;
      }
      this.$data.showForm = false;
    });
  },
  beforeDestroy() {
    // コンポーネントが破棄されるタイミングにイベントリスナーも消去
    window.removeEventListener('click', this._onBlurHandler);
  }
}
</script>