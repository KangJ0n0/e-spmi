<template>
  <div class="bg-white">
    <!-- ============ BREADCRUMB ============ -->
    <div class="max-w-6xl mx-auto px-6 pt-8 text-sm text-gray-400 flex items-center gap-2">
      <router-link to="/" class="hover:text-[#0F2A4A] transition-colors">Beranda</router-link>
      <span>/</span>
      <span class="text-[#0F2A4A] font-medium">SPME</span>
    </div>

    <!-- ============ HERO SECTION ============ -->
    <section class="max-w-6xl mx-auto px-6 pt-10 pb-24">
      <div class="text-center max-w-3xl mx-auto">
        <p
          class="text-[16px] md:text-[18px] font-bold tracking-[0.25em] text-[#C9A227] uppercase mb-4"
        >
          UPMF
        </p>
        <h1 class="font-poppins text-2xl md:text-4xl font-bold text-[#0F2A4A] leading-tight mb-8">
          Halaman dalam pengembangan
        </h1>
        <p class="text-gray-600 text-lg leading-relaxed">
          Konten Unit Penjaminan Mutu Fakultas segera hadir.
        </p>
      </div>
    </section>
  </div>
</template>

<script setup>
defineOptions({ name: 'SpmePage' })
</script>

<style scoped>
.font-poppins {
  font-family: 'Poppins', sans-serif;
}
</style>
