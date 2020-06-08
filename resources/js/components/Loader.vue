<template>
  <div class="loader">
    <p class="loading__text">
      <!-- PhotoForm.vueテンプレートの「送信中...」の文言を表示。<slot>タグを上書き -->
      <slot>Loading...</slot>
    </p>
    <!-- <div></div>の部分がローディングハートの場合の下部。divタグ内に擬似要素（::beforeハート左上、::afterハート右上） -->
    <div class="loader__item loader__item--circle"><div></div></div>
  </div>
</template>