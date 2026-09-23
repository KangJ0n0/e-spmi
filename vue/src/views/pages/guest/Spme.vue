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
          Penjaminan Mutu
        </p>
        <h1 class="font-poppins text-3xl md:text-5xl font-bold text-[#0F2A4A] leading-tight mb-8">
          SPME
        </h1>
        <p class="text-gray-600 text-lg leading-relaxed">
          Halaman Sistem Penjaminan Mutu Eksternal (SPME) sedang dalam penyusunan konten.
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
