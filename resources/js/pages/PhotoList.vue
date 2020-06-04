<template>
  <div class="photo-list">
    <div class="grid">
      <!-- 写真の一覧データを、データの数だけ展開 -->
      <Photo

        class="grid__item"
        v-for="photo in photos"
        :key="photo.id"
        :item="photo"
        @praise="onPraiseClick"
        
      />
    </div>
    <!-- ページ送り表示 -->
    <Pagination :current-page="currentPage" :last-page="lastPage" />
  </div>
</template>

<script>
import { OK } from '../util'// cookieからvalueを返却するコード、ステータスコードをインポート
import Photo from '../components/Photo.vue' // <Photo> コンポーネントをインポート
import Pagination from '../components/Pagination.vue' // <Pagination> コンポーネントをインポート

export default {
  components: {
    Photo, // <Photo> コンポーネントを登録
    Pagination // <Pagination> コンポーネントを登録
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
      // v-forにて展開し一覧に出せなかったので、いったん断念。必要なら設計から見直す必要ありそう
      comments: [], // 写真一覧取得API呼び出し後、コメントデータを入れる
      currentPage: 0, // <Pagination> コンポーネントに渡すための、現在ページと総ページ数
      lastPage: 0
    }
  },
  methods: {
    async fetchPhotos () {
      const response = await axios.get(`/api/photos/?page=${this.page}`)

      if (response.status !== OK) {
        this.$store.commit('error/setCode', response.status)
        return false
      }
      this.photos = response.data.photos.data // レスポンスのJSON取得（response.data）後、photosの中の配列dataを取得
      // 以下コメント取得コード。v-forにて展開し一覧に出せなかったので、いったん断念。必要なら設計から見直す必要ありそう
      this.comments = response.data.comments.data // レスポンスのJSON取得（response.data）後、commentsの中の配列dataを取得
      // APIのレスポンスから「現在ページ」と「総ページ数」を取り出し、data変数に代入
      this.currentPage = response.data.photos.current_page
      this.lastPage = response.data.photos.last_page
    },
    onPraiseClick ({ id, praised }) {
      if (! this.$store.getters['auth/check']) {
        alert('グッジョブ機能を使うにはログインしてください。')
        return false
      }
      if (praised) {
        this.praiseless(id)
      } else {
        this.praise(id)
      }
    },
    async praise (id) {
      const response = await axios.put(`/api/photos/${id}/praise`)
      if (response.status !== OK) {
        this.$store.commit('error/setCode', response.status)
        return false
      }
      this.photos = this.photos.map(photo => {
        if (photo.id === response.data.photo_id) {
          photo.praises_count += 1
          photo.praised_by_user = true
        }
        return photo
      })
    },
    async praiseless (id) {
      const response = await axios.delete(`/api/photos/${id}/praise`)

      if (response.status !== OK) {
        this.$store.commit('error/setCode', response.status)
        return false
      }

      this.photos = this.photos.map(photo => {
        if (photo.id === response.data.photo_id) {
          photo.praises_count -= 1
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