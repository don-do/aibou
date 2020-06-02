<template>
  <div v-show="value" class="photo-form">
    <h2 class="title">報告へ</h2>
    <div v-show="loading" class="panel">
      <Loader>送信中...</Loader>
    </div>
    <form v-show="! loading" class="form" @submit.prevent="submit"> <!-- デフォルトアクション（ブラウザ本来の挙動）のみ停止 -->
      <div class="errors" v-if="errors">
        <ul v-if="errors.photo">
          <li v-for="msg in errors.photo" :key="msg">{{ msg }}</li>
        </ul>
      </div>
      <input class="form__item" type="file" @change="onFileChange">
      <output class="form__output" v-if="preview">
        <img :src="preview" alt="">
      </output>

<div v-if="commentErrors" class="errors">
  <ul v-if="commentErrors.content">
    <li v-for="msg in commentErrors.content" :key="msg">{{ msg }}</li>
  </ul>
</div>
<textarea class="form__item" v-model="commentContent"></textarea>

      <div class="form__button">
        <button type="submit" class="button button--inverse">送信</button>
      </div>
    </form>
  </div>
</template>

<script>
// レスポンスコードの定義をインポート。エラー処理のため
import { CREATED, UNPROCESSABLE_ENTITY } from '../util'
// Loaderコンポーネントをインポート
import Loader from './Loader.vue'

export default {
  components: {
    // Loaderコンポーネントを登録
    Loader
  },
  props: {
    value: {
      type: Boolean,
      required: true
    }
  },
  data () {
    return {
      loading: false, // ローディングを表示させるかどうか
      preview: null,
      photo: null, // 選択中のファイルを格納

commentContent: '', // 画像の説明

      errors: null // エラーメッセージを格納

,
commentErrors: null

    }
  },
  methods: {
    // フォームでファイルが選択されたら実行
    onFileChange (event) {
      // 何も選択されていなかったら処理中断
      if (event.target.files.length === 0) {
        this.reset() // 入力欄の値とプレビュー表示をクリア
        return false
      }

      // ファイルが画像ではなかったら処理中断
      if (! event.target.files[0].type.match('image.*')) {
        this.reset() // 入力欄の値とプレビュー表示をクリア
        return false
      }

      // FileReaderクラスのインスタンスを取得
      const reader = new FileReader()

      // 画像ファイルを読み込み終わったタイミングで実行する処理
      reader.onload = e => {
        // previewに読み込み結果（データURL）を代入
        // previewに値が入ると<output>につけたv-ifがtrueと判定される
        // また<output>内部の<img>のsrc属性は、previewの値を参照しているので
        // 結果として画像が表示される
        this.preview = e.target.result
      }

      // 画像ファイルを読み込む
      // 読み込まれたファイルはデータURL形式で受け取れる（その後の処理は、上記onloadへ）
      reader.readAsDataURL(event.target.files[0])

      this.photo = event.target.files[0] // 画像ファイルを代入
    },
    // 入力欄の値とプレビュー表示をクリアするメソッド
    reset () {
      this.preview = ''
      this.photo = null

this.commentContent = '' // 画像投稿と同時に投稿した、画像説明コメント

      // this.$elは、コンポーネントそのもののDOM要素
      this.$el.querySelector('input[type="file"]').value = null

this.errors = '' // バリデーションエラー後、送信成功したら、エラーメッセージを消去
this.commentErrors = '' // バリデーションエラー後、送信成功したら、エラーメッセージを消去

    },
    async submit () {
      // ローディングを表示
      this.loading = true

      const formData = new FormData()// FormData APIを使用。Ajaxでファイルを送るため
      formData.append('photo', this.photo)
      // LaravelでAPIを作成する場合、routes/api.phpにルートを記載するが、
      // その場合、APIにアクセスする側は/api/article/1のようにルートの先頭にapiをつける必要がある
      const response = await axios.post('/api/photos', formData)

// 画像投稿後、作成した画像の詳細画面に遷移するため、URL用のidを生成
// const id = response.data.url.substr( 9, 12 );
// ここに ↑ のコードを置いてファイル未選択送信すると未送信のためid発行されない ↓ のエラー。reset()直前にコードを移した
// [Vue warn]: Error in v-on handler (Promise/async): "TypeError: Cannot read property 'substr' of undefined"

      // 通信が終わったら、ローディングを非表示
      this.loading = false

      // 処理中断後に、バリデーションチェック。エラーメッセージを表示させるため
      if (response.status === UNPROCESSABLE_ENTITY) {
        this.errors = response.data.errors
        return false
      }

// 画像投稿後、作成した画像の詳細画面に遷移するため、URL用のidを生成
const id = response.data.url.substr( 9, 12 );
// 画像投稿と同時に、画像説明コメント投稿
const responseComment = await axios.post(`/api/photos/${id}/comments`, {
  content: this.commentContent
})
// 画像説明コメントバリデーションエラー
if (responseComment.status === UNPROCESSABLE_ENTITY) {
  this.commentErrors = responseComment.data.errors
  return false
}

      this.reset()
      // 自動的にフォームが閉じるよう、inputイベントを発行。それに伴い、falseを発行
      this.$emit('input', false)

      if (response.status !== CREATED) {
        this.$store.commit('error/setCode', response.status)
        return false
      }

      // 投稿完了後、メッセージ登録。message.jsモジュールのcontentを更新
      this.$store.commit('message/setContent', {
        content: '写真が投稿されました！',
        timeout: 4000
      })

      // 画像投稿後、作成した画像の詳細画面に遷移
      this.$router.push(`/photos/${id}`)
    }
  }
}
</script>