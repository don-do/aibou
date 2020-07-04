<template>
  <div class="p-loader">
    <p class="p-loading__text">
      <!-- 画面遷移時は以下のデフォルトの文言。PhotoForm.vueにこのLoader.vueを読み込むと「送信中...」の文言に。下の<slot>タグに上書く -->
      <slot>Loading...</slot>
    </p>
    <div class="p-loader__item p-loader__item--circle"></div>
  </div>
</template>