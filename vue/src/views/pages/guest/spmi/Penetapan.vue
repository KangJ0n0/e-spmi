<template>
  <div class="bg-white font-poppins min-h-[60vh]">
    <!-- Breadcrumb -->
    <div class="max-w-5xl mx-auto px-6 pt-8 text-sm text-gray-400 flex items-center gap-2">
      <router-link to="/" class="hover:text-[#0F2A4A] transition-colors">Beranda</router-link>
      <span>/</span>
      <span class="text-gray-500">SPMI</span>
      <span>/</span>
      <span class="text-[#0F2A4A] font-medium">Penetapan</span>
    </div>

    <!-- Konten -->
    <section class="max-w-5xl mx-auto px-6 py-20">
      <p class="text-[11px] font-bold tracking-[0.25em] text-[#C9A227] uppercase mb-4">
        SPMI &middot; Penetapan
      </p>
      <h1 class="font-poppins font-bold text-3xl md:text-4xl text-[#0F2A4A] mb-8">
        Dokumen Mutu dan Perangkat SPMI
      </h1>

      <div class="flex flex-col gap-4">
        <a
          href="https://drive.google.com/drive/folders/1MtBeBfK-MF-C32M44X8yk40z3L0maSFs"
          target="_blank"
          rel="noopener noreferrer"
          class="inline-flex items-center gap-2 bg-[#0F2A4A] text-white text-sm font-semibold px-6 py-3 rounded-full hover:bg-[#16386b] transition-colors w-fit"
        >
          Buka Dokumen Mutu
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75L6.75 17.25M6.75 6.75h10.5v10.5" />
          </svg>
        </a>
        <a
          href="https://drive.google.com/drive/folders/1DoHC2yTjw5afwOYgYAY4hB_U27TapLKC?usp=sharing"
          target="_blank"
          rel="noopener noreferrer"
          class="inline-flex items-center gap-2 bg-[#0F2A4A] text-white text-sm font-semibold px-6 py-3 rounded-full hover:bg-[#16386b] transition-colors w-fit"
        >
          Buka Perangkat SPMI
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75L6.75 17.25M6.75 6.75h10.5v10.5" />
          </svg>
        </a>
      </div>
    </section>
  </div>
</template>

<script setup>
defineOptions({ name: 'PenetapanPage' });
</script>

<style scoped>
.font-poppins {
  font-family: 'Poppins', sans-serif;
}
</style>