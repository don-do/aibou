<template>
<!-- ルートエレメント。ローディングコンポーネントを追加して使用 -->
<div>

<div v-show="loading" class="p-photo-detail" style="display: inherit;">
    <!-- Loader.vueテンプレートが当て込まれ、ローディング画面が表示される -->
    <Loader></Loader>
</div>

  <div class="photo-list">
    <div class="c-grid">
      <!-- 写真の一覧データを、データの数だけ展開 -->
      <!-- Photoコンポーネントから、イベントを受け取る -->
      <Photo
        class="c-grid__item"
        v-for="photo in photos"
        :key="photo.id"
        :item="photo"
        @praise="onPraiseClick"
      />
    </div>
    <!-- ページ送り表示 -->
    <Pagination :current-page="currentPage" :last-page="lastPage" />
  </div>

</div>

</template>

<script>
import { OK } from '../util'// cookieからvalueを返却するコード、ステータスコードをインポート
import Photo from '../components/Photo.vue' // <Photo> コンポーネントをインポート
import Pagination from '../components/Pagination.vue' // <Pagination> コンポーネントをインポート

// Loaderコンポーネントをインポート
import Loader from '../components/Loader.vue'

export default {
  components: {
    Photo, // <Photo> コンポーネントを登録
    Pagination, // <Pagination> コンポーネントを登録

  // Loaderコンポーネントを登録
  Loader

  },
  props: { // router.jsから渡される、pageプロパティを受け取る
    page: {
      type: Number,
      required: false,
      default: 1
    }
  },
  data () {
    return {
      photos: [], // 写真一覧取得API呼び出し後、写真一覧データを入れる
      // コメントをv-forにて展開し一覧に出せず。配列が複数となるため。必要なら設計から見直す必要ありそう
      // comments: [], // 写真一覧取得API呼び出し後、コメントデータを入れる
      currentPage: 0, // <Pagination> コンポーネントに渡すための、現在ページと総ページ数
      lastPage: 0,

loading: false // ローディングを表示させるかどうか

    }
  },
  methods: {
    async fetchPhotos () {

// ローディングを表示
this.loading = true

      const response = await axios.get(`/api/photos/?page=${this.page}`)

      if (response.status !== OK) {
        this.$store.commit('error/setCode', response.status)
        return false
      }

// 通信が終わったら、ローディングを非表示
this.loading = false

      this.photos = response.data.photos.data // レスポンスのJSON取得（response.data）後、photosの中の配列dataを取得
      // 以下コメント取得コード。v-forにて展開し一覧に出せず。配列が複数となるため。必要なら設計から見直す必要ありそう
      // this.comments = response.data.comments.data // レスポンスのJSON取得（response.data）後、commentsの中の配列dataを取得
      // APIのレスポンスから「現在ページ」と「総ページ数」を取り出し、data変数に代入
      this.currentPage = response.data.photos.current_page
      this.lastPage = response.data.photos.last_page
    },
    // Photoコンポーネントから、イベントを受け取ったあとの処理
    onPraiseClick ({ id, praised, ownerName }) {
      // ログイン状態でないなら、ログインを促すアラート表示
      if (! this.$store.getters['auth/check']) {
        alert('グッジョブ機能を使うにはログインしてください。')
        return false
      }
      // 自分の投稿にはグッジョブできない。画像の投稿者とログインユーザーが一致するなら、グッジョブ機能が使えない旨のアラートを表示
      if ( ownerName === this.$store.getters['auth/username']) {
        alert('自身の投稿には、グッジョブ機能を使えません。')
        return false
      }

      // グッジョブがついているなら、解除
      if (praised) {
        this.praiseless(id)
      } else { //グッジョブがついていないなら、付与
        this.praise(id)
      }
    },
    async praise (id) {
      // グッジョブ付与。APIへの通信
      const response = await axios.put(`/api/photos/${id}/praise`)

      if (response.status !== OK) {
        this.$store.commit('error/setCode', response.status)
        return false
      }

      this.photos = this.photos.map(photo => {
        // 写真のidと、レスポンスのphoto_idが一致するもの
        if (photo.id === response.data.photo_id) {
          // グッジョブ数を増やす
          photo.praises_count += 1
          // 見た目を変更
          photo.praised_by_user = true
        }
        return photo
      })
    },
    async praiseless (id) {
      // グッジョブ削除。APIへの通信
      const response = await axios.delete(`/api/photos/${id}/praise`)

      if (response.status !== OK) {
        this.$store.commit('error/setCode', response.status)
        return false
      }

      this.photos = this.photos.map(photo => {
        // 写真のidと、レスポンスのphoto_idが一致するもの
        if (photo.id === response.data.photo_id) {
          // グッジョブ数を減らす
          photo.praises_count -= 1
          // 見た目を戻す
          photo.praised_by_user = false
        }
        return photo
      })
    }
  },
  watch: {
    // ページの切り替わり時に、fetchPhotos()を実行。$routeを監視
    // 一覧のコンポーネント内の2ページ目では、created()では作動しないため、ルーティングで操作
    $route: {
      async handler () {
        await this.fetchPhotos()
      },
      // コンポーネント生成時、fetchPhotos()を実行
      immediate: true
    }
  }
}
</script>