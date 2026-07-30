<template>
  <div class="bg-white">
    <!-- Breadcrumb -->
    <div class="max-w-5xl mx-auto px-6 pt-8 text-sm text-gray-400 flex items-center gap-2">
      <router-link to="/" class="hover:text-[#0F2A4A] transition-colors">Beranda</router-link>
      <span>/</span>
      <span class="text-gray-500">Profil</span>
      <span>/</span>
      <span class="text-[#0F2A4A] font-medium">Visi</span>
    </div>

    <!-- ============ HERO: Pernyataan Visi ============ -->
    <section ref="heroRef" class="max-w-5xl mx-auto px-6 pt-10 pb-20">
      <p class="text-[18px] font-bold tracking-[0.25em] text-[#C9A227] uppercase mb-5">
        Visi LPMU UNWIKU
      </p>

      <!-- Tipografi editorial: bagian penting ditonjolkan, kata penghubung diredam -->
      <p class="font-poppins leading-[1.35] text-2xl sm:text-3xl md:text-[2.6rem] text-center max-w-4xl mx-auto">
        <span class="text-gray-400 font-normal">Menjadi lembaga yang </span>
        <span class="text-[#0F2A4A] font-bold">menjamin mutu penyelenggaraan pendidikan</span>
        <span class="text-gray-400 font-normal"> di UNWIKU, menuju terwujudnya UNWIKU sebagai </span>
        <span class="text-[#0F2A4A] font-bold">entrepreneur university</span>
        <span class="text-gray-400 font-normal">, untuk menghasilkan lulusan yang </span>
        <span class="text-[#0F2A4A] font-bold">mandiri, inovatif, kompetitif, dan berjiwa Pancasila</span>
        <span class="text-gray-400 font-normal">.</span>
      </p>
    </section>

    <!-- ============ TARGET 2030 ============ -->
    <section class="bg-[#0F2A4A] py-16 px-6 mb-24">
      <div class="max-w-5xl mx-auto grid grid-cols-1 md:grid-cols-[auto_1fr] gap-8 md:gap-14 items-center">
        <div class="flex items-baseline gap-1 shrink-0">
          <span class="font-poppins font-extrabold text-[#C9A227] text-7xl md:text-8xl leading-none tracking-tight">
            2030
          </span>
        </div>
        <div class="border-t md:border-t-0 md:border-l border-white/15 pt-6 md:pt-0 md:pl-14">
          <p class="text-[11px] font-bold tracking-[0.2em] text-white/50 uppercase mb-2">
            arget Pencapaian
          </p>
          <p class="text-white/85 leading-relaxed max-w-md">
            Merupakan tahun tenggat yang menjadi acuan kerja LPMU dalam menjaga mutu secara
            bertahap dan terukur, melalui siklus
            <router-link to="/spmi/penetapan" class="text-[#C9A227] font-medium hover:underline">
              Penetapan, Pelaksanaan, Evaluasi, Pengendalian, dan Peningkatan
            </router-link>
            standar mutu di seluruh unit UNWIKU.
          </p>
        </div>
      </div>
    </section>

  </div>
</template>

<script setup>
defineOptions({ name: 'VisiPage' });

import { ref } from 'vue';
</script>

<style scoped>
.font-poppins {
  font-family: 'Poppins', sans-serif;
}

.trait-card {
  background: white;
  border: 1px solid rgba(15, 42, 74, 0.08);
  border-radius: 1rem;
  padding: 1.5rem;
  opacity: 0;
  transform: translateY(14px);
  transition: opacity 0.5s ease, transform 0.5s ease;
}
.trait-card.trait-visible {
  opacity: 1;
  transform: translateY(0);
}
.trait-card[data-delay="1"] { transition-delay: 0.08s; }
.trait-card[data-delay="2"] { transition-delay: 0.16s; }
.trait-card[data-delay="3"] { transition-delay: 0.24s; }

@media (min-width: 768px) {
  .trait-card:nth-of-type(1) { grid-area: mandiri; }
}

@media (prefers-reduced-motion: reduce) {
  .trait-card {
    transition: none !important;
    opacity: 1;
    transform: none;
  }
}
</style>