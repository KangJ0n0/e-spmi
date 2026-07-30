<template>
  <div class="bg-white font-poppins">
    <!-- ============ HERO ============ -->
    <section
      class="relative h-[520px] md:h-[600px] bg-cover bg-center flex items-end overflow-hidden"
      style="background-image: url('https://unwiku.ac.id/wp-content/uploads/2025/02/Drone-luas-minimize.jpg')"
    >
      <div class="absolute inset-0 bg-gradient-to-t from-[#081B33] via-[#0F2A4A]/70 to-[#0F2A4A]/20"></div>

      <div class="relative max-w-5xl mx-auto px-6 pb-16 md:pb-20 w-full">
        <p class="text-[11px] font-bold tracking-[0.25em] text-[#C9A227] uppercase mb-4">
          LPMU &middot; Universitas Wijayakusuma Purwokerto
        </p>
        <h1 class="font-poppins font-bold text-white text-3xl md:text-5xl leading-tight max-w-2xl mb-5">
          Mutu yang terjaga, langkah yang terukur.
        </h1>
        <p class="text-white/75 leading-relaxed max-w-xl mb-8">
          LPMU menjamin mutu akademik dan non-akademik UNWIKU melalui siklus
          Penetapan, Pelaksanaan, Evaluasi, Pengendalian, dan Peningkatan
          &mdash; langkah nyata menuju UNWIKU sebagai
          <em class="not-italic text-white/90 font-medium">entrepreneur university</em> pada 2030.
        </p>

        <div class="flex flex-wrap items-center gap-3">
          <router-link
            to="/spmi/penetapan"
            class="inline-flex items-center gap-2 bg-[#C9A227] text-[#0F2A4A] font-semibold text-sm px-5 py-3 rounded-full hover:bg-[#dab449] transition-colors"
          >
            Jelajahi Siklus SPMI
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
            </svg>
          </router-link>
          <router-link
            to="/profil/visi"
            class="inline-flex items-center gap-2 text-white font-medium text-sm px-5 py-3 rounded-full border border-white/30 hover:border-white/60 hover:bg-white/5 transition-colors"
          >
            Kenali LPMU
          </router-link>
        </div>
      </div>
    </section>

    <!-- ============ JELAJAHI ============ -->
    <section ref="exploreRef" class="max-w-5xl mx-auto px-6 py-16">
      <div
        class="grid grid-cols-2 md:grid-cols-4 gap-4 explore-grid"
        :class="{ 'explore-visible': exploreInView }"
      >
        <router-link
          v-for="(item, idx) in exploreLinks"
          :key="item.label"
          :to="item.to"
          class="explore-card"
          :data-delay="idx"
        >
          <div class="h-11 w-11 rounded-xl bg-[#0F2A4A]/[0.06] flex items-center justify-center mb-4 text-[#0F2A4A] group-hover:bg-[#C9A227]/15 transition-colors">
            <component :is="renderIcon(item.icon)" class="h-5 w-5" />
          </div>
          <p class="font-poppins font-bold text-gray-800 mb-1">{{ item.label }}</p>
          <p class="text-xs text-gray-400 leading-relaxed">{{ item.desc }}</p>
        </router-link>
      </div>
    </section>

    <!-- ============ KUALITAS MUTU ============ -->
    <section class="bg-[#0F2A4A] py-20 px-6">
      <div class="max-w-5xl mx-auto">
        <p class="text-[11px] font-bold tracking-[0.25em] text-[#C9A227] uppercase text-center mb-3">
          Cakupan Penjaminan
        </p>
        <h2 class="text-center text-white text-2xl md:text-3xl font-bold mb-14">
          Kualitas Mutu yang Dijaga LPMU
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div
            v-for="area in qualityAreas"
            :key="area.label"
            class="bg-white rounded-2xl p-8 text-center"
          >
            <div class="flex justify-center mb-5">
              <div class="h-14 w-14 rounded-full bg-[#0F2A4A]/[0.06] flex items-center justify-center text-[#0F2A4A]">
                <component :is="renderIcon(area.icon)" class="h-6 w-6" />
              </div>
            </div>
            <h3 class="font-poppins font-bold text-gray-800 mb-2.5">{{ area.label }}</h3>
            <p class="text-sm text-gray-500 leading-relaxed">{{ area.desc }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- ============ TEASER SIKLUS PPEPP ============ -->
    <section class="max-w-4xl mx-auto px-6 py-20">
      <div class="text-center mb-12">
        <p class="text-[11px] font-bold tracking-[0.25em] text-[#C9A227] uppercase mb-3">
          Cara Kerja LPMU
        </p>
        <h2 class="font-poppins font-bold text-2xl md:text-3xl text-gray-800 mb-3">
          Siklus PPEPP yang Berkelanjutan
        </h2>
        <p class="text-gray-500 max-w-md mx-auto text-sm leading-relaxed">
          Lima tahap yang terus berulang &mdash; setiap peningkatan mutu
          menjadi titik awal penetapan standar berikutnya.
        </p>
      </div>

      <div class="flex flex-wrap justify-center gap-3 md:gap-2 mb-8">
        <template v-for="(step, idx) in ppeppSteps" :key="step">
          <router-link
            :to="`/spmi/${slugify(step)}`"
            class="group flex items-center gap-2.5 bg-white border border-gray-100 hover:border-[#C9A227]/50 rounded-full pl-2 pr-4 py-2 shadow-sm hover:shadow-md transition-all"
          >
            <span
              class="flex items-center justify-center h-7 w-7 rounded-full text-[11px] font-bold shrink-0 border-[1.5px] border-[#C9A227]/40 text-[#0F2A4A] group-hover:bg-[#C9A227] group-hover:border-[#C9A227] group-hover:text-white transition-colors"
            >
              {{ idx + 1 }}
            </span>
            <span class="text-sm font-medium text-gray-700 group-hover:text-[#0F2A4A] transition-colors">
              {{ step }}
            </span>
          </router-link>
          <svg
            v-if="idx < ppeppSteps.length - 1"
            class="h-4 w-4 text-gray-300 self-center hidden md:block"
            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"
          >
            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
          </svg>
        </template>
      </div>
    </section>

    

    <!-- ============ KEGIATAN TERBARU ============ -->
    <section class="max-w-4xl mx-auto px-6 py-20">
      <p class="text-[11px] font-bold tracking-[0.25em] text-[#C9A227] uppercase text-center mb-3">
        Kabar Terbaru
      </p>
      <h2 class="text-center font-poppins font-bold text-2xl md:text-3xl text-gray-800 mb-12">
        Kegiatan LPMU
      </h2>

      <article class="grid grid-cols-1 md:grid-cols-[1.1fr_1fr] gap-0 bg-white rounded-2xl border border-gray-100 overflow-hidden shadow-sm">
        <!-- Ilustrasi kustom, bukan placeholder generik -->
        <div class="bg-[#0F2A4A] flex items-center justify-center p-10 md:p-12">
          <svg viewBox="0 0 200 200" class="w-full max-w-[220px]">
            <rect x="45" y="30" width="110" height="140" rx="10" fill="white" fill-opacity="0.06" />
            <rect x="60" y="50" width="80" height="8" rx="4" fill="#C9A227" />
            <rect x="60" y="68" width="60" height="6" rx="3" fill="white" fill-opacity="0.3" />
            <rect x="60" y="82" width="65" height="6" rx="3" fill="white" fill-opacity="0.3" />
            <circle cx="100" cy="125" r="26" fill="#C9A227" fill-opacity="0.15" />
            <path d="M88 125l8 8 18-18" stroke="#C9A227" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" fill="none" />
          </svg>
        </div>

        <div class="p-8 md:p-10 flex flex-col justify-center">
          <p class="text-xs text-gray-400 mb-2">04 September 2025</p>
          <h3 class="font-poppins font-bold text-lg text-gray-800 mb-3 leading-snug">
            Website LPMU Hadir dengan Tampilan Baru
          </h3>
          <p class="text-sm text-gray-500 leading-relaxed mb-5">
            Pembaruan tampilan situs LPMU menghadirkan navigasi yang lebih
            sederhana untuk mengakses profil lembaga, siklus SPMI, dan
            sistem E-SPMI.
          </p>
          <router-link
            to="/profil/visi"
            class="inline-flex items-center gap-1.5 text-sm font-semibold text-[#0F2A4A] hover:gap-2.5 transition-all duration-200 w-fit"
          >
            Selengkapnya
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
            </svg>
          </router-link>
        </div>
      </article>
    </section>
  </div>
</template>

<script setup>
defineOptions({ name: 'LandingPage' });

import { ref, onMounted, onUnmounted, h } from 'vue';

const exploreRef = ref(null);
const exploreInView = ref(false);
let observer;

onMounted(() => {
  observer = new IntersectionObserver(
    ([entry]) => {
      if (entry.isIntersecting) {
        exploreInView.value = true;
        observer.disconnect();
      }
    },
    { threshold: 0.2 }
  );
  if (exploreRef.value) observer.observe(exploreRef.value);
});

onUnmounted(() => observer?.disconnect());

const exploreLinks = [
  { label: 'Profil', desc: 'Visi, struktur, dan tugas LPMU', to: '/profil/visi', icon: 'building' },
  { label: 'SPMI', desc: 'Siklus penjaminan mutu internal', to: '/spmi/penetapan', icon: 'cycle' },
  { label: 'SPME', desc: 'Akreditasi lembaga eksternal', to: '/spme', icon: 'award' },
  { label: 'Kuisioner', desc: 'Survei kepuasan sivitas akademika', to: '/kuisioner', icon: 'clipboard' },
];

const qualityAreas = [
  {
    label: 'Sarana dan Prasarana',
    desc: 'Menjaga mutu fasilitas pembelajaran dan lingkungan kampus yang mendukung mahasiswa.',
    icon: 'building',
  },
  {
    label: 'Mahasiswa dan Alumni',
    desc: 'Memastikan mutu pendidikan lewat rekrutmen transparan dan kualitas lulusan yang relevan.',
    icon: 'graduate',
  },
  {
    label: 'Tata Kelola',
    desc: 'Menjaga mutu kepemimpinan, perencanaan strategis, dan pengelolaan keuangan yang transparan.',
    icon: 'governance',
  },
];

const ppeppSteps = ['Penetapan', 'Pelaksanaan', 'Evaluasi', 'Pengendalian', 'Peningkatan'];

const traits = ['Mandiri', 'Inovatif', 'Kompetitif', 'Berjiwa Pancasila'];

function slugify(text) {
  return text.toLowerCase();
}

const iconPaths = {
  building: 'M3 9.5L12 4l9 5.5M4.5 10.5V19a1 1 0 001 1h4v-5h5v5h4a1 1 0 001-1v-8.5',
  cycle: 'M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99',
  award: 'M12 15a5 5 0 100-10 5 5 0 000 10zm-3.5 3.5L7 22l5-2.5L17 22l-1.5-3.5',
  clipboard: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
  graduate: 'M12 14l9-5-9-5-9 5 9 5zm0 0v6m-9-9v4c0 1.1 4 3 9 3s9-1.9 9-3v-4',
  governance: 'M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m5-4a4 4 0 100-8 4 4 0 000 8zm6 4a4 4 0 10-8 0 4 4 0 008 0z',
};

function renderIcon(name) {
  return h(
    'svg',
    { viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor', 'stroke-width': '1.8' },
    [h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', d: iconPaths[name] })]
  );
}
</script>

<style scoped>
.font-poppins {
  font-family: 'Poppins', sans-serif;
}

.explore-card {
  display: block;
  background: white;
  border: 1px solid rgba(15, 42, 74, 0.08);
  border-radius: 1rem;
  padding: 1.5rem;
  opacity: 0;
  transform: translateY(14px);
  transition: opacity 0.5s ease, transform 0.5s ease, border-color 0.2s ease, box-shadow 0.2s ease;
}
.explore-card:hover {
  border-color: rgba(201, 162, 39, 0.4);
  box-shadow: 0 8px 24px -8px rgba(15, 42, 74, 0.15);
}
.explore-visible .explore-card {
  opacity: 1;
  transform: translateY(0);
}
.explore-card[data-delay="1"] { transition-delay: 0.06s; }
.explore-card[data-delay="2"] { transition-delay: 0.12s; }
.explore-card[data-delay="3"] { transition-delay: 0.18s; }

@media (prefers-reduced-motion: reduce) {
  .explore-card {
    transition: none !important;
    opacity: 1;
    transform: none;
  }
}
</style>