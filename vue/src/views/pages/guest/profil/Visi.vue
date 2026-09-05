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
      <p class="text-[15px] font-bold tracking-[0.25em] text-[#C9A227] uppercase mb-7 text-center">
        Visi LPMU UNWIKU
      </p>

      <div class="max-w-4xl mx-auto flex flex-col items-center text-center">
        <span class="visi-quote" aria-hidden="true">&ldquo;</span>

        <!-- Tipografi editorial: bagian penting ditonjolkan, kata penghubung diredam -->
        <p class="font-poppins leading-[1.35] text-2xl sm:text-3xl md:text-[2.6rem] text-balance">
          <span class="text-gray-400 font-normal">Menjadi lembaga yang </span>
          <span class="text-[#0F2A4A] font-bold">menjamin mutu penyelenggaraan pendidikan</span>
          <span class="text-gray-400 font-normal">
            di UNWIKU, menuju terwujudnya UNWIKU sebagai
          </span>
          <span class="text-[#0F2A4A] font-bold">entrepreneur university</span>
          <span class="text-gray-400 font-normal">, untuk menghasilkan lulusan yang </span>
          <span class="text-[#0F2A4A] font-bold"
            >mandiri, inovatif, kompetitif, dan berjiwa Pancasila</span
          >
          <span class="text-gray-400 font-normal">.</span>
        </p>

        <span class="visi-quote visi-quote--close" aria-hidden="true">&rdquo;</span>
      </div>
    </section>

    <!-- ============ TARGET 2030 ============ -->
    <section class="bg-[#0F2A4A] py-16 px-6 mb-24">
      <div
        class="max-w-5xl mx-auto grid grid-cols-1 md:grid-cols-[auto_1fr] gap-8 md:gap-14 items-center"
      >
        <div class="flex items-baseline gap-1 shrink-0">
          <span
            class="font-poppins font-extrabold text-[#C9A227] text-7xl md:text-8xl leading-none tracking-tight"
          >
            2030
          </span>
        </div>
        <div class="border-t md:border-t-0 md:border-l border-white/15 pt-6 md:pt-0 md:pl-14">
          <p class="text-[11px] font-bold tracking-[0.2em] text-white/50 uppercase mb-2">
            Target Pencapaian
          </p>
          <p class="text-white/85 leading-relaxed max-w-md">
            Merupakan tahun tenggat yang menjadi acuan kerja LPMU dalam menjaga mutu secara bertahap
            dan terukur, melalui siklus
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
defineOptions({ name: 'VisiPage' })
</script>

<style scoped>
.font-poppins {
  font-family: 'Poppins', sans-serif;
}

.visi-quote {
  display: block;
  font-family: Georgia, 'Times New Roman', serif;
  font-weight: 700;
  font-size: 3.5rem;
  line-height: 0.5;
  color: #2563eb;
  opacity: 0.4;
  margin-bottom: 1.25rem;
}
.visi-quote--close {
  margin-bottom: 0;
  margin-top: 1.25rem;
}

@media (max-width: 640px) {
  .visi-quote {
    font-size: 2.5rem;
    margin-bottom: 0.75rem;
  }
  .visi-quote--close {
    margin-bottom: 0;
    margin-top: 0.75rem;
  }
}
</style>
