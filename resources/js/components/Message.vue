<template>
  <div class="u-message" v-show="message">
    {{ message }}
  </div>
</template>

<script>
  import { mapState } from 'vuex'

  export default {
    // message.jsファイルのcontentの更新を参照。値があればメッセージを表示
    // PhotoForm.vueにて投稿完了後、メッセージ登録時にcontentは更新される
    computed: {
      ...mapState({
        message: state => state.message.content
      })
    }
  }
</script>