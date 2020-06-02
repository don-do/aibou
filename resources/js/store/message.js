const state = {
  content: ''
}

const mutations = {
  // PhotoForm.vueにて投稿完了後、メッセージ登録。当message.jsファイルのcontentを更新
  setContent (state, { content, timeout }) {
    state.content = content

    if (typeof timeout === 'undefined') {
      timeout = 3000
    }

    setTimeout(() => (state.content = ''), timeout)
  }
}

export default {
  namespaced: true,
  state,
  mutations
}
