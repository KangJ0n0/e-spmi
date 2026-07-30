<template>
  <div class="bg-white">
    <!-- ============ BREADCRUMB ============ -->
    <div class="max-w-6xl mx-auto px-6 pt-8 text-sm text-gray-400 flex items-center gap-2">
      <router-link to="/" class="hover:text-[#0F2A4A] transition-colors">Beranda</router-link>
      <span>/</span>
      <span class="text-gray-500">Profil</span>
      <span>/</span>
      <span class="text-[#0F2A4A] font-medium">Tugas & Fungsi</span>
    </div>

    <!-- ============ HERO SECTION ============ -->
    <section class="max-w-6xl mx-auto px-6 pt-10 pb-16">
      <div class="text-center max-w-3xl mx-auto">
        <p class="text-[16px] md:text-[18px] font-bold tracking-[0.25em] text-[#C9A227] uppercase mb-4">
          Tugas & Fungsi Utama
        </p>
        <h1 class="font-poppins text-3xl md:text-5xl font-bold text-[#0F2A4A] leading-tight mb-8">
          Menjaga Mutu Berkelanjutan
        </h1>
        <p class="text-gray-600 text-lg leading-relaxed">
          Lembaga Penjaminan Mutu (LPMU) mempunyai tugas krusial dalam 
          <span class="text-[#0F2A4A] font-bold">memastikan terlaksananya siklus Sistem Penjaminan Mutu Internal (SPMI)</span> 
          secara konsisten dan terukur di seluruh unit Universitas.
        </p>
      </div>
    </section>

  
   

  </div>
</template>

<script setup>
defineOptions({ name: 'TugasFungsiPage' });
</script>

<style scoped>
.font-poppins {
  font-family: 'Poppins', sans-serif;
}
</style>