<template>
  <!-- Yandex.Metrika noscript fallback -->
  <noscript>
    <div>
      <img src="https://mc.yandex.ru/watch/103481171" style="position:absolute; left:-9999px;" alt="" />
    </div>
  </noscript>
</template>

<script setup>
import { onMounted } from 'vue';

onMounted(() => {
  // Проверяем, не загружена ли уже Яндекс.Метрика
  if (window.ym && window.ym[103481171]) {
    return;
  }

  // Создаем функцию для Яндекс.Метрики
  (function(m,e,t,r,i,k,a){
      m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
      m[i].l=1*new Date();
      for (var j = 0; j < document.scripts.length; j++) {
        if (document.scripts[j].src === r) { return; }
      }
      k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)
  })(window, document,'script','https://mc.yandex.ru/metrika/tag.js?id=103481171', 'ym');

  // Инициализируем счетчик
  window.ym(103481171, 'init', {
    ssr: true,
    webvisor: true,
    clickmap: true,
    ecommerce: "dataLayer",
    accurateTrackBounce: true,
    trackLinks: true
  });
});
</script> 