<!-- <Pagination> コンポーネントは使い回さないが、プログラムの見通しのため意味のあるUIのまとまりとして分割 -->
<template>
  <div class="pagination">
    <!-- 1ページ目以外の場合、「prev（前へ）」ボタン表示 -->
    <RouterLink
      v-if="! isFirstPage"
      :to="`/?page=${currentPage - 1}`"
      class="button"
    >&laquo; prev</RouterLink>
    <!-- 最後のページ以外の場合、「next（次へ）」ボタン表示 -->
    <RouterLink
      v-if="! isLastPage"
      :to="`/?page=${currentPage + 1}`"
      class="button"
    >next &raquo;</RouterLink>
  </div>
</template>

<script>
export default {
  props: {
    currentPage: {
      type: Number,
      required: true
    },
    lastPage: {
      type: Number,
      required: true
    }
  },
  computed: {
    isFirstPage () {
      return this.currentPage === 1
    },
    isLastPage () {
      return this.currentPage === this.lastPage
    }
  }
}
</script>