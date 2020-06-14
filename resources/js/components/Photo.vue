<template>
  <div class="photo">
    <figure class="photo__wrapper">
      <img
        class="photo__image"
        :src="item.url"
        :alt="`Photo by ${item.owner.name}`"
      >
    </figure>
    <!-- マウスオーバー時に表示。詳細ページへのリンク。Vue Routerによるハンドリング -->
    <RouterLink
      class="photo__overlay"
      :to="`/photos/${item.id}`"
      :title="`View the photo by ${item.owner.name}`"
    >
      <div class="photo__controls">
        <!-- グッジョブボタン。クリック時、親のPhotoListコンポーネントに通知 -->
        <button
          class="photo__action photo__action--praise"
          :class="{ 'photo__action--praised': item.praised_by_user }"
          title="Praise photo"
          @click.prevent="praise"
        >
          <i class="icon ion-md-thumbs-up"></i>{{ item.praises_count }}
        </button>
        <!-- ダウンロードボタン（リンク）。サーバーへ、GETリクエスト送信 -->
        <!-- イベント伝播を抑止。「DLリンク」クリック時、親要素の「詳細ページのリンク」へのバブリング防止 -->
        <a
          class="photo__action"
          title="Download photo"
          @click.stop
          :href="`/photos/${item.id}/download`"
        >
          <i class="icon ion-md-download"></i>ダウンロード
        </a>
      </div>
    </RouterLink>
    <!-- 投稿者名を出力 -->
    <div class="photo__username">
      {{ item.owner.name }}
    </div>
  </div>
</template>

<script>
export default {
  props: { // 一つ分の写真データ item のpropsを受け取る
    item: {
      type: Object,
      required: true
    }
  },
  methods: {
    // グッジョブボタン。クリック時、親のPhotoListコンポーネントに通知
    praise () {
      this.$emit('praise', {
        id: this.item.id,
        praised: this.item.praised_by_user,
        ownerName: this.item.owner.name
      })
    }
  }
}
</script>