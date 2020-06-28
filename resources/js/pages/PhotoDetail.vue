<template>
<!-- ルートエレメント。ローディングコンポーネントを追加して使用 -->
<div>

<div v-show="loading" class="photo-detail" style="display: inherit;">
    <!-- Loader.vueテンプレートが当て込まれ、ローディング画面が表示される -->
    <Loader></Loader>
</div>

  <!-- 写真クリック時に、写真の大きさを大きくし、コメント一覧パネルを下へ移動（flex-direction: column; とし、縦並びにする） -->
  <div
    v-if="photo"
    class="photo-detail"
    :class="{ 'photo-detail--column': fullWidth }"
  >
    <!-- 画像・報告者名 -->
    <!-- 写真クリック時に、写真の大きさを大きくし、コメント一覧パネルを下へ移動 -->
    <figure
      class="photo-detail__panel photo-detail__image"
      @click="fullWidth = ! fullWidth"
    >
      <img :src="photo.url" alt="">
      <figcaption>報告者： {{ photo.owner.name }}</figcaption>
    </figure>
    <!-- グッジョブボタン・ダウンロードボタン（aタグ）・コメント一覧（内容・投稿者） -->
    <div class="photo-detail__panel">
      <!-- グッジョブボタン。クリック時、当PhotoDetailコンポーネント内にてイベント発生 -->
      <button
        class="button button--praise"
        :class="{ 'button--praised': photo.praised_by_user }"
        title="Praise photo"
        @click="onPraiseClick"
      >
        <i class="icon ion-md-thumbs-up"></i>{{ photo.praises_count }}
      </button>
      <a
        :href="`/photos/${photo.id}/download`"
        class="button"
        title="Download photo"
      >
        <i class="icon ion-md-download"></i>ダウンロード
      </a>
      <h2 class="photo-detail__title">
        <i class="icon ion-md-people"></i>コメント
      </h2>
      <!-- コメントを表示 -->
      <ul v-if="photo.comments.length > 0" class="photo-detail__comments">
        <li
          v-for="comment in photo.comments"
          :key="comment.content"
          class="photo-detail__commentItem"
        >
          <p class="photo-detail__commentBody">
            {{ comment.content }}
          </p>
          <p class="photo-detail__commentInfo">
            {{ comment.author.name }}
          </p>
        </li>
      </ul>
      <!-- 報告時にコメント投稿するのでコメントは必ず存在するが、一応「コメントはありません」メッセージを置いておく -->
      <p v-else>まだ、コメントはありません。</p>
      <!-- ログインしていたら、コメント投稿できる -->
      <form v-if="isLogin" @submit.prevent="addComment" class="form">
        <!-- エラーメッセージ -->
        <div v-if="commentErrors" class="errors">
          <ul v-if="commentErrors.content">
            <li v-for="msg in commentErrors.content" :key="msg">{{ msg }}</li>
          </ul>
        </div>
        <!-- 入力欄 -->
        <textarea class="form__item" v-model="commentContent"></textarea>
        <div class="form__button">
          <button type="submit" class="button button--inverse">コメントを送信</button>
        </div>
      </form>
    </div>
  </div>

</div>

</template>

<script>
import { OK, CREATED, UNPROCESSABLE_ENTITY } from '../util'

// Loaderコンポーネントをインポート
import Loader from '../components/Loader.vue'

export default {

components: {
  // Loaderコンポーネントを登録
  Loader
},

  props: {
    id: { // router.jsから、/photos/:idの :id の部分に入る値がpropsとして渡ってくる
      type: String,
      required: true
    }
  },
  data () {
    return {
      photo: null, // 写真取得API呼び出し後、写真データを入れる
      fullWidth: false, // 写真クリック時に、写真の大きさを大きくし、コメント一覧パネルを下へ移動
      commentContent: '', // コメント <textarea> 入力値を参照
      commentErrors: null,

loading: false // ローディングを表示させるかどうか

    }
  },
  computed: {
    isLogin () { // ストアのゲッターを参照
      return this.$store.getters['auth/check']
    }
  },
  methods: {
    async fetchPhoto () {

// ローディングを表示
this.loading = true

      const response = await axios.get(`/api/photos/${this.id}`)

      if (response.status !== OK) {
        this.$store.commit('error/setCode', response.status)
        return false
      }

// 通信が終わったら、ローディングを非表示
this.loading = false

      this.photo = response.data // レスポンスのJSON取得（response.data）
    },
    async addComment () {

      const response = await axios.post(`/api/photos/${this.id}/comments`, {
        content: this.commentContent
      })

      // バリデーションエラー
      if (response.status === UNPROCESSABLE_ENTITY) {
        this.commentErrors = response.data.errors
        return false
      }

      this.commentContent = ''
      // エラーメッセージをクリア
      this.commentErrors = null

      // その他のエラー
      if (response.status !== CREATED) {
        this.$store.commit('error/setCode', response.status)
        return false
      }
      // 投稿してすぐに、コメントを表示。　レスポンスデータを挿入
      this.photo.comments = [
        response.data,
        ...this.photo.comments // オブジェクトを展開して、配列に追加
      ]
    },
    // グッジョブボタンクリックイベント発生時
    onPraiseClick () {
      // ログイン状態でないなら、ログインを促すアラート表示
      if (! this.isLogin) {
        alert('グッジョブ機能を使うにはログインしてください。')
        return false
      }
      // 自分の投稿にはグッジョブできない。画像の投稿者とログインユーザーが一致するなら、グッジョブ機能が使えない旨のアラートを表示
      if ( this.photo.owner.name === this.$store.getters['auth/username']) {
        alert('自身の投稿には、グッジョブ機能を使えません。')
        return false
      }

      // グッジョブがついているなら、解除
      if (this.photo.praised_by_user) {
        this.praiseless()
      } else { //グッジョブがついていないなら、付与
        this.praise()
      }
    },
    async praise () {
      // グッジョブ付与。APIへの通信
      const response = await axios.put(`/api/photos/${this.id}/praise`)

      if (response.status !== OK) {
        this.$store.commit('error/setCode', response.status)
        return false
      }
      // グッジョブ数を増やす
      this.photo.praises_count = this.photo.praises_count + 1
      // 見た目を変更
      this.photo.praised_by_user = true
    },
    async praiseless () {
      // グッジョブ削除。APIへの通信
      const response = await axios.delete(`/api/photos/${this.id}/praise`)

      if (response.status !== OK) {
        this.$store.commit('error/setCode', response.status)
        return false
      }
      // グッジョブ数を減らす
      this.photo.praises_count = this.photo.praises_count - 1
      // 見た目を戻す
      this.photo.praised_by_user = false
    }
  },
  watch: {
    // ページの切り替わり時に、fetchPhoto()を実行。$routeを監視
    $route: {
      async handler () {
        await this.fetchPhoto()
      },
      // コンポーネント生成時、fetchPhoto()を実行
      immediate: true
    }
  }
}
</script>