<template>
  <div class="bg-white min-h-screen">
    <!-- Breadcrumb -->
    <div class="max-w-6xl mx-auto px-6 pt-6 text-sm text-gray-500 flex items-center gap-2">
      <span class="text-blue-600 font-bold">❝</span>
      <router-link to="/" class="hover:text-blue-600 transition">Beranda</router-link>
      <span>/</span>
      <span class="text-gray-700">Profil LPMU</span>
    </div>

    <!-- Hero / Judul -->
    <section class="relative max-w-6xl mx-auto px-6 pt-8 pb-20">
      <div class="hidden md:block absolute right-24 top-0 h-48 w-48 rounded-full bg-blue-600"></div>
      <div class="hidden md:block absolute right-0 top-28 h-56 w-56 rounded-full bg-gray-100"></div>

      <h1 class="relative text-4xl md:text-5xl font-bold text-gray-800 mb-10">
        Profil LPMU
      </h1>

      <div class="relative grid grid-cols-1 md:grid-cols-2 gap-10 items-center">
        <p class="text-gray-500 leading-loose text-justify">
          Lembaga Penjaminan Mutu Universitas (LPMU) Universitas Wijayakusuma
          Purwokerto dibentuk untuk memastikan mutu akademik dan non-akademik
          universitas berjalan secara terpadu dan konsisten. LPMU menjadi
          ujung tombak Sistem Penjaminan Mutu Internal (SPMI), mencakup
          kualitas sumber daya manusia, lulusan, tata kelola, penyelenggaraan
          Tri Dharma, serta layanan universitas secara berkelanjutan.
        </p>

        <img
          src="https://unwiku.ac.id/wp-content/uploads/2025/02/Drone-luas-minimize.jpg"
          alt="Kampus UNWIKU"
          class="rounded-xl shadow-md w-full h-64 object-cover"
        />
      </div>
    </section>

    <!-- Struktur Anggota -->
    <section class="bg-gray-50 py-16 px-6">
      <div class="max-w-6xl mx-auto">
        <h2 class="text-2xl md:text-3xl font-bold text-gray-800 text-center mb-2">
          Struktur Anggota
        </h2>
        <p class="text-gray-500 text-center mb-14">
          Susunan pengurus dan anggota LPMU
        </p>

        <div class="flex flex-col items-center gap-8">
          <!-- Level 1: Ketua -->
          <template v-if="ketua">
            <MemberCard :anggota="ketua" size="lg" />
            <div class="w-px h-8 bg-gray-300 -mt-2"></div>
          </template>

          <!-- Level 2: Wakil Ketua / Sekretaris -->
          <template v-if="wakilDanSekretaris.length">
            <div class="hidden md:block w-2/3 h-px bg-gray-300"></div>
            <div class="flex flex-wrap justify-center gap-8">
              <MemberCard
                v-for="anggota in wakilDanSekretaris"
                :key="anggota.id"
                :anggota="anggota"
                size="md"
              />
            </div>
          </template>

          <!-- Level 3: Auditor / Anggota -->
          <template v-if="auditorAnggota.length">
            <div class="hidden md:block w-4/5 h-px bg-gray-300"></div>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-6 w-full max-w-4xl">
              <MemberCard
                v-for="anggota in auditorAnggota"
                :key="anggota.id"
                :anggota="anggota"
                size="sm"
              />
            </div>
          </template>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
defineOptions({ name: 'ProfilPage' });

import { computed, h } from 'vue';

const defaultAvatar =
  'https://ui-avatars.com/api/?name=User&background=e5e7eb&color=6b7280';

// Data dummy statis (belum terhubung backend)
const daftarAnggota = [
  { id: 1, nama: 'Dr. Ahmad Fauzi', jabatan: 'Ketua', unit: 'Pusat Penjaminan Mutu', foto: '' },
  { id: 2, nama: 'Siti Aminah, M.Kom', jabatan: 'Sekretaris', unit: 'Pusat Penjaminan Mutu', foto: '' },
  { id: 3, nama: 'Joko Prasetyo, M.M', jabatan: 'Wakil Ketua', unit: 'Pusat Penjaminan Mutu', foto: '' },
  { id: 4, nama: 'Budi Santoso, M.T', jabatan: 'Auditor', unit: 'Fakultas Teknik', foto: '' },
  { id: 5, nama: 'Rina Wulandari, M.Pd', jabatan: 'Auditor', unit: 'Fakultas Pendidikan', foto: '' },
  { id: 6, nama: 'Dewi Lestari, M.Si', jabatan: 'Anggota', unit: 'Fakultas Ekonomi', foto: '' },
  { id: 7, nama: 'Andi Firmansyah, M.Kom', jabatan: 'Anggota', unit: 'Fakultas Ilmu Komputer', foto: '' },
];

const levelJabatan = {
  ketua: 1,
  'wakil ketua': 2,
  sekretaris: 2,
  auditor: 3,
  anggota: 3,
};

function getLevel(jabatan) {
  const key = (jabatan || '').toLowerCase().trim();
  return levelJabatan[key] ?? 3;
}

const ketua = computed(() => daftarAnggota.find((a) => getLevel(a.jabatan) === 1));
const wakilDanSekretaris = computed(() => daftarAnggota.filter((a) => getLevel(a.jabatan) === 2));
const auditorAnggota = computed(() => daftarAnggota.filter((a) => getLevel(a.jabatan) === 3));

// Komponen kartu anggota, ukuran menyesuaikan level jabatan
const sizeMap = {
  lg: { avatar: 'h-32 w-32', name: 'text-lg', card: 'p-8 w-64' },
  md: { avatar: 'h-24 w-24', name: 'text-base', card: 'p-6 w-52' },
  sm: { avatar: 'h-16 w-16', name: 'text-sm', card: 'p-4 w-full' },
};

const MemberCard = (props) => {
  const s = sizeMap[props.size] || sizeMap.sm;
  return h(
    'div',
    { class: `bg-white rounded-xl shadow-sm text-center hover:shadow-md transition ${s.card}` },
    [
      h('img', {
        src: props.anggota.foto || defaultAvatar,
        alt: props.anggota.nama,
        class: `${s.avatar} rounded-full object-cover mx-auto mb-4 border-4 border-blue-50`,
      }),
      h('h3', { class: `font-semibold text-gray-800 ${s.name}` }, props.anggota.nama),
      h('p', { class: 'text-sm text-blue-600 mb-1' }, props.anggota.jabatan),
      h('p', { class: 'text-xs text-gray-400' }, props.anggota.unit),
    ]
  );
};
</script>