<template>
  <div class="bg-white">
    <!-- ============ BREADCRUMB ============ -->
    <div class="max-w-6xl mx-auto px-6 pt-8 text-sm text-gray-400 flex items-center gap-2">
      <router-link to="/" class="hover:text-[#0F2A4A] transition-colors">Beranda</router-link>
      <span>/</span>
      <span class="text-gray-500">Profil</span>
      <span>/</span>
      <span class="text-[#0F2A4A] font-medium">Struktur Organisasi</span>
    </div>

    <!-- ============ HERO ============ -->
    <section class="max-w-6xl mx-auto px-6 pt-10 pb-16">
      <div class="text-center">
        <p class="text-[11px] md:text-xs font-bold tracking-[0.25em] text-[#C9A227] uppercase mb-3">
          Struktur Organisasi
        </p>
        <h1 class="font-poppins text-3xl md:text-4xl font-bold text-[#0F2A4A]">
          Lembaga Penjaminan Mutu
        </h1>
      </div>
    </section>

    <!-- ============ LEVEL 1: KETUA ============ -->
    <section class="max-w-6xl mx-auto px-6 pb-14">
      <div class="flex flex-col items-center">
        <div
          v-if="dataPimpinan"
          class="w-full max-w-sm bg-[#0F2A4A] text-white p-8 rounded-2xl text-center shadow-xl border-b-4 border-[#C9A227]"
        >
          <div class="w-32 h-32 md:w-36 md:h-36 mx-auto aspect-square bg-white rounded-xl overflow-hidden mb-4 shadow-inner ring-4 ring-[#0F2A4A] flex items-center justify-center">
            <img
              v-if="dataPimpinan.foto"
              :src="dataPimpinan.foto"
              :alt="dataPimpinan.nama"
              class="w-full h-full object-cover object-top"
            />
            <span v-else class="font-poppins font-bold text-2xl text-[#0F2A4A]">
              {{ getInisial(dataPimpinan.nama) }}
            </span>
          </div>
          <h2 class="font-poppins text-[1.1rem] font-bold mb-1">
            {{ dataPimpinan.nama }}
          </h2>
          <p class="text-xs text-[#C9A227] font-bold tracking-widest uppercase mt-3">
            {{ dataPimpinan.jabatan }}
          </p>
        </div>

        <!-- Konektor ke level 2 -->
        <div class="hidden md:block w-px h-10 bg-[#0F2A4A]/15"></div>
      </div>
    </section>

    <!-- ============ LEVEL 2: KOORDINATOR BIDANG ============ -->
    <!--
      Sengaja TIDAK diberi background section (tetap menyatu putih dengan halaman)
      supaya tidak membentuk "kotak" yang janggal. Pembeda cukup di level card:
      tint navy tipis (bukan abu netral) + border + shadow.
    -->
    <section class="max-w-6xl mx-auto px-6 pb-24">
      <p class="text-center text-xs font-semibold tracking-[0.15em] text-gray-400 uppercase mb-10">
        Koordinator Bidang
      </p>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div
          v-for="(item, index) in dataKoordinator"
          :key="index"
          class="coordinator-card"
        >
          <div class="w-28 h-28 md:w-32 md:h-32 mx-auto aspect-square bg-white rounded-xl overflow-hidden mb-4 flex items-center justify-center text-[#0F2A4A] font-bold text-xl font-poppins shadow-sm">
            <img
              v-if="item.foto"
              :src="item.foto"
              :alt="item.nama"
              class="w-full h-full object-cover object-top"
            />
            <span v-else>
              {{ getInisial(item.nama) }}
            </span>
          </div>
          <h3 class="font-poppins text-[1.05rem] font-bold text-[#0F2A4A] leading-snug mb-3 min-h-[3rem] flex items-center justify-center">
            {{ item.nama }}
          </h3>
          <p class="text-xs text-gray-500 font-medium leading-relaxed">
            {{ item.jabatan }}
          </p>
        </div>
      </div>
    </section>

    <!-- ============ CALLOUT: TIM AUDITOR (AMI) ============ -->
    <section class="bg-[#0F2A4A] py-16 px-6 relative overflow-hidden">
      <div class="absolute top-0 right-0 w-72 h-72 bg-[#C9A227] rounded-full blur-3xl opacity-10 -translate-y-1/2 translate-x-1/3 pointer-events-none"></div>

      <div class="max-w-6xl mx-auto grid grid-cols-1 md:grid-cols-[auto_1fr] gap-8 md:gap-14 items-center relative z-10">
        <div class="flex items-baseline gap-1 shrink-0">
          <span class="font-poppins font-extrabold text-[#C9A227] text-5xl md:text-6xl leading-none tracking-tight">
            Tim AMI
          </span>
        </div>
        <div class="border-t md:border-t-0 md:border-l border-white/15 pt-6 md:pt-0 md:pl-14">
          <p class="text-[11px] font-bold tracking-[0.2em] text-white/50 uppercase mb-2">
            Gugus Tugas Khusus
          </p>
          <p class="text-white/85 leading-relaxed max-w-2xl text-[1.1rem]">
            Dalam pelaksanaan kegiatan
            <router-link to="/spmi/evaluasi" class="text-white font-semibold hover:text-[#C9A227] transition-colors">
              Audit Mutu Internal
            </router-link>,
            LPMU dapat membentuk <span class="text-[#C9A227] font-medium">Tim Auditor</span> secara independen untuk melaksanakan, mengawal, dan memastikan efektivitas siklus SPMI di seluruh unit Universitas.
          </p>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref } from 'vue';

defineOptions({ name: 'StrukturOrganisasiPage' });

const getInisial = (nama) => {
  if (!nama) return '';
  const words = nama.split(' ').filter((w) => w.length > 0 && !w.includes('.') && !w.includes(','));
  if (words.length >= 2) {
    return (words[0][0] + words[1][0]).toUpperCase();
  }
  return nama.substring(0, 2).toUpperCase();
};

const dataPimpinan = ref({
  nama: 'Cahyaningtyas Ria Uripi, S.E., M.Si.',
  jabatan: 'Ketua LPMU',
  foto: '/images/ketua.png',
});

const dataKoordinator = ref([
  {
    nama: 'Dwi Sri Wiyanti, S.T., M.T.',
    jabatan: 'Koordinator Bidang Peningkatan Standar',
    foto: '/images/koor1.png',
  },
  {
    nama: 'Dr. Tjahjani Murdijaningsih, S.E., M.Si.',
    jabatan: 'Koordinator Bidang Proses Pembelajaran & Kurikulum',
    foto: '/images/koor2.png',
  },
  {
    nama: 'Krisnhoe Sukma Danuta, S.E., M.Acc.Ak.CA.',
    jabatan: 'Koordinator Bidang Dokumentasi dan Pelaporan',
    foto: '/images/koor3.png',
  },
]);
</script>

<style scoped>
.font-poppins {
  font-family: 'Poppins', sans-serif;
}

/*
  Card koordinator: tint navy sangat tipis (bukan abu netral) sebagai
  pembeda dari halaman putih, konsisten dengan identitas warna situs.
  Saat hover, tint sedikit menguat + terangkat.
*/
.coordinator-card {
  background-color: rgba(15, 42, 74, 0.035);
  border: 1px solid rgba(15, 42, 74, 0.08);
  border-top: 4px solid #0f2a4a;
  border-radius: 1rem;
  padding: 1.5rem;
  text-align: center;
  transition: background-color 0.25s ease, transform 0.25s ease, box-shadow 0.25s ease;
}
.coordinator-card:hover {
  background-color: rgba(15, 42, 74, 0.06);
  transform: translateY(-4px);
  box-shadow: 0 12px 28px -10px rgba(15, 42, 74, 0.2);
}

@media (prefers-reduced-motion: reduce) {
  .coordinator-card {
    transition: none !important;
  }
  .coordinator-card:hover {
    transform: none;
  }
}
</style>